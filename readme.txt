=== Log Emails ===
Contributors: webaware
Plugin Name: Log Emails
Plugin URI: http://shop.webaware.com.au/downloads/log-emails/
Author URI: http://webaware.com.au/
Donate link: http://shop.webaware.com.au/donations/?donation_for=Log+Emails
Tags: email log, logging, logs, email
Requires at least: 3.6.1
Tested up to: 4.4
Stable tag: 1.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Log emails to the database, to better diagnose email problems

== Description ==

Log emails to the WordPress database for later analysis. Access is restricted to administrators, and emails with WordPress passwords are obfuscated for security / privacy. Useful for diagnosing lost email problems.

Use this plugin with [Disable Emails](http://shop.webaware.com.au/downloads/disable-emails/) to divert all WordPress emails into logs, especially handy for developers.

Logs are automatically purged after a defined period, set through a settings page in the WordPress admin. Setting the period to 0 prevents logs from being purged. Deactivating the plugin purges all logs.

= Translations =

Many thanks to the generous efforts of our translators:

* Czech (cs-CZ) -- [Rudolf Klusal](http://www.klusik.cz/)
* French (fr-FR) -- [Hugo Catellier](http://www.eticweb.ca/)

If you'd like to help out by translating this plugin, please [sign up for an account and dig in](https://translate.webaware.com.au/projects/log-emails).

== Installation ==

1. Upload this plugin to your /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Standard WordPress emails are logged, but some others are not =

You probably have a plugin that is sending emails via some other method, like directly using the PHP `mail()` function, or directly implementing an SMTP client. Not much I can do about that...

= Why are some HTML emails broken? =

Some plugins tell WordPress that their emails are HTML, using one of two accepted methods. These emails are correctly recorded as HTML and should display reasonably well in the logs. If they use CSS, they might not display exactly like they do in an email program, but should still display OK.

Some plugins just dump HTML into emails without saying it's HTML, or even following basic HTML document rules. If those emails have an alternative body, implying HTML + plain text parts, they'll be marked as HTML and will display as such.

If you find that emails from some plugins are broken, please tell me in the [support forum](https://wordpress.org/support/plugin/log-emails).

= What performance impact does it have? =

Logging emails writes to the database. The plugin uses a custom post type, so logging each email has the same impact as saving a new WordPress post, i.e. generally not much.

The plugin stops caching plugins from purging their cache every time an email log is saved, currently implemented for WP Super Cache. If you have a caching plugin that is being affected by Log Emails, please tell me in the [support forum](https://wordpress.org/support/plugin/log-emails).

== Contributions ==

* [Translate into your preferred language](https://translate.webaware.com.au/projects/log-emails)
* [Fork me on GitHub](https://github.com/webaware/log-emails)

== Roadmap ==

Things I'd like to add to the plugin:

* flag read / unread with bulk actions to mark logs
* filter read / unread logs

== Upgrade Notice ==

= 1.0.6 =

added French translation, verified working in WordPress 4.4

== Changelog ==

## Changelog

The full changelog can be found [on GitHub](https://github.com/webaware/log-emails/blob/master/changelog.md). Recent entries:

### 1.0.6, 2015-12-02

* added: French translation (thanks, [Hugo Catellier](http://www.eticweb.ca/)!)
