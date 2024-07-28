<?php

if (!defined('ABSPATH')) {
	exit;
}

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
		add_action('admin_init', array($this, 'registerSettings'));
		add_action('admin_menu', array($this, 'adminMenu'));
		add_action('plugin_action_links_' . LOG_EMAILS_PLUGIN_NAME, [$this, 'pluginActionLinks']);
		add_filter('plugin_row_meta', array($this, 'addPluginDetailsLinks'), 10, 2);
		add_action(self::TASK_PURGE, array($this, 'purge'));

		register_deactivation_hook(LOG_EMAILS_PLUGIN_FILE, array($this, 'deactivate'));

		// hooks for monitoring mails, priority set low so they run after other plugins
		add_filter('wp_mail', array($this, 'wpMail'), 99999);
		add_filter('bp_email_set_to', array($this, 'buddypressRecipients'), 99999);
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
	* filter wp_mail -- grab a copy of the arguments for later
	* @param array $args
	* @return array
	*/
	public function wpMail($args) {
		$this->args = $args;
		return $args;
	}

	/**
	* if BuddyPress is managing mail calls itself, grab a copy of the recipients since we won't get the above
	* @param BP_Email_Recipient[] $recipients
	* @return BP_Email_Recipient[]
	*/
	public function buddypressRecipients($recipients) {
		$to = array();
		foreach ($recipients as $recipient) {
			$to[] = $recipient->get_address();
		}

		$this->args = array(
			'to'			=> implode(',', $to),
			'subject'		=> false,
			'message'		=> false,
			'headers'		=> false,
			'attachments'	=> array(),
		);

		return $recipients;
	}

	/**
	* action phpmailer_init -- grab a copy of the email for the log
	* @param PHPMailer $phpmailer
	*/
	public function phpmailerInit($phpmailer) {
		// get message body, protect passwords
		$message = $this->maybeObfuscatePassword($phpmailer->Body);

		// get alternative message body, protect passwords
		$alt_message = $this->maybeObfuscatePassword($phpmailer->AltBody);

		// collate additional fields into array
		$fields = array();
		$fields['_log_emails_log_from'] = sprintf('%s <%s>', $phpmailer->FromName, $phpmailer->From);

		// detect text/html when content type is text/plain but email has an alternate message (WP e-Commerce, I'm looking at you!)
		$contentType = $phpmailer->ContentType;
		if ($contentType === 'text/plain' && !empty($alt_message)) {
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
	* maybe obfuscate a line with a password
	* @param string $line
	* @return string
	*/
	protected function maybeObfuscatePassword($line) {
		$line = preg_replace('/.*password.*/im', __('*** password redacted ***', 'log-emails'), $line);

		// maybe also obfuscate localised term for password
		$password_local = translate('Password');
		$password_local = function_exists('mb_strtolower') ? mb_strtolower($password_local) : strtolower($password_local);
		if ($password_local !== 'password') {
			$line = preg_replace('/.*' . preg_quote($password_local) . '.*/im', __('*** password redacted ***', 'log-emails'), $line);
		}

		return $line;
	}

	/**
	* deactivate the plug-in
	*/
	public function deactivate() {
		// remove scheduled tasks
		wp_clear_scheduled_hook(self::TASK_PURGE);
	}

	/**
	 * add plugin action links on plugins page
	 */
	public function pluginActionLinks(array $links) : array {
		if (current_user_can('manage_options')) {
			// add logs link
			$url = admin_url('edit.php?post_type=log_emails_log');
			$link = sprintf('<a href="%s">%s</a>', esc_url($url), esc_html_x('Logs', 'plugin details links', 'log-emails'));
			array_unshift($links, $link);

			// add settings link
			$url = admin_url('options-general.php?page=log-emails');
			$link = sprintf('<a href="%s">%s</a>', esc_url($url), esc_html_x('Settings', 'plugin details links', 'log-emails'));
			array_unshift($links, $link);
		}

		return $links;
	}

	/**
	* action hook for adding plugin details links
	*/
	public function addPluginDetailsLinks($links, $file) {
		if ($file === LOG_EMAILS_PLUGIN_NAME) {
			$links[] = sprintf('<a href="https://wordpress.org/support/plugin/log-emails" target="_blank">%s</a>', _x('Get Help', 'plugin details links', 'log-emails'));
			$links[] = sprintf('<a href="https://wordpress.org/plugins/log-emails/" target="_blank">%s</a>', _x('Rating', 'plugin details links', 'log-emails'));
			$links[] = sprintf('<a href="https://translate.wordpress.org/projects/wp-plugins/log-emails" target="_blank">%s</a>', _x('Translate', 'plugin details links', 'log-emails'));
			$links[] = sprintf('<a href="https://shop.webaware.com.au/donations/?donation_for=Log+Emails" target="_blank">%s</a>', _x('Donate', 'plugin details links', 'log-emails'));
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
				'option_name'		=> 'log_emails_limit_days',
				'label_text'		=> __('number of days to keep email logs', 'log-emails'),
				'default'			=> 30,
				'class'				=> 'small-text',
			)
		);
	}

	/**
	* admin menu items
	*/
	public function adminMenu() {
		$label = __('Log Emails', 'log-emails');
		add_options_page($label, $label, 'manage_options', 'log-emails', array($this, 'settingsPage'));
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
