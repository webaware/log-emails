# Log Emails

## Changelog

### 1.1.0, 2016-07-02

* SECURITY FIX: any logged-in user could see any email log or other post by guessing a post ID (thanks for responsible disclosure, [Plugin Vulnerabilities](https://www.pluginvulnerabilities.com/))

### 1.0.6, 2015-12-02

* added: French translation (thanks, [Hugo Catellier](http://www.eticweb.ca/)!)

### 1.0.5, 2014-12-18

* fixed: undefined property `delete_posts` on custom post type capabilities in WordPress 4.1

### 1.0.4, 2014-11-03

* fixed: default sort order is by ID descending, to avoid ordering errors when logs occur in the same second
* added: Czech translation (thanks, [Rudolf Klusal](http://www.klusik.cz/)!)

### 1.0.3, 2014-09-06

* fixed: PHP warning on static call to non-static methods in class LogEmailsCache_WpSuperCache
* fixed: fix WordPress 4.0 box shadow on return-to-list :focus

### 1.0.2, 2014-08-21

* fixed: bulk action checkboxes not appearing on stand-alone WordPress sites

### 1.0.1, 2014-08-20

* fixed: menu link not appearing on stand-alone WordPress sites
* added: uninstall handler to remove logs when plugin is uninstalled

### 1.0.0, 2014-08-16

* initial public release
