<?php
/*
Plugin Name: Log Emails
Plugin URI: http://snippets.webaware.com.au/wordpress-plugins/log-emails/
Description: Log emails to the database, to enable email problem analysis
Version: 0.0.7-alpha7
Author: WebAware
Author URI: http://webaware.com.au/
Text Domain: log-emails
Domain Path: /languages
*/

/*
copyright (c) 2013-2014 WebAware Pty Ltd (email : support@webaware.com.au)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined('LOG_EMAILS_PLUGIN_ROOT')) {
	define('LOG_EMAILS_PLUGIN_ROOT', dirname(__FILE__) . '/');
	define('LOG_EMAILS_PLUGIN_NAME', basename(dirname(__FILE__)) . '/' . basename(__FILE__));

	// scheduled tasks
	define('LOG_EMAILS_TASK_PURGE', 'log_emails_purge');
}

require LOG_EMAILS_PLUGIN_ROOT . 'class.LogEmailsPlugin.php';
LogEmailsPlugin::run();
