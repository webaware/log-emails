<?php
/*
Plugin Name: Log Emails
Plugin URI: https://shop.webaware.com.au/downloads/log-emails/
Description: Log emails to the database, to enable email problem analysis
Version: 1.5.0
Author: WebAware
Author URI: https://shop.webaware.com.au/
Text Domain: log-emails
Domain Path: /languages/
*/

/*
copyright (c) 2013-2024 WebAware Pty Ltd (email : support@webaware.com.au)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if (!defined('ABSPATH')) {
	exit;
}

// phpcs:disable Modernize.FunctionCalls.Dirname.FileConstant
define('LOG_EMAILS_PLUGIN_FILE', __FILE__);
define('LOG_EMAILS_PLUGIN_ROOT', dirname(__FILE__) . '/');
define('LOG_EMAILS_PLUGIN_NAME', basename(dirname(__FILE__)) . '/' . basename(__FILE__));
define('LOG_EMAILS_PLUGIN_VERSION', '1.5.0');

require LOG_EMAILS_PLUGIN_ROOT . 'includes/class.LogEmailsPlugin.php';
LogEmailsPlugin::getInstance();
