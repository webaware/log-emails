=== Log Emails ===
Contributors: webaware
Plugin Name: Log Emails
Plugin URI: http://snippets.webaware.com.au/wordpress-plugins/log-emails/
Author URI: http://www.webaware.com.au/
Donate link:
Tags: email log, logging, logs, email
Requires at least: 3.2.1
Tested up to: 3.9.1
Stable tag: -
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Log emails to the database, to enable email problem analysis

== Description ==

Log emails to the WordPress database for later analysis. Access is restricted to administrators, and emails with WordPress passwords are obfuscated for security / privacy. Useful for diagnosing lost email problems.

== Installation ==

1. Upload this plugin to your /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= TODO: how do I FAQ? =

Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.

== Screenshots ==

TODO: a screenshot

== Roadmap ==

Things I'd like to add to the plugin:

* next/previous navigation through email logs
* flag read / unread with bulk actions to mark logs
* filter read / unread logs
* detect "smells like HTML" for HTML emails with no content type, e.g. WP e-Commerce mails

== Changelog ==

= 0.0.1 [2013-10-09] =
* private release

TODO: can this run later, after all wp_mail* filters have been called? e.g. wp_mail_content_type
