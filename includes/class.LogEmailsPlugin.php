<?php

/**
* plugin management
*/
class LogEmailsPlugin {

	protected $args = false;				// arguments to wp_mail() function, recorded from filter wp_mail

	// scheduled tasks
	const TASK_PURGE = 'log_emails_purge';

	/**
	* static method for getting the instance of this singleton object
	* @return self
	*/
	public static function getInstance() {
		static $instance = null;

		if (is_null($instance)) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	* hook WordPress to handle script and style fixes
	*/
	public function __construct() {
		add_action('init', array($this, 'loadTranslations'), 0);		// must run before CPT are registered
		add_action('init', array($this, 'init'));
		add_action('plugins_loaded', array($this, 'cacheManagers'));
		add_action('admin_init', array($this, 'registerSettings'));
		add_action('admin_menu', array($this, 'adminMenu'));
		add_filter('plugin_row_meta', array($this, 'addPluginDetailsLinks'), 10, 2);
		add_action(self::TASK_PURGE, array($this, 'purge'));

		register_deactivation_hook(LOG_EMAILS_PLUGIN_FILE, array($this, 'deactivate'));

		// hooks for monitoring mails, priority set low so they run after other plugins
		add_filter('wp_mail', array($this, 'wpMail'), 99999);
		add_action('phpmailer_init', array($this, 'phpmailerInit'), 99999);

		// load custom post type handler
		require LOG_EMAILS_PLUGIN_ROOT . 'includes/class.LogEmailsPostTypeLog.php';
		new LogEmailsPostTypeLog();
	}

	/**
	* load translations
	* NB: must load before CPT are registered so that strings are properly translated
	*/
	public function loadTranslations() {
		load_plugin_textdomain('log-emails', false, basename(dirname(LOG_EMAILS_PLUGIN_FILE)) . '/languages/');
	}

	/**
	* init action
	*/
	public function init() {
		// make sure we have a schedule for purging old logs
		if (!wp_next_scheduled(self::TASK_PURGE)) {
			wp_schedule_event(time() + 10, 'daily', self::TASK_PURGE);
		}
	}

	/**
	* load cache managers if caching plugin found
	*/
	public function cacheManagers() {
		if (defined('WP_CACHE') && WP_CACHE) {
			// WP Super Cache
			require LOG_EMAILS_PLUGIN_ROOT . 'includes/class.LogEmailsCache_WpSuperCache.php';
			LogEmailsCache_WpSuperCache::softInstall();

			// TODO: also handle W3 Total Cache if it needs similar treatment
		}
	}

	/**
	* filter wp_mail -- grab a copy of the arguments for later
	* @param array $args
	* @return array
	*/
	public function wpMail($args) {
		$this->args = $args;
		return $args;
	}

	/**
	* action phpmailer_init -- grab a copy of the email for the log
	* @param PHPMailer $phpmailer
	*/
	public function phpmailerInit($phpmailer) {
		// get message body, protect passwords
		$message = $phpmailer->Body;
		if (stripos($message, 'password') !== false) {
			$message = preg_replace('/.*password.*/im', __('*** password redacted ***', 'log-emails'), $message);
		}

		// get alternative message body, protect passwords
		$alt_message = $phpmailer->AltBody;
		if (stripos($alt_message, 'password') !== false) {
			$alt_message = preg_replace('/.*password.*/im', __('*** password redacted ***', 'log-emails'), $alt_message);
		}

		// collate additional fields into array
		$fields = array();
		$fields['_log_emails_log_from'] = sprintf('%s <%s>', $phpmailer->FromName, $phpmailer->From);

		// detect text/html when content type is text/plain but email has an alternate message (WP e-Commerce, I'm looking at you!)
		$contentType = $phpmailer->ContentType;
		if ($contentType == 'text/plain' && !empty($alt_message)) {
			$contentType = 'text/html';
		}
		$fields['_log_emails_log_content-type'] = $contentType;

		// pick up recipients from wp_mail() args
		if (isset($this->args['to'])) {
			$to = $this->args['to'];
			if (is_array($to)) {
				$to = implode(', ', $to);
			}
			$fields['_log_emails_log_to'] = $to;
		}

		// pick up CC/BCC from wp_mail() args, collating them from headers
		if (isset($this->args['headers'])) {
			$cc = array();
			$bcc = array();
			$headers = $this->args['headers'];
			if (!is_array($headers)) {
				$headers = explode("\n", str_replace("\r\n", "\n", $headers));
			}
			foreach ($headers as $header) {
				if ($header) {
					list($header, $value) = explode(':', $header, 2);
					switch (strtolower($header)) {
						case 'cc':
							$cc[] = trim($value);
							break;

						case 'bcc':
							$bcc[] = trim($value);
							break;
					}
				}
			}

			if (!empty($cc)) {
				$fields['_log_emails_log_cc'] = implode(', ', $cc);
			}
			if (!empty($bcc)) {
				$fields['_log_emails_log_bcc'] = implode(', ', $bcc);
			}
		}

		$post_id = LogEmailsPostTypeLog::createLog($phpmailer->Subject, $message, $alt_message, $fields);

		// reset recorded wp_mail() arguments
		$this->args = false;
	}

	/**
	* deactivate the plug-in
	*/
	public function deactivate() {
		// remove scheduled tasks
		wp_clear_scheduled_hook(self::TASK_PURGE);
	}

	/**
	* action hook for adding plugin details links
	*/
	public function addPluginDetailsLinks($links, $file) {
		if ($file == LOG_EMAILS_PLUGIN_NAME) {
			$links[] = sprintf('<a href="http://wordpress.org/support/plugin/log-emails" target="_blank">%s</a>', _x('Get Help', 'plugin details links', 'log-emails'));
			$links[] = sprintf('<a href="http://wordpress.org/plugins/log-emails/" target="_blank">%s</a>', _x('Rating', 'plugin details links', 'log-emails'));
			$links[] = sprintf('<a href="http://translate.webaware.com.au/projects/log-emails" target="_blank">%s</a>', _x('Translate', 'plugin details links', 'log-emails'));
			$links[] = sprintf('<a href="http://shop.webaware.com.au/downloads/log-emails/" target="_blank">%s</a>', _x('Donate', 'plugin details links', 'log-emails'));
		}

		return $links;
	}

	/**
	* register options and class method that will handle the settings screen
	*/
	public function registerSettings() {
		add_settings_section('log-emails', false, false, 'log-emails');

		register_setting('log-emails', 'log_emails_limit_days', 'intval');
		add_settings_field('log_emails_limit_days', _x('Log limit', 'maximum days to keep email logs', 'log-emails'),
			array($this, 'settingsFieldText'), 'log-emails', 'log-emails',
			array(
				'option_name' => 'log_emails_limit_days',
				'label_text' => __('number of days to keep email logs', 'log-emails'),
				'default' => 30,
				'class' => 'small-text',
			)
		);
	}

	/**
	* admin menu items
	*/
	public function adminMenu() {
		add_options_page('Log Emails', 'Log Emails', 'manage_options', 'log-emails', array($this, 'settingsPage'));
	}

	/**
	* settings admin
	*/
	public function settingsPage() {
		require LOG_EMAILS_PLUGIN_ROOT . 'views/settings-form.php';
	}

	/**
	* show text field
	*/
	public function settingsFieldText($args) {
		require LOG_EMAILS_PLUGIN_ROOT . 'views/settings-field-text.php';
	}

	/**
	* execute purge of old logs
	*/
	public function purge() {
		$limit_days = get_option('log_emails_limit_days');
		if (empty($limit_days) || !is_numeric($limit_days)) {
			return;
		}

		LogEmailsPostTypeLog::purge($limit_days);
	}

}
