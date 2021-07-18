=== GlotPress Notify ===
Contributors: webaware
Plugin Name: GlotPress Notify
Plugin URI: http://shop.webaware.com.au/glotpress-notify/
Author URI: http://webaware.com.au/
Donate link: http://shop.webaware.com.au/donations/?donation_for=GlotPress+Notify
Tags: glotpress, translations, localization, localisation, language
Requires at least: 3.7
Tested up to: 4.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

notify WordPress users when new GlotPress translations strings are awaiting review

== Description ==

[GlotPress](https://glotpress.trac.wordpress.org/wiki/GlotPress) is a great free tool for localising your plugins and themes. When translators add new translations to GlotPress, they don't always tell you. If your GlotPress installation is paired with a WordPress installation, this plugin gives you an easy way to find out what's waiting for approval:

* list the projects and languages with strings waiting for approval
* subscribe to email notifications for individual projects

Admins, validators, and translators can all view strings waiting for approval and receive notification emails. End the guessing game.

= Translations =

Many thanks to the generous efforts of our translators:

* Dutch (nl-NL) -- [Sander Keuzenkamp](https://ribwhost.nl/)
* French (fr-FR) -- [Hugo Catellier](http://www.eticweb.ca/)

If you'd like to help out by translating this plugin, please [sign up for an account and dig in](https://translate.wordpress.org/projects/wp-plugins/glotpress-notify). Yes, it's GlotPress.

== Installation ==

1. Either install automatically through the WordPress admin, or download the .zip file, unzip to a folder, and upload the folder to your /wp-content/plugins/ directory. Read [Installing Plugins](https://codex.wordpress.org/Managing_Plugins#Installing_Plugins) in the WordPress Codex for details.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to GlotPress Notify > Settings in the admin and set the GlotPress table prefix and sender email address

== Frequently Asked Questions ==

= Does GlotPress need to be in the same database as WordPress? =

Yes. This plugin only looks at GlotPress tables in the same database as the WordPress installation it's running from.

It's not unusual for a WordPress installation to be set up on the same database as GlotPress, with the wp_users table as the GlotPress users table, because it makes it easier to manage user registrations. This is how I have it, and why I wrote the notifications plugin as a WordPress plugin.

= Can it manage multiple GlotPress installations? =

Not yet.

= Can I change the email template? =

Yes, copy it from the plugin's templates folder into your theme, in a folder called "plugins/glotpress-notify".

== Screenshots ==

1. settings
2. user settings for subscribing to projects
3. listing of projects with translations awaiting approval

== Contributions ==

* [Translate into your preferred language](https://translate.wordpress.org/projects/wp-plugins/glotpress-notify)
* [Fork me on GitHub](https://github.com/webaware/glotpress-notify)

== Upgrade Notice ==

= 1.0.1 =

Translations for nl_NL, fr_FR

== Changelog ==

### 1.0.1, 2015-12-07

* added: Dutch translation (thanks, [Sander Keuzenkamp](https://ribwhost.nl/)!)
* added: French translation (thanks, [Hugo Catellier](http://www.eticweb.ca/)!)
* changed: translations now accepted on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/glotpress-notify)

### 1.0.0, 2014-09-15

* initial public version
