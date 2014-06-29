<?php

/**
* plugin management
*/
class LogEmailsPlugin {

	protected static $args = false;			// arguments to wp_mail() function, recorded from filter wp_mail

	/**
	* hook WordPress to handle script and style fixes
	*/
	public static function run() {
		add_action('init', array(__CLASS__, 'init'));
		add_action('admin_init', array(__CLASS__, 'registerSettings'));
		add_action('admin_menu', array(__CLASS__, 'adminMenu'));
		add_filter('plugin_row_meta', array(__CLASS__, 'addPluginDetailsLinks'), 10, 2);
		add_action(LOG_EMAILS_TASK_PURGE, array(__CLASS__, 'purge'));

		register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivate'));

		// hooks for monitoring mails, priority set low so they run after other plugins
		add_filter('wp_mail', array(__CLASS__, 'wpMail'), 99999);
		add_action('phpmailer_init', array(__CLASS__, 'phpmailerInit'), 99999);

		// load custom post type handler
		require LOG_EMAILS_PLUGIN_ROOT . 'class.LogEmailsPostTypeLog.php';
		new LogEmailsPostTypeLog();
	}

	/**
	* init action
	*/
	public static function init() {
		// load gettext domain
		//~ load_plugin_textdomain('log-emails', false, dirname(plugin_basename(__FILE__)) . '/languages/');

		// make sure we have a schedule for purging old logs
		if (!wp_next_scheduled(LOG_EMAILS_TASK_PURGE)) {
			wp_schedule_event(time(), 'daily', LOG_EMAILS_TASK_PURGE);
		}
	}

	/**
	* filter wp_mail -- grab a copy of the arguments for later
	* @param array $args
	* @return array
	*/
	public static function wpMail($args) {
		self::$args = $args;
		return $args;
	}

	/**
	* action phpmailer_init -- grab a copy of the email for the log
	* @param PHPMailer $phpmailer
	*/
	public static function phpmailerInit($phpmailer) {
		// stop WP Super Cache from clearing cache on new email log
		if (isset($GLOBALS['wp_cache_clear_on_post_edit'])) {
			$old_wp_cache_clear_on_post_edit = $GLOBALS['wp_cache_clear_on_post_edit'];
			$GLOBALS['wp_cache_clear_on_post_edit'] = false;
		}

//~ error_log("\n\nargs = \n" . print_r(self::$args,1));
//~ error_log("\n\nphpmailer = \n" . print_r($phpmailer,1));

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

		// create post for message
		$post_id = wp_insert_post(array(
			'post_type' => 'log_emails_log',
			'post_content' => $message,
			'post_title' => $phpmailer->Subject,
			'post_status' => 'publish',
			'comment_status' => 'closed',
			'ping_status' => 'closed',
		));

		if ($post_id) {
			// pick up some fields from mail object
			add_post_meta($post_id, '_log_emails_log_from', sprintf('%s <%s>', $phpmailer->FromName, $phpmailer->From));
			add_post_meta($post_id, '_log_emails_log_content-type', $phpmailer->ContentType);

			// pick up recipients from wp_mail() args
			if (isset(self::$args['to'])) {
				$to = self::$args['to'];
				if (is_array($to)) {
					$to = implode(', ', $to);
				}
				add_post_meta($post_id, '_log_emails_log_to', $to);
			}

			// pick up CC/BCC from wp_mail() args, collating them from headers
			if (isset(self::$args['headers'])) {
				$cc = array();
				$bcc = array();
				$headers = self::$args['headers'];
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

				if (empty($cc)) {
					add_post_meta($post_id, '_log_emails_log_cc', implode(', ', $cc));
				}
				if (empty($bcc)) {
					add_post_meta($post_id, '_log_emails_log_bcc', implode(', ', $bcc));
				}
			}

			// alternative body if present
			if ($alt_message) {
				add_post_meta($post_id, '_log_emails_log_altbody', $alt_message);
			}
		}

		// reset recorded wp_mail() arguments
		self::$args = false;

		// restore WP Super Cache cache clearing setting
		if (isset($GLOBALS['wp_cache_clear_on_post_edit'])) {
			$GLOBALS['wp_cache_clear_on_post_edit'] = $old_wp_cache_clear_on_post_edit;
		}
	}

	/**
	* deactivate the plug-in
	*/
	public static function deactivate() {
		// remove scheduled tasks
		wp_clear_scheduled_hook(LOG_EMAILS_TASK_PURGE);
	}

	/**
	* action hook for adding plugin details links
	*/
	public static function addPluginDetailsLinks($links, $file) {
		//~ if ($file == LOG_EMAILS_PLUGIN_NAME) {
			//~ $links[] = '<a href="http://wordpress.org/support/plugin/log-emails">' . __('Get help', 'log-emails') . '</a>';
			//~ $links[] = '<a href="http://wordpress.org/plugins/log-emails/">' . __('Rating', 'log-emails') . '</a>';
			//~ $links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=XXXXXXXXXXXX">' . __('Donate', 'log-emails') . '</a>';
		//~ }

		return $links;
	}

	/**
	* register options and class method that will handle the settings screen
	*/
	public static function registerSettings() {
		add_settings_section('log-emails', false, false, 'log-emails');

		register_setting('log-emails', 'log_emails_limit_days', 'intval');
		add_settings_field('log_emails_limit_days', _x('Log limit', 'maximum days to keep email logs', 'log-emails'),
			array(__CLASS__, 'settingsFieldText'), 'log-emails', 'log-emails',
			array(
				'option_name' => 'log_emails_limit_days',
				'label_text' => __('number of days to keep email logs for', 'log-emails'),
				'default' => 30,
				'class' => 'small-text',
			)
		);
	}

	/**
	* admin menu items
	*/
	public static function adminMenu() {
		add_options_page('Log Emails', 'Log Emails', 'manage_options', 'log-emails', array(__CLASS__, 'settingsPage'));
	}

	/**
	* settings admin
	*/
	public static function settingsPage() {
		require LOG_EMAILS_PLUGIN_ROOT . 'views/settings-form.php';
	}

	/**
	* show text field
	*/
	public static function settingsFieldText($args) {
		require LOG_EMAILS_PLUGIN_ROOT . 'views/settings-field-text.php';
	}

	/**
	* purge old logs
	*/
	public static function purge() {
		global $wpdb;

		$limit_days = get_option('log_emails_limit_days');
		if (empty($limit_days) || !is_numeric($limit_days)) {
			return;
		}

		$cutoff = date_create("-$limit_days days");

		$sql = "select ID from {$wpdb->posts} where post_type = 'log_emails_log' and post_date_gmt < %s";
		$posts = $wpdb->get_col($wpdb->prepare($sql, $cutoff->format('Y-m-d')));

		if ($posts) {
			// stop WP Super Cache from clearing cache on new email log
			if (isset($GLOBALS['wp_cache_clear_on_post_edit'])) {
				$old_wp_cache_clear_on_post_edit = $GLOBALS['wp_cache_clear_on_post_edit'];
				$GLOBALS['wp_cache_clear_on_post_edit'] = false;
			}

			foreach ($posts as $post_id) {
				wp_delete_post($post_id, true);
			}

			// restore WP Super Cache cache clearing setting
			if (isset($GLOBALS['wp_cache_clear_on_post_edit'])) {
				$GLOBALS['wp_cache_clear_on_post_edit'] = $old_wp_cache_clear_on_post_edit;
			}
		}
	}
}
