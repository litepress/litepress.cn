=== Genericon'd ===
Contributors: Ipstenu
Tags: icons, genericons, font icon, UI
Requires at least: 3.9
Tested up to: 4.9
Stable tag: 4.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enables easy use of the Genericons and Social Logo icon sets from within WordPress. Icons can be inserted using either HTML or a shortcode.

== Description ==

Genericon'd includes three icon sets:

* [Genericons Neue](https://github.com/Automattic/genericons-neue) - Generic looking icons, suitable for a blog or simple website.
* [Social Logos](https://github.com/Automattic/social-logos) - A repository of all the social logos used on WordPress.com
* [Genericons Classic](https://github.com/Automattic/genericons) - The original! Generic looking icons as a Font, suitable for a blog or simple website.

By default, _Genericons Neue_ and _Social Logos_ are both active and use SVG sprites, not fonts. If you want to go back to displaying the icons as a font, you can do so via settings.

To use any of the Genericons icons on your WordPress site you can use basic HTML (for inserting in themes and functions) or shortcodes (for use in posts or widgets). You can adjust the size of the icons via css or, when using the shortcode, the size attribute. Default size is 16px for Genericons and 24 for Social Logos.

To display the Twitter icon: `[genericon icon=twitter]`

= Privacy Policy =

No remote calls are made with this plugin and no data is tracked.

== Installation ==

Install as a normal WordPress Plugin.

= Usage =

Add shortcodes to your posts, pages and even widgets to display a Genericons or Social Logos icon.

For example, to display the Twitter icon: `[genericon icon=twitter]`

== Frequently Asked Questions ==

= I have an idea for an icon! =

Great! I'm a monkey with a crayon! Seriously, though, I didn't make Genericons, I have no artistic ability to make more. If I did, we'd have a unicorn one. Please file issues and requests for new icons <a href="https://github.com/Automattic/genericons-neue/issues">directly with Genericons</a>.

Have a desire for social icons like Twumlrbook? Submit that [directly to Social Logos](https://github.com/Automattic/social-logos/issues)

= What's the difference between Genericons Neue, Social Logos, and 'Classic' Genericons? =

* [Genericons Neue](https://github.com/Automattic/genericons-neue) are the replacements for Genericons, using SVG instead of fonts.
* [Social Logos](https://github.com/Automattic/social-logos) are where all the social logos like Twitter moved.
* ['Classic' Genericons](http://genericons.com/) are the old font icon pack you know and love

= Are there any known conflicts? =

Not at this time.

= What are all the icon names? =

On your WP dashboard, go to Appearance -> Genericon'd. The page there will list all the icons and their file names.

= Can I add them to menus? =

No. You can't add shortcodes to menus at this time. There used to be a workaround with using the `icon` code, but since we're not using Font Icons anymore, you can't anymore unless you enable Legacy Font Icons.

= What are Legacy Font Icons? =

If you still want to have the old font-icons available, you can enable them by checking boxes on the settings page. It's not recommended though, as the font will slow your page down.

= How do I change colors? =

Using CSS with SVGs is weird. Instead of something like `.genericon {color:red;}` you'll have to use the `fill` parameter. For example, if you just want Twitter to be blue, add `svg.social-logos-twitter {fill:blue;}` and so on and so forth.

= Why are some icons using `genericons` and others use `social-logos`? =

Because in version 4.0, Genericons ceased development and moved to Genericons Neue. In doing so, they dropped support for all social media logos. Since I didn't want to leave everyone out in the cold, I pulled in the Social Logos icon set. 

= Okay, but I want to change color in just this one use... =

I know what you mean. Try this: `[genericon icon=twitter color=blue]`

It uses inline styling, which I hate, but this was the best way to solve the problem that I could find (suggestions welcome).

= Speaking of, can I make just this one icon bigger? =

Sure can! Use this: `[genericon icon=twitter size=2x]`

You can use 2x through 6x. Anything else punts it to 1x.

= I want to repeat an icon =

You do it like this: `[genericon icon=star repeat=4]`

= Can I flip an icon? =

Sure! `[genericon icon=twitter rotate={90|180|270|flip-horizontal|flip-vertical} ]`

= How about changing the hover-color? =

This is less complicated than it looks but requires CSS:

`
svg.social-logos-twitter:hover {
    fill: purple!important;
}
`

If you wanted it to be for links only, then use `a svg.social-logos-twitter:hover` since you wrap the href around the shortcode.

= Aren't they called Genericons with an S? =

Yes, but Genericon'd is a Zaboo-esque sort of way of saying 'These icons have been genericonified!' I was in a The Guild frame of mind. Also since this is not the official plugin, I didn't want to use that slug.

== Screenshots ==

1. Genericon'd help page
2. Zaboo, patron avatar of Genericon'd

== Changelog ==

= 4.0.5 =
* 2018-01
* Updating some icons

= 4.0.1 =
* 2016-11
* Settings wouldn't save

= 4.0.0 =

* 2016-11
* Complete refactor
* Merge of Genericons Neue
* Merge of Social Icons
* Add option for supporting legacy fonts

= 3.4.1 =
* 2015-11-12
* IE8 support restored. 

= 3.4.0 = 
* 2015-09-18
* <a href="http://genericons.com/2015/09/18/3-4/">Major 3.4 release to Genericons</a>
* Move path to CSS file per change in Genericons
* Remove my rotation code as it's now included in core Genericons
* Split rotation and flip code to reflect changes above
* Fix broken rotations (which apparently was broken ages ago and no one noticed, sorry)

== Upgrade Notice ==
Version 4.0 and up uses New Genericons which NO LONGER uses font icons by default. While I have done my best to ensure upgrades are seamless, please make sure to look at your site where Genericon'd is in use to be sure.
