# Log Emails
Contributors: webaware
Plugin Name: Log Emails
Plugin URI: https://wordpress.org/plugins/log-emails/
Author URI: https://shop.webaware.com.au/
Donate link: https://shop.webaware.com.au/donations/?donation_for=Log+Emails
Tags: email log, logging, logs, email
Requires at least: 4.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Log emails to the database, to enable email problem analysis

## Description

Log emails to the WordPress database for later analysis. Access is restricted to administrators, and emails with WordPress passwords are obfuscated for security / privacy. Useful for diagnosing lost email problems.

Use this plugin with [Disable Emails](https://shop.webaware.com.au/downloads/disable-emails/) to divert all WordPress emails into logs, especially handy for developers.

Logs are automatically purged after a defined period, set through a settings page in the WordPress admin. Setting the period to 0 prevents logs from being purged. Uninstalling the plugin purges all logs.

### Translations

Many thanks to the generous efforts of our translators:

* Czech (cs-CZ) -- [Rudolf Klusal](http://www.klusik.cz/)
* English (en_CA) -- [the English (Canadian) translation team](https://translate.wordpress.org/locale/en-ca/default/wp-plugins/log-emails)
* English (en_GB) -- [the English (British) translation team](https://translate.wordpress.org/locale/en-gb/default/wp-plugins/log-emails)
* French (fr-FR) -- [Hugo Catellier](http://www.eticweb.ca/)
* Korean (ko_KR) -- [the Korean translation team](https://translate.wordpress.org/locale/ko/default/wp-plugins/log-emails)

If you'd like to help out by translating this plugin, please [sign up for an account and dig in](https://translate.wordpress.org/projects/wp-plugins/log-emails).

## Installation

1. Upload this plugin to your /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## Frequently Asked Questions

### Standard WordPress emails are logged, but some others are not

You probably have a plugin that is sending emails via some other method, like directly using the PHP `mail()` function, or directly implementing an SMTP client. Not much I can do about that...

### Why are some HTML emails broken?

Some plugins tell WordPress that their emails are HTML, using one of two accepted methods. These emails are correctly recorded as HTML and should display reasonably well in the logs. If they use CSS, they might not display exactly like they do in an email program, but should still display OK.

Some plugins just dump HTML into emails without saying it's HTML, or even following basic HTML document rules. If those emails have an alternative body, implying HTML + plain text parts, they'll be marked as HTML and will display as such.

If you find that emails from some plugins are broken, please tell me in the [support forum](https://wordpress.org/support/plugin/log-emails).

### What performance impact does it have?

Logging emails writes to the database. The plugin uses a custom post type, so logging each email has the same impact as saving a new WordPress post, i.e. generally not much.

## Upgrade Notice

### 1.5.0

requires PHP 7.4 minimum (recommend PHP 8.2+); fixed warnings in PHP 8.1+; added Settings, Logs links to Plugins page listing

## Changelog

The full changelog can be found [on GitHub](https://github.com/webaware/log-emails/blob/master/changelog.md). Recent entries:

### 1.5.0

Released 2024-07-28

* changed: requires PHP 7.4 minimum (recommend PHP 8.2+)
* fixed: viewing an email log triggered warnings in PHP 8.1+
* added: Settings, Logs links to Plugins page listing
