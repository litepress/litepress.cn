=== Permalink Manager Lite ===
Contributors: mbis
Donate link: https://www.paypal.me/Bismit
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: permalinks, custom permalinks, url editor, permalinks, woocommerce permalinks
Requires at least: 4.4.0
Requires PHP: 5.4
Tested up to: 5.7.0
Stable tag: 2.2.9.7

Permalink Manager allows to easily change full URL addresses of posts, pages, custom post types, terms and WooCommerce links. You can also set different permalink formats per language or bulk change the URLs.

== Description ==

Permalink Manager is a most advanced and highly rated Wordpress permalink editor that helps Wordpress users to control the URL addresses of all posts, pages, custom post type elements (taxonomies are supported in Pro version). To avoid 404 or duplicated content errors after the new custom permalink is defined, the visitors trying to access the old permalink will be automatically redirected to the new custom URL.

The plugin supports all custom post types & custom taxonomies and popular 3rd party plugins including WooCommerce, Yoast SEO, WPML, and Polylang. To improve SEO performance even more, the plugin settings provide a possibility to disable the canonical redirect (used natively by Wordpress) and control the trailing slashes settings.

<a href="https://permalinkmanager.pro/docs/?utm_source=wordpressorg">Documentation</a> | <a href="https://permalinkmanager.pro/buy-permalink-manager-pro/?utm_source=wordpressorg">Permalink Manager Pro</a>

= All features =

* **Edit full permalinks**<br/>A completely custom permalink can be set for each post, page and public custom post type individually *(categories, tags & custom taxonomies terms permalinks can be edited in Permalink Manager Pro)*
* **Custom post types support**<br/>It is also possible to exclude specific post types & taxonomies to stop Permalink Manager from filtering their permalinks.
* **Custom permastructures**<br/>The plugin allows to specify how the custom permalinks should be formatted by default (when the new post/term is added or after the permalinks are regenerated)
* **Translate permalinks**<br/>Permalink Manager allows to translate and set different permalink format/structure per language when WPML or Polylang plugin is enabled.
* **Auto-redirect**<br/>Old (native) permalinks are redirected to new (custom) permalinks (in 301 or 302 mode) to prevent 404 error (SEO friendly).
* **Canonical redirects**<br/>Possibility to disable native canonical redirects.
* **Bulk editors**<br/>"Regenerate/Reset" + "Find and replace" tools that allow to bulk/mass change the permalinks (or native slugs).
* **Trailing slashes settings**<br/>They can be forced or removed from all permalinks.

= Need additional features & priority support? =
Buy <a href="https://permalinkmanager.pro?utm_source=wordpress">Permalink Manager Pro here</a>.

https://www.youtube.com/watch?v=KMOtAK5c7t8

= Additional features available in Permalink Manager Pro =

* **Taxonomies**<br/>Full support for taxonomies (categories, tags & custom taxonomies).
* **WooCommerce**<br/>Full support for WooCommerce (products, product tags, product categories). Permalink Manager allows to remove /product-category and /product from WooCommerce permalinks.
* **WooCommerce coupon URLs**<br/>Coupon codes may have their public URLs (eg. http://shop.com/BLACKFRIDAY) that will automatically apply the discount to the cart.
* **Custom fields**<br/>Custom fields can be used inside permalinks (compatible with Advanced Custom Fields).
* **Extra internal redirects**<br/>Multiple URLs can lead to a single post/term (they could be defined individually for each element).
* **External URL redirect**<br/>Posts/terms can redirect the visitors to external websites (the URLs could be defined individually for each element).
* **"Stop-words"**<br/>User-defined words can be automatically removed from default permalinks.
* **Custom Permalinks**<br/>Import custom permalinks saved with that plugin.
* **Priority support**<br/>All the support requests from Permalink Manager Pro users are handled in the first place.

Buy <a href="https://permalinkmanager.pro?utm_source=wordpress">Permalink Manager Pro here</a>.

= Translators =
* Japanese - Shinsaku Ikeda

== Installation ==

Go to `Plugins -> Add New` section from your admin account and search for `Permalink Manager`.

You can also install this plugin manually:

1. Download the plugin's ZIP archive and unzip it.
2. Copy the unzipped `permalink-manager` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress

= Bulk URI editor =
After the plugin is installed you can access its dashboard from this page: `Tools -> Permalink Manager`.

= Single URI editor =
To display the URI editor metabox click on gray "Permalink Editor" button displayed below the post/page title.

== Frequently Asked Questions ==

= Can I define different permalink formats per each language.
Yes, it is possible if you are using either WPML or Polylang. You can find <a href="https://permalinkmanager.pro/docs/tutorials/how-to-translate-permalinks/">the full instructions here</a>.

= Can I use Permalink Manager to change the terms permalinks (eg. post or product categories)?
This feature is available only in Permalink Manager Pro.

= Will the old permalink automatically redirect the new custom ones?
Yes, Permalink Manager will automatically redirect the native permalinks (used when the plugin is disabled or before it was activated) to the actual, custom permalinks.

= Can I add custom fields to the permalinks?
This feature is available in paid version of plugin. Please go <a href="https://permalinkmanager.pro/docs/tutorials/how-to-use-custom-fields-inside-wordpress-permalinks/">here for more details</a>.

= Does this plugin support Buddypress?
Currently there is no 100% guarantee that Permalink Manager will work correctly with Buddypress.

= Can I delete/disable Permalink Manager after the permalinks are updated? =
Yes, if you used Permalink Manager only to regenerate the slugs (native post names). Please note that if you use custom permalinks (that differ from the native ones), they will no longer be used after the plugin is disabled.

It is because Permalink Manager overwrites one of the core Wordpress functionalities to bypass the rewrite rules ("regular expressions" to detect the posts/pages/taxonomies/etc. and another parameters from the URL) by using the array of custom permalinks (you can check them in "Debug" tab) that are used only by my plugin.

== Screenshots ==

1.	Permalink URI editor.
2.	Permalink URI editor in Gutenberg.
3.	"Find & replace" tool.
4.	"Regenerate/Reset" tool.
5.	A list of updated posts after the permalinks are regenerated.
6.	Permastructure settings.
7.	Permastructure settings (different permalink structure per language).
8.	Permalink Manager settings.

== Changelog ==

= 2.2.9.7 (11/03/2021) =
* Enhancement - Support for WooCommerce CSV Product Importer/Exporter added
* Enhancement - Better support for relationship field (ACF)
* Fix - The custom redirects are now case-insensitive

= 2.2.9.6 (08/02/2021) =
* Fix - Hotfix for WooCommerce coupon related functions

= 2.2.9.5 (08/02/2021) =
* Fix - The custom permalink is generated properly if the product is duplicated in WooCommerce dashboard
* Enhancement - New settings field - "Exclude drafts"
* Enhancement - Minor code improvements

= 2.2.9.4 =
* Fix - The language prefix for default language is now added again when "Use directory for default language" mode is turned on in WPML settings ("Language URL format")

= 2.2.9.3 =
* Fix - The custom permalinks are now saved correctly for new posts with 'wp_insert_post' hook
* Fix - The custom permalinks are deleted when 'delete_post' hook is called
* Fix - WPML - language switcher on posts (blog) page works correctly now
* Fix - WooCommerce Subscription - the switch subscription URL is no longer overwritten
* Fix - The URLs with duplicated trailing slashes are now redirected to the canonical permalink
* Enhancement - Basic support for Ultimate Member plugin added
* Enhancement - UI improvements
* Enhancement - Support for "comment-page" endpoint added
* Enhancement - New filter added - 'permalink_manager_control_trailing_slashes'

= 2.2.9.2 =
* Dev - Improvements for Permalink_Manager_Core_Functions::control_trailing_slashes() function
* Dev - Minor codebase improvements
* Fix - Hotfix for "Automatically fix broken URIs" function
* Fix - Underscores are now by default allowed in the custom permalinks
* Enhancement - Better support for GeoDirectory plugin
* Fix - 'permalink_manager_allow_new_post_uri' & 'permalink_manager_allow_update_post_uri' filter replaced 'permalink_manager_new_post_uri_{$post_object->post_type}' and 'permalink_manager_update_post_uri_{$post->post_type}'

= 2.2.9.0/2.2.9.1 =
* Enhancement - Basic support for BasePress added
* Enhancement - Added support for custom product attributes in products' permalinks (WooCommerce)
* Fix - "Trailing slash redirect" is now disabled on front pages (to prevent redirect loop on Polylang/WPML language front pages)
* Dev - The taxonomy term used in custom permalinks is selected differently
* Dev - Performance improvements (duplicate-check function)
* Dev - Further improvements for the function used to sanitize the custom permalinks
* Dev - Codebase improvements

= 2.2.8.8/2.2.8.9 =
* Fix - Hotfix for 'redirect_canonical' function (causing a redirect loop)
* Fix - The custom canonical permalink set with Yoast SEO is now no longer overwriten
* Fix - The custom permalinks are no longer saved if the post/term has no title
* Fix - Hotfix for Gutenberg related JS errors
* Fix - Hotfix for Groundhogg plugin
* Fix - Hotfix for "Customize" admin bar menu link
* Fix - Hotfix for WPML's language switcher on posts page
* Fix - Hotfixes for WP 5.5 - blog/posts page + draft template is now loaded correctly
* Dev - Trailing slash redirect code adjustments
* Enhancement - Added support for GeoDirectory plugin

= 2.2.8.7 =
* Dev - Improved breadcrumbs hook (better compatibility with WPML/Polylang)
* Fix - Hotfix for permalinks used in language switcher on blog/posts page (WPML)
* Fix - Hotfix for cart URL in WooCommerce's mini-cart widget (now the permalink is translated correctly when WPML/Polylang is used)
* Dev - Improved support for WPML's Advanced Translation
* Dev - Improved support for pagination & embed endpoints
* Fix - Hotfix for attachments permalinks
* Fix - Improved url_to_postid() hook
* Fix - Added support for Dokan /edit/ endpoint

= 2.2.8.6 =
* Fix - Hotfix for Permalink_Manager_Helper_Functions::get_disabled_taxonomies() function
* Dev - New wrapper function with filter 'permalink_manager_post_statuses' for get_post_statuses()
* Enhancement - Extended support for "My Listing" theme (by 27collective)
* Fix - Hotfix for Gutenberg editor (broken HTML output)
* Dev - Extended support for permalinks stored in Yoast SEO database tables (Indexables)

= 2.2.8.4/2.2.8.5 =
* Fix - Hotfix for Permastructures (now the permalink formats are saved correctly)
* Fix - Hotfix for trailing slashes settings
* Dev - Improved setting fields descriptions
* Dev - Adjustments for search functionality in Bulk URI Editor
* Enhancement - Support for WPML Classic Translation Editor
* Dev - Adjustments for "Auto-update URI"
* Dev - Improvements for get_post_types_array() & get_taxonomies_array() functions used to list the content types supported by Permalink Manager

= 2.2.8.2/2.2.8.3 =
* Dev - Improved descriptions in the plugin settings
* Fix - Hotfix for endpoint redirect

= 2.2.8.1 =
* Fix - Hotfix for stop-words (now, the stop-words can be saved again)
* Enhancement - Support for Duplicate Page plugin

= 2.2.8.0 =
* Fix - Hotfix for multisite/network installations. Now, the plugin globals are reloaded whenever switch_blog() function is called.
* Fix - Hotfix for url_to_postid() function. The $pm_query global will no longer be altered.
* Fix - Hotfix for post/page revisions in custom permalink detect function
* Dev - Improved WP All Import Pro integration (better support for taxonomies)
* Dev - A different approach for WP Customize URLs
* Enhancement - New option added: "Old slug redirect"

= 2.2.7.6 =
* Dev - Code optimization for Bulk URI Editor
* Enhancement - Support for WooCommerce breadcrumbs
* Fix - A hotfix for WPForo plugin
* Enhancement - New filter "permalink_manager_chunk_size" that allows to control the chunk size in bulk tools ("Regenerate/reset", "Find & Replace")
* Enhancement - New filter "permalink_manager_sanitize_regex" that allows to adjust the function that sanitizes the custom permalinks
* Dev - Autoload for backup arrays is now disabled
* Enhancement - New option added: "Convert accented letters"

= 2.2.7.5 =
* Fix - CSS adjustments. Now the redirects box is displayed correctly in the URI editor

= 2.2.7.4 =
* Enhancement - Support for "Primary category" set with SEOPress & RankMath plugins
* Enhancement - Support for breadcrumbs added by SEOPress & RankMath plugins
* Dev - Improved "trailing slashes" functionality - untrailingslashit() & trailingslashit() replaced with REGEX based functions
* Enhancement - Possibility to remove custom permalinks, redirects, permastructure settings directly from "Debug" section
* Enhancement - New filter "permalink_manager_duplicates_priority" that allows to decide what content type ("posts" or "terms") should be loaded when the custom permalink is duplicated
* Fix - A minor fix for url_to_postid() function

= 2.2.7.3 =
* Enhancement - Support for "Primary category" set with The SEO Framework
* Dev - Changes for URI Editor section backend (SQL queries + improvements for search box)
* Enhancement - Improved support for WooCommerce Wishlist plugin
* Dev - Improvements for slugs sanitization functions
* Enhancement - Possibility to exclude posts from bulk tools added to "Auto-update the URI" dropdown in URI Editor

= 2.2.7.1 =
* Fix - Hotfix for PHP Fatal error in permalink-manager-admin-functions.php file

= 2.2.7 =
* Dev - Force 404 for draft posts (for non-logged-in users)
* Enhancement - New setting fields: "URI Editor role capability" & "Force HTTPS in URLs"
* Dev - Minor improvements

= 2.2.6 =
* Dev - More debug functions added
* Dev - Better support for Hebrew letters
* Enhancement - Support for location custom fields in WP Store Locator - CSV Manager plugin
* Enhancement - Improved support for Gutenberg editor (reported by Cedric Busuttil)

= 2.2.4/2.2.5 =
* Dev - Minor code improvements
* Dev - Yoast SEO Breadcrumbs - further improvements
* Fix - Hotfix for Toolset custom fields support (Permalink Manager Pro)
* Fix - Hotfix for Polylang URL modes

= 2.2.3 =
* Dev - Code improvements for WP All Import integration functions
* Fix - Hotfix for Elementor conflict with custom redirects function (Permalink Manager Pro)
* Enhancement - New field ("Do not automatically append the slug") in Permastructure settings added to each post type & taxonomy
* Enhancement - Basic support added for Mailster plugin
* Enhancement - New permastructure tag: "%monthname%"

= 2.2.2 =
* Dev - Code improvement for "Quick Edit" inline form
* Enhancement - Support for Yoast SEO breadcrumbs added
* Fix - Hotfix for Elementor

= 2.2.1.1/2.2.1.2/2.2.1.3/2.2.1.4 =
* Fix - Hotfix for function that detects custom URIs
* Dev - Custom URIs for WP All Import inported posts are now generated 30 seconds after the import job is completed

= 2.2.1 =
* Fix - Hotfix for Customizer (custom permalinks filters are disabled in admin panel)
* Dev - Minor UX improvements
* Enhancement - Partial support for TranslatePress plugin added
* Fix - Term permalinks are processed correctly when WPML enabled and "Adjust IDs for multilingual functionality" mode is activated
* Enhancement - New setting field separated from "Force custom slugs" - now, both the native slugs and special characters (.|_+) can be kept inside the slugs
* Enhancement - "permalink_manager_force_custom_slugs" filter added

= 2.2.0 =
* Fix - Hotfix for WPML - ?lang query parameter is now appended correctly
* Fix - Support for comment pages endpoint
* Dev - Minor code adjustments
* Enhancement - Metabox for Gutenberg enabled also for CPT
* Dev - Further improvements for redirect hooks
* Fix - Hotfix for WP Customizer
* Fix - Native slugs are saved correctly in Gutenberg editor
* Enhancement - "permalink_manager_filter_permastructure" filter added
* Enhancement - Permastructures can be now translated from admin panel

= 2.1.2.1/2.1.2.2 =
* Fix - Hotfix for "Force custom slugs" option - now special characters are not removed if "Yes, use post/term titles + do not strip special characters: .|-+" mode is set.
* Fix - Hotfix for custom fields support in custom permalinks

= 2.1.2 =
* Fix - Hotfix for WP All Import - default permalinks are now assigned correctly to imported posts + possibility to disable WP All Import custom URI functions in Permalink Manager settings
* Fix - Hotfix for Yoast SEO - notice displayed on author pages
* Dev - Adjustments for sanitize slug functions
* Enhancement - Basic support for Gutenberg added

= 2.1.1 =
* Enhancement - Support for draft custom permalinks
* Enhancement - Support for WP All Import plugin, now the custom permalinks can be defined directly in XML, CSV, ZIP, GZIP, GZ, JSON, SQL, TXT, DAT or PSV import files.
* Fix - Permalink_Manager_Pro_Functions::save_redirects() method - now the custom redirects are correctly saved when a custom permalink is updated.
* Fix - Hotfix for "Language name added as a parameter" mode in "WPML Language URL format" settings.
* Fix - Hotfix for canonical redirect triggered by WPML.
* Dev - Better support for non-latin letters in custom URIs & redirects
* Dev - Better support for endpoints
* Enhancement - Searchbox in URI Editors

= 2.1.0 =
* Enhancement - Support for "url_to_postid" function
* Dev - Bulk tools use now AJAX & transients to prevent timeout when large number of posts/terms is processed
* Fix - Fix for multi-domain language setup in WPML

= 2.0.6.5 =
* Enhancement - Support for %__sku% permastructure tag (WooCommerce) added - now SKU number can be added to the custom permalinks (Permalink Manager Pro)

= 2.0.6.4 =
* Dev - Code optimization
* Enhancement - 'permalink_manager_fix_uri_duplicates' filter added
* Enhancement - Possibility to display the native slug field
* Fix - License validation functions fixed

= 2.0.6.3.2 =
* Enhancement - Support added for Revisionize plugin
* Fix - Minor tweaks

= 2.0.6.2/2.0.6.3 =
* Enhancement - Japaneese translation added
* Dev - Some minor improvements
* Enhancement - New filters: permalink_manager_hide_uri_editor_term_{$term->taxonomy}, permalink_manager_hide_uri_editor_post_{$post->post_type} & permalink_manager_update_term_uri_{$this_term->taxonomy}, permalink_manager_update_post_uri_{$post->post_type}, permalink_manager_new_post_uri_{$post_object->post_type}
* Fix - Hotfix for default permalinks (no-hierarchical post types)
* Fix - Hotfix for attachments default permalinks + URI detect function

= 2.0.6.1 =
* Fix - Hotfix for endpoints in REGEX
* Fix - Minor bug fixed - native slugs are now correctly regenerated
* Fix - Hotfix for URI sanitization functions
* Fix - Hotfix for AMP plugin
* Enhancement - Full support for WPML multi-domain language setup
* Fix - Hotfix for VisualComposer + Yoast SEO JS functions
* Fix - Hotfix for WPML String Translation

= 2.0.6.0 =
* Fix - Minor bugs fixed
* Enhancement - New permastrutcure tag - %native_slug%
* Enhancement - "Force custom slugs" feature enhanced with new options
* Enhancement - Possibility to redirect the posts & terms to external URL (Permalink Manager Pro)

= 2.0.5.9 =
* Enhancement - New permastructure tags - %post_type% & %taxonomy%
* Enhancement- Support for "Taxonomy" custom field in ACF (Advanced Custom Fields)
* Fix - Minor fix for endpoints
* Enhancement - New hooks: "permalink_manager-filter-permalink-base" used instead of "permalink-manager-post-permalink-prefix" & "permalink-manager-term-permalink-prefix"

= 2.0.5.7/2.0.5.8 =
* Fix - MultilingualPress plugin
* Fix - Hotfix & better support for attachment post type (Media Library)
* Fix - Custom redirects for old permalinks are now correctly saved in Permalink Manager Pro
* Enhancement - Support for WooCommerce Wishlist plugin

= 2.0.5.6 =
* Fix - The URIs for trashed posts are now correctly removed
* Dev - Better support for non-ASCII characters in URIs
* Fix - Minor fix for hierarchical post types
* Fix Fix for coupon URL redirect
* Enhancement - New filter - "permalink-manager-force-hyphens"

= 2.0.5.5 =
* Enhancement - Discount URLs for WooCommerce - now the shop clients can use coupons' custom URIs to easily apply the discount to the cart
* Enhancement - Extra AJAX check for duplicated URIs in "Edit URI" box
* Enhancement - Wordpress CronJobs for "Automatically remove duplicates" functionality
* Dev - Extra improvements in "save_post/update_term" hooks
* Fix - Terms permalinks added via "Edit post" page
* Enhancement - "permalink-manager-force-lowercase-uris" filter added

= 2.0.5.4 =
* Enhancement - "permalink_manager_empty_tag_replacement" filter added
* Enhancement - New settings field for pagination redirect
* Enhancement - Trailing slashes are no longer added to custom permalinks ended with extension, eg. .html, or .php
* Fix - Term placeholder tags in taxonomies permastructures
* Fix - Page pagination improvement (404 error page for non-existing pages)

= 2.0.5.3 =
* Fix - Hotfix for redirects - redirect chain no longer occurs (WPML)
* Fix - Hotfix for ACF custom fields in terms
* Fix - "Trailling slashes" mode setting added, also the trailing slashes are removed from permalinks containing GET parameters or anchors (often used by 3rd party plugins)

= 2.0.5.2.2 =
* Fix - Hotfix for admin requests (+ compatibility with WooCommerce TM Extra Product Options)
* Fix - Hotfix for no-ASCII characters in custom URIs
* Fix - Hotfix for attachments

= 2.0.5.2.1 =
* Fix - Hotfix for endpoints redirect

= 2.0.5.1/2.0.5.2 =
* Dev - yoast_attachment_redirect setting removed (it is no longer needed)
* Dev - "yoast_primary_term" setting replaced with "permalink-manager-primary-term" filter
* Fix - REGEX rules
* Fix - Hotfix for WP All Import
* Fix - Hotfix for WooCommerce endpoints
* Dev - Better support for Polylang
* Enhancement - Support for Theme My Login plugin

= 2.0.5 =
* Enhancement - Now, the duplicates and unused custom permalinks can be automatically removed
* Enhancement - %{taxonomy}_flat% tag enhanced for post types permastructures
* Enhancement - Possibility to disable Permalink Manager functions for particular post types or taxonomies
* Dev - Better support for endpoints
* Dev - "Disable slug appendix" field is no longer needed
* Fix - Fix for WPML language prefixes in REGEX rule used to detect URIs

= 2.0.4.3 =
* Fix - Hotfix for problem with custom URIs for new terms & posts

= 2.0.4.2 =
* Trailing slashes redirect adjustment

= 2.0.4.1 =
* Fix - Hotfix for Elementor and another visual editor plugins
* Dev - Support for endpoints parsed as $_GET parameters

= 2.0.4 =
* Enhancement - New settings field - "Deep detect"

= 2.0.3.1 =
* Enhancement - Custom fields tags in permastructures settings

= 2.0.3 =
* Enhancement - Custom URI editor in "Quick Edit"
* Enhancement - New permastrutcure tag %category_custom_uri%
* Fix - "Quick/Bulk Edit" hotfix

= 2.0.2 =
* Fix - WooCommerce search redirect loop - hotfix

= 2.0.1 =
* Fix - WooCommerce endpoints hotfix
* Fix - Redirects save notices - hotfix

= 2.0.0 =
* Enhancement - Extra Redirects - possibility to define extra redirects for each post/term
* Enhancement - New "Tools" section - "Permalink Duplicates"
* Enhancement - UI improvements for taxonomies ("Custom URI" panel)
* Fix - Fixes for user reported bugs

= 1.11.6.3 =
* Fix - Slug appendix fix
* Fix - Hotfix for WooCommerce checkout

= 1.11.6 =
* Fix - Hotfix for taxonomy tags
* Fix - Hotfix for custom field tags
* Fix - Hotfix for Jetpack
* Enhancement - Suuport for WP All Import
* Enhancement - Support for Custom Permalinks

= 1.11.5.1 =
* Fix - "Custom URI" form issues
* Fix - for Yoast SEO & Visual Composer
* Enhancement - Possibility to choose if slugs should or should not be added to the default custom permalinks

= 1.11.4 =
* Fix - Hotfix for RSS feeds URLs

= 1.11.1 =
* Enhancement - Trailing slashes & Decode URIs - new settings
* Fix - "Bulk Edit" URI reset
* Dev - Partial code refactoring

= 1.11.0 =
* Fix - Hierarchical taxonomies fix
* Enhancement - 'permalink_manager_filter_final_term_permalink' filter added

= 1.10.2 =
* Fix - Taxonomies & permastructures fix

= 1.1.1 =
* Dev - UI improvements
* Fix - Fix for canonical redirects in WPML

= 1.1.0 =
* Dev - Partial code refactoring
* Dev - UI/UX improvements
* Enhancement - "Auto-update" feature
* Enhancement - Support for AMP plugin by Automattic

= 1.0.3 =
* Fix - Another pagination issue - hotfix

= 1.0.2 =
* Fix - Post pagination fix
* Enhancement - Basic REGEX support
* Enhancement - 'permalink_manager_filter_final_post_permalink' filter added

= 1.0.1 =
* Fix - WPML support fixes

= 1.0.0 =
* Dev - Further refactoring
* Dev - Some minor issues fixed
* Enhancement - WPML support added
* Enhancement - "Sample permalink" support added

= 0.5.2/0.5.3 =
* Another hotfix

= 0.5.1 =
* Hotfix for "Settings" section

= 0.5.0 =
* Code refactoring completed
* Interface changes
* Hooks enabled

= 0.4.9 =
* Hook for removed posts (their URI is now automatically removed)

= 0.4.8 =
* Pagination bug - SQL formula fix (offset variable)

= 0.4.7 =
* Strict standards - fix for arrays with default values

= 0.4.6 =
* 302 redirect fix.
* Code optimization.

= 0.4.5 =
* Bug with infinite loop fixed.
* Bug with revisions ID fixed.

= 0.4.4 =
* Redirect for old URIs added.
* Debug tools added.

= 0.4.3 =
* Hotfix for "Screen Options" save process.

= 0.4.2 =
* Hotfix for bulk actions' functions - additional conditional check for arrays added.

= 0.4.1 =
* Hotfix for "Edit Post" URI input (the URIs were reseted after "Update" button was pressed).

= 0.4 =
* Rewrite rules are no longer used (SQL queries are optimized). The plugin uses now 'request' filter to detect the page/post that should be loaded instead.
* Now full URI (including slug) is editable.
* A few major improvements applied.
* Partial code optimization.

= 0.3.4 =
* Hotfix for not working custom taxonomies tags.
* Now the rewrite rules for custom post types are stored in different way.

= 0.3.3 =
* Hotfix for bug with dynamic function names in PHP7.

= 0.3.2 =
* Hotfix for front-end permalinks. The custom permastructures worked only in wp-admin.

= 0.3.1 =
* Hotfix for Posts & Pages permastructures

= 0.3 =
* Now all permalink parts can be edited - new "Permalink Base Editor" section added.
* Code optimization.
* Bugfixes for Screen Options & Edit links.

= 0.2 =
* First public version.

= 0.1 =
* A first initial version.
