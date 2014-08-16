<?php

/**
* stop WP Super Cache from clearing its cache just because we're adding or deleting email logs
*/
class LogEmailsCache_WpSuperCache {

	/**
	* do a soft install, only fully install if target cache is actually running
	*/
	public static function softInstall() {
		if (isset($GLOBALS['wp_super_cache_late_init'])) {
			add_action('log_emails_cache_pause', array(__CLASS__, 'pauseCaching'));
			add_action('log_emails_cache_resume', array(__CLASS__, 'resumeCaching'));
		}
	}

	/**
	* temporarily stop caching plugin from reacting to post changes
	*/
	public function pauseCaching() {
		remove_action('delete_post', 'wp_cache_post_edit', 0);
		remove_action('clean_post_cache', 'wp_cache_post_edit');
	}

	/**
	* allow caching plugin to resume reacting to post changes
	*/
	public function resumeCaching() {
		add_action('delete_post', 'wp_cache_post_edit', 0);
		add_action('clean_post_cache', 'wp_cache_post_edit');
	}

}
