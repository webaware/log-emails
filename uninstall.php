<?php

/**
* uninstall script -- remove all traces of this plugin
*/

// must be called from WordPress as uninstall action
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

// remove all options
delete_option('log_emails_limit_days');

// remove scheduled tasks
wp_clear_scheduled_hook('log_emails_purge');

// include page cache handlers
include dirname(__FILE__) . '/includes/class.LogEmailsCache_WpSuperCache.php';
LogEmailsCache_WpSuperCache::softInstall();

// remove all email log data
global $wpdb;
$sql = "select ID from {$wpdb->posts} where post_type = 'log_emails_log'";
$posts = $wpdb->get_col($sql);

if ($posts) {
	do_action('log_emails_cache_pause');

	foreach ($posts as $post_id) {
		wp_delete_post($post_id, true);
	}

	do_action('log_emails_cache_resume');
}
