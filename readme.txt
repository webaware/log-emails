=== Log Emails ===
Contributors: webaware
Plugin Name: Log Emails
Plugin URI: https://shop.webaware.com.au/downloads/log-emails/
Author URI: https://webaware.com.au/
Donate link: https://shop.webaware.com.au/donations/?donation_for=Log+Emails
Tags: email log, logging, logs, email
Requires at least: 3.6.1
Tested up to: 4.5.3
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Log emails to the database, to enable email problem analysis

== Description ==

Log emails to the WordPress database for later analysis. Access is restricted to administrators, and emails with WordPress passwords are obfuscated for security / privacy. Useful for diagnosing lost email problems.

Use this plugin with [Disable Emails](https://shop.webaware.com.au/downloads/disable-emails/) to divert all WordPress emails into logs, especially handy for developers.

Logs are automatically purged after a defined period, set through a settings page in the WordPress admin. Setting the period to 0 prevents logs from being purged. Deactivating the plugin purges all logs.

= Translations =

Many thanks to the generous efforts of our translators:

* Czech (cs-CZ) -- [Rudolf Klusal](http://www.klusik.cz/)
* English (en_CA) -- [the English (Canadian) translation team](https://translate.wordpress.org/locale/en-ca/default/wp-plugins/log-emails)
* French (fr-FR) -- [Hugo Catellier](http://www.eticweb.ca/)
* Korean (ko_KR) -- [the Korean translation team](https://translate.wordpress.org/locale/ko/default/wp-plugins/log-emails)

If you'd like to help out by translating this plugin, please [sign up for an account and dig in](https://translate.wordpress.org/projects/wp-plugins/log-emails).

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

* [Translate into your preferred language](https://translate.wordpress.org/projects/wp-plugins/log-emails)
* [Fork me on GitHub](https://github.com/webaware/log-emails)

== Roadmap ==

Things I'd like to add to the plugin:

* flag read / unread with bulk actions to mark logs
* filter read / unread logs

== Upgrade Notice ==

= 1.1.0 =

SECURITY FIX: any logged-in user could see any email log or other post by guessing a post ID; see changelog for other changes.

== Changelog ==

## Changelog

The full changelog can be found [on GitHub](https://github.com/webaware/log-emails/blob/master/changelog.md). Recent entries:

### 1.1.0, 2016-07-02

* SECURITY FIX: any logged-in user could see any email log or other post by guessing a post ID (thanks for responsible disclosure, [Plugin Vulnerabilities](https://www.pluginvulnerabilities.com/))
* fixed: second row of action links for each log in list
* fixed: move Date column back to end of row in list
* changed: don't sanitize email log body / alt-body when saving, to preserve more of actual email for log view (credit: [Hrohh](https://wordpress.org/support/profile/hrohh))
* changed: restrict log view CSS so that it doesn't affect email content display (credit: [Hrohh](https://wordpress.org/support/profile/hrohh))
* changed: improved accessibility in the log view page
* added: warn when an email is missing sender, recipients, subject, or body
