=== Wordfence Security - Firewall & Malware Scan ===
Contributors: mmaunder, wfryan, wfmatt, wfmattr
Tags: security, firewall, malware scanner, web application firewall, two factor authentication, block hackers, country blocking, clean hacked site, blocklist, waf, login security
Requires at least: 3.9
Requires PHP: 5.3
Tested up to: 5.7
Stable tag: 7.5.4

Secure your website with the most comprehensive WordPress security plugin. Firewall, malware scan, blocking, live traffic, login security & more.

== Description ==

### THE MOST POPULAR WORDPRESS FIREWALL & SECURITY SCANNER

Wordfence includes an endpoint firewall and malware scanner that were built from the ground up to protect WordPress. Our Threat Defense Feed arms Wordfence with the newest firewall rules, malware signatures and malicious IP addresses it needs to keep your website safe. Rounded out by 2FA and a suite of additional features, Wordfence is the most comprehensive WordPress security solution available.

#### WORDPRESS FIREWALL
* Web Application Firewall identifies and blocks malicious traffic. Built and maintained by a large team focused 100% on WordPress security.
* [Premium] Real-time firewall rule and malware signature updates via the Threat Defense Feed (free version is delayed by 30 days).
* [Premium] Real-time IP Blocklist blocks all requests from the most malicious IPs, protecting your site while reducing load.
* Protects your site at the endpoint, enabling deep integration with WordPress. Unlike cloud alternatives does not break encryption, cannot be bypassed and cannot leak data.
* Integrated malware scanner blocks requests that include malicious code or content.
* Protection from brute force attacks by limiting login attempts.

#### WORDPRESS SECURITY SCANNER
* Malware scanner checks core files, themes and plugins for malware, bad URLs, backdoors, SEO spam, malicious redirects and code injections.
* [Premium] Real-time malware signature updates via the Threat Defense Feed (free version is delayed by 30 days).
* Compares your core files, themes and plugins with what is in the WordPress.org repository, checking their integrity and reporting any changes to you.
* Repair files that have changed by overwriting them with a pristine, original version. Delete any files that don’t belong easily within the Wordfence interface.
* Checks your site for known security vulnerabilities and alerts you to any issues. Also alerts you to potential security issues when a plugin has been closed or abandoned.
* Checks your content safety by scanning file contents, posts and comments for dangerous URLs and suspicious content.
* [Premium] Checks to see if your site or IP have been blocklisted for malicious activity, generating spam or other security issue.

#### LOGIN SECURITY
* Two-factor authentication (2FA), one of the most secure forms of remote system authentication available via any TOTP-based authenticator app or service.
* Login Page CAPTCHA stops bots from logging in.
* Disable or add 2FA to XML-RPC.
* Block logins for administrators using known compromised passwords.

#### WORDFENCE CENTRAL
* Wordfence Central is a powerful and efficient way to manage the security for multiple sites in one place.
* Efficiently assess the security status of all your websites in one view. View detailed security findings without leaving Wordfence Central.
* Powerful templates make configuring Wordfence a breeze.
* Highly configurable alerts can be delivered via email, SMS or Slack. Improve the signal to noise ratio by leveraging severity level options and a daily digest option.
* Track and alert on important security events including administrator logins, breached password usage and surges in attack activity.
* Free to use for unlimited sites.

#### SECURITY TOOLS
* With Live Traffic, monitor visits and hack attempts not shown in other analytics packages in real time; including origin, their IP address, the time of day and time spent on your site.
* Block attackers by IP or build advanced rules based on IP Range, Hostname, User Agent and Referrer. 
* Country blocking available with Wordfence Premium.

== Installation ==

Secure your website using the following steps to install Wordfence:

1. Install Wordfence automatically or by uploading the ZIP file. 
2. Activate the Wordfence through the 'Plugins' menu in WordPress. Wordfence is now activated.
3. Go to the scan menu and start your first scan. Scheduled scanning will also be enabled.
4. Once your first scan has completed, a list of threats will appear. Go through them one by one to secure your site.
5. Visit the Wordfence options page to enter your email address so that you can receive email security alerts.
6. Optionally, change your security level or adjust the advanced options to set individual scanning and protection options for your site.
7. Click the "Live Traffic" menu option to watch your site activity in real-time. Situational awareness is an important part of website security.

To install Wordfence on WordPress Multi-Site installations:

1. Install Wordfence via the plugin directory or by uploading the ZIP file.
2. Network Activate Wordfence. This step is important because until you network activate it, your sites will see the plugin option on their plugins menu. Once activated that option disappears. 
3. Now that Wordfence is network activated it will appear on your Network Admin menu. Wordfence will not appear on any individual site's menu. 
4. Go to the "Scan" menu and start your first scan. 
5. Wordfence will do a scan of all files in your WordPress installation including those in the blogs.dir directory of your individual sites. 
6. Live Traffic will appear for ALL sites in your network. If you have a heavily trafficked system you may want to disable live traffic which will stop logging to the DB. 
7. Firewall rules and login rules apply to the WHOLE system. So if you fail a login on site1.example.com and site2.example.com it counts as 2 failures. Crawler traffic is counted between blogs, so if you hit three sites in the network, all the hits are totalled and that counts as the rate you're accessing the system.

== Frequently Asked Questions ==

[Visit our website to access our official documentation which includes security feature descriptions, common solutions and comprehensive help.](https://wordfence.com/help/)

= How does Wordfence Security protect sites from attackers? =

The WordPress security plugin provides the best protection available for your website. Powered by the constantly updated Threat Defense Feed, Wordfence Firewall stops you from getting hacked. Wordfence Scan leverages the same proprietary feed, alerting you quickly about security issues or if your site is compromised. The Live Traffic view gives you real-time visibility into traffic and hack attempts on your website. A deep set of additional tools round out the most comprehensive WordPress security solution available.

= What features does Wordfence Premium enable? =

We offer a Premium API key that gives you real-time updates to the Threat Defense Feed which includes a real-time IP blocklist, firewall rules, and malware signatures. Premium support, country blocking, more frequent scans, and spam and spamvertising checks are also included. [Click here to sign-up for Wordfence Premium now](http://www.wordfence.com/) or simply install Wordfence free and start protecting your website.

= How does the Wordfence WordPress Firewall protect websites? =

* Web Application Firewall stops you from getting hacked by identifying malicious traffic, blocking attackers before they can access your website.
* Threat Defense Feed automatically updates firewall rules that protect you from the latest threats. Premium members receive the real-time version.
* Block common WordPress security threats like fake Googlebots, malicious scans from hackers and botnets.

= What checks does the Wordfence Security Scanner perform? =

* Scans core files, themes and plugins against WordPress.org repository versions to check their integrity. Verify security of your source.
* See how files have changed. Optionally repair changed files that are security threats.
* Scans for signatures of over 44,000 known malware variants that are known WordPress security threats.
* Scans for many known backdoors that create security holes including C99, R57, RootShell, Crystal Shell, Matamu, Cybershell, W4cking, Sniper, Predator, Jackal, Phantasma, GFS, Dive, Dx and many more.
* Continuously scans for malware and phishing URL’s including all URLs on the Google Safe Browsing List in all your comments, posts and files that are security threats.
* Scans for heuristics of backdoors, trojans, suspicious code and other security issues.

= What security monitoring features does Wordfence include? =

* See all your traffic in real-time, including robots, humans, 404 errors, logins and logouts and who is consuming most of your content. Enhances your situational awareness of which security threats your site is facing.
* A real-time view of all traffic including automated bots that often constitute security threats that Javascript analytics packages never show you.
* Real-time traffic includes reverse DNS and city-level geolocation. Know which geographic area security threats originate from.
* Monitors disk space which is related to security because many DDoS attacks attempt to consume all disk space to create denial of service.

= What login security features are included =

* See all your traffic in real-time, including robots, humans, 404 errors, logins and logouts and who is consuming most of your content. Enhances your situational awareness of which security threats your site is facing.
* A real-time view of all traffic including automated bots that often constitute security threats that Javascript analytics packages never show you.
* Real-time traffic includes reverse DNS and city-level geolocation. Know which geographic area security threats originate from.
* Monitors disk space which is related to security because many DDoS attacks attempt to consume all disk space to create denial of service.

= How will I be alerted if my site has a security problem? =

Wordfence sends security alerts via email. Once you install Wordfence, you will configure a list of email addresses where security alerts will be sent. When you receive a security alert, make sure you deal with it promptly to ensure your site stays secure.

= Do I need a security plugin like Wordfence if I’m using a cloud based firewall (WAF)? =

Wordfence provides true endpoint security for your WordPress website. Unlike cloud based firewalls, Wordfence executes within the WordPress environment, giving it knowledge like whether the user is signed in, their identity and what access level they have. Wordfence uses the user’s access level in more than 80% of the firewall rules it uses to protect WordPress websites. Learn more about the [Cloud WAF identity problem here](https://www.wordfence.com/blog/2016/10/endpoint-vs-cloud-security-cloud-waf-user-identity-problem/). Additionally, cloud based firewalls can be bypassed, leaving your site exposed to attackers. Because Wordfence is an integral part of the endpoint (your WordPress website), it can’t be bypassed. Learn more about the [Cloud WAF bypass problem here](https://www.wordfence.com/blog/2016/10/endpoint-vs-cloud-security-cloud-waf-bypass-problem/). To fully protect the investment you’ve made in your website you need to employ a defense in depth approach to security. Wordfence takes this approach.

= What blocking features does Wordfence include? =

* Real-time blocking of known attackers. If another site using Wordfence is attacked and blocks the attacker, your site is automatically protected.
* Block entire malicious networks. Includes advanced IP and Domain WHOIS to report malicious IP’s or networks and block entire networks using the firewall. Report WordPress security threats to network owner.
* Rate limit or block WordPress security threats like aggressive crawlers, scrapers and bots doing security scans for vulnerabilities in your site.
* Choose whether you want to block or throttle users and robots who break your WordPress security rules.
* Premium users can also block countries and schedule scans for specific times and a higher frequency.

= What differentiates Wordfence from other WordPress Security plugins? =

* Wordfence Security provides a WordPress Firewall developed specifically for WordPress and blocks attackers looking for vulnerabilities on your site.  The Firewall is powered by our Threat Defense Feed which is continually updated as new threats emerge.  Premium customers receive updates in real-time.
* Wordfence verifies your website source code integrity against the official WordPress repository and shows you the changes. 
* Wordfence scans check all your files, comments and posts for URLs in Google's Safe Browsing list. We are the only plugin to offer this very important security enhancement.
* Wordfence scans do not consume large amounts of your bandwidth because all security scans happen on your web server which makes them very fast.
* Wordfence fully supports WordPress Multi-Site which means you can security scan every blog in your Multi-Site installation with one click.
* Wordfence includes Two-Factor authentication, the most secure way to stop brute force attackers in their tracks.
* Wordfence fully supports IPv6 including giving you the ability to look up the location of IPv6 addresses, block IPv6 ranges, detect IPv6 country and do a whois lookup on IPv6 addresses and more.

= Will Wordfence slow down my website? =

No. Wordfence Security is extremely fast and uses techniques like caching its own configuration data to avoid database lookups and blocking malicious attacks that would slow down your site.

= What if my site has already been hacked? =

Wordfence Security is able to repair core files, themes and plugins on sites where security is already compromised. You can follow this guide on [how to clean a hacked website](https://www.wordfence.com/docs/how-to-clean-a-hacked-wordpress-site-using-wordfence/) using Wordfence. However, please note that site security cannot be assured unless you do a full reinstall if your site has been hacked. We recommend you only use Wordfence Security to get your site into a running state in order to recover the data you need to do a full reinstall. If you need help repairing a hacked site, we offer an affordable, high-quality [site cleaning service](https://www.wordfence.com/wordfence-site-cleanings/) that includes a Premium key for a year.

= Does Wordfence Security support IPv6? =

Yes. We fully support IPv6 with all security functions including country blocking, range blocking, city lookup, whois lookup and all other security functions. If you are not running IPv6, Wordfence will work great on your site too. We are fully compatible with both IPv4 and IPv6 whether you run both or only one addressing scheme.

= Does Wordfence Security support Multi-Site installations? =

Yes. WordPress Multi-Site is fully supported. Using Wordfence you can scan every blog in your network for malware with one click. If one of your customers posts a page or post with a known malware URL that threatens your whole domain with being blocklisted by Google, we will alert you in the next scan.

= What support options are available for Wordfence users? =

Providing excellent customer service is very important to us.  We offer help to all our customers whether you are using the Premium or free version of Wordfence.  For help with the free version, you can post in our [forum](https://wordpress.org/support/plugin/wordfence) where we have dedicated staff responding to questions. If you need faster or more in-depth help, Premium customers can submit a [support ticket](https://support.wordfence.com/support/home) to our Premium support team.

= Where can I learn more about WordPress security? =

Designed for every skill level, [The WordPress Security Learning Center](https://www.wordfence.com/learn/) is dedicated to deepening users’ understanding of security best practices by providing free access to entry-level articles, in-depth articles, videos, industry survey results, graphics and more.

= Where can I find the Wordfence Terms of Use and Privacy Policy? =

These are available on our website: [Terms of Use](https://www.wordfence.com/terms-of-use/) and [Privacy Policy](https://www.wordfence.com/privacy-policy/)

== Screenshots ==

Secure your website with Wordfence. 

1. The dashboard gives you an overview of your site's security including notifications, attack statistics and Wordfence feature status.
2. The firewall protects your site from common types of attacks and known security vulnerabilities.
3. The Wordfence Security Scanner lets you know if your site has been compromised and alerts you to other security issues that need to be addressed.  
4. Wordfence is highly configurable, with a deep set of options available for each feature. High level scan options are shown above.
5. Brute Force Protection features protect you from password guessing attacks.
6. Block attackers by IP, Country, IP range, Hostname, Browser or Referrer.
7. The Wordfence Live Traffic view shows you real-time activity on your site including bot traffic and exploit attempts.
8. Take login security to the next level with Two-Factor Authentication.
9. Logging in is easy with Wordfence 2FA.

== Changelog ==

= 7.5.4 - June 7, 2021 =

* Fix: Resolve conflict with woocommerce-gateway-amazon-payments-advanced plugin

= 7.5.3 - May 10, 2021 =

* Improvement: Expanded WAF capabilities including better JSON and user permission handling
* Improvement: Switched to relative paths in WAF auto_prepend file to increase portability
* Improvement: Eliminated unnecessary calls to Wordfence servers
* Fix: Prevented errors on PHP 8.0 when disk_free_space and/or disk_total_space are included in disabled_functions
* Fix: Fixed PHP notices caused by unexpected plugin version data
* Fix: Gracefully handle unexpected responses from Wordfence servers
* Fix: Time field now displays correctly on "See Recent Traffic" overlay
* Fix: Corrected typo on Diagnostics page
* Fix: Corrected IP counts on activity report
* Fix: Added missing line break in scan result emails
* Fix: Sending test activity report now provides success/failure response
* Fix: Reduced SQLi false positives caused by comma-separated strings
* Fix: Fixed JS error when resolving last scan result

= 7.5.2 - March 24, 2021 =

* Fix: Fixed fatal error on single-sites running WordPress <4.9.

= 7.5.1 - March 24, 2021 =

* Fix: Fixed fatal error when viewing the Login Security settings page from an allowlisted IP.

= 7.5.0 - March 24, 2021 =

* Improvement: Translation-readiness: All user-facing strings are now run through WordPress's i18n functions.
* Improvement: Remove legacy admin functions no longer used within the UI.
* Improvement: Local GeoIP database update.
* Improvement: Remove Lynwood IP range from allowlist, and add new AWS IP range.
* Fix: Fixed bug with unlocking a locked out IP without correctly resetting its failure counters.
* Fix: Sites using deleted premium licenses correctly revert to free license behavior.
* Fix: When enabled, cookies are now set for the correct roles on previously used devices.
* Fix: WAF cron jobs are now skipped when running on the CLI.
* Fix: PHP 8.0 compatibility - prevent syntax error when linting files.
* Fix: Fixed issue where PHP 8 notice sometimes cannot be dismissed.

= 7.4.14 - December 3, 2020 =

* Improvement: Added option to disable application passwords.
* Improvement: Updated site cleaning callout with 1-year guarantee.
* Improvement: Upgraded sodium_compat library to 1.13.0.
* Improvement: Replaced the terms whitelist and blacklist with allowlist and blocklist.
* Improvement: Made a number of WordPress 5.6 and jQuery 3.x compatibility improvements.
* Improvement: Made a number of PHP8 compatilibility improvements.
* Improvement: Added dismissable notice informing users of possible PHP8 compatibility issues.

= 7.4.12 - October 21, 2020 =

* Improvement: Initial integration of i18n in Wordfence.
* Improvement: Prevent Wordfence from loading under <PHP 5.3.
* Improvement: Updated GeoIP database.
* Improvement: Prevented wildcard from running/saving for scan's excluded files pattern.
* Improvement: Included Wordfence Login Security tables in diagnostics missing table list.
* Fix: Removed new scan issues when WordPress update occurs mid-scan.
* Fix: Specified category when saving `whitelistedServiceIPs` to WAF storage engine.
* Fix: Removed localhost IP for auto-update email alerts.
* Fix: Fixed broken message in Live Traffic with MySQLi storage engine for blocklisted hits.
* Fix: Removed optional parameter values for PHP 8 compatibility.

= 7.4.11 - August 27, 2020 =

* Improvement: Added diagnostic debug button to clear Wordfence Central connection data from the database.
* Improvement: Added help documentation links to modified plugin/theme file scan results.
* Fix: Prevent file system scan from following symlinks to root.
* Fix: Cleared pending plugin/theme update scan results and notification when a plugin/theme is auto-updated.
* Fix: Added check for when site is disconnected on Central's end, but not in the plugin.

= 7.4.10 - August 5, 2020 =

* Improvement: Prevent author sitemap from leaking usernames in WordPress >= 5.5.0.
* Fix: Prevent Wordfence auto-update from running if the user has enabled auto-update through WordPress.
* Fix: Added default `permission_callback` params to Wordfence Central REST routes.
* Fix: Fixed missing styling on WAF optimization admin notice.

= 7.4.9 - July 8, 2020 =

* Improvement: Added list of known malicious usernames to suspicious administrator scan.
* Improvement: Added ability for the WAF to determine if a given plugin/theme/core version is installed.
* Improvement: Added a feature to export a diagnostics report.
* Improvement: Add php_errorlog to the list of downloadable logs in diagnostics.
* Improvement: Added a prompt to allow user to download a backup prior to repairing files.
* Improvement: Prevent scan from failing when the home URL has changed and the key is no longer valid.
* Improvement: Deprecated PHP 5.3, and ended PHP 5.2 support by prevent auto-update from running on older versions.
* Fix: Fixed issue where WAF mysqli storage engine cannot find credentials if wflogs/ does not exist.
* Fix: Changed capability checked to read WP REST API users endpoint when "Prevent discovery of usernames through ..." is enabled.
* Fix: Prevented duplicate queries for wordfenceCentralConnected wfconfig value.
* Fix: Prevented custom wp-content or other directories from appearing in "skipped paths" scan result, even when scanned.
* Fix: Login Attempts dashboard widget "Show more" link is not visible when long usernames and IPs cause wrapping.
* Fix: Fix typo in the readme.

= 7.4.8 - June 16, 2020 =
* Fix: Fixed issue with fatal errors encountered during activation under certain conditions.

= 7.4.7 - April 23, 2020 =
* Improvement: Updated bundled GeoIP database.
* Improvement: Better messaging when selecting restrictive rate limits.
* Improvement: Scan result emails now include the count of issues that were found again.
* Improvement: Resolved scan issues will now email again if they reoccur.
* Improvement: Added the state/province name when applicable to geolocation displays in Live Traffic.
* Improvement: New blocking page design to better inform blocked visitors on how to resolve the block.
* Improvement: Custom WP_CONTENT_DIR, WP_PLUGIN_DIR, and UPLOADS path constants will now get scanned correctly.
* Improvement: Added TLS connection failure detection to brute force reporting and checking and a corresponding backoff period.
* Fix: Fixed an issue where a bad cron record could interfere with automatic WAF rule updates.
* Fix: Fixed a PHP warning that could occur if a bad response was received while updating an IP list.
* Fix: The new user tour and onboarding flow will now work correctly on the 2FA page.

= 7.4.6 - February 12, 2020 =
* Improvement: Enhanced the detection ability of the WAF for SQLi attacks.
* Improvement: Updated the bundled GeoIP database.
* Improvement: Modified some country names in the block configuration to align with those shown in Live Traffic.
* Change: Moved the skipped files scan check to the Server State category.
* Fix: Fixed an issue where after scrolling on the Live Traffic page, updates would no longer automatically load.
* Fix: Modified the number of login records kept to align better with Live Traffic so they're trimmed around the same time.

= 7.4.5 - January 15, 2020 =
* Improvement: Improved WAF coverage for an Infinite WP authentication bypass vulnerability.

= 7.4.4 - January 14, 2020 =
* Fix: Fixed a UI issue where the scan summary status marker for malware didn't always match the findings.

= 7.4.3 - January 13, 2020 =
* Improvement: Added WAF coverage for an Infinite WP authentication bypass vulnerability.
* Improvement: The malicious URL scan now includes protocol-relative URLs (e.g., //example.com)
* Improvement: Malware signatures are now better applied to large files read in multiple passes.
* Improvement: Added a scan issue that will appear when one or more paths are skipped due to scan settings excluding them.
* Changed: AJAX endpoints now send the application/json Content-Type header.
* Changed: Updated text on scan issues for plugins removed from wordpress.org to better indicate possible reasons.
* Changed: Added compatibility messaging for reCAPTCHA when WooCommerce is active.
* Fixed: Added missing $wp_query->set_404() call when outputting a 404 page on a custom action.
* Fixed: Fixed the logout username display in Live Traffic broken by a change in WordPress 5.3.
* Fixed: Improved the response callback used for the WAF status check during extended protection installation.
* Fixed: The "Require 2FA for all administrators" notice is now automatically dismissed if an administrator sets up 2FA.

= 7.4.2 - December 3, 2019 =
* Improvement: Increased performance of IP CIDR range comparisons.
* Improvement: Added parameter signature to remote scanning for better validation during forking.
* Change: Removed duplicate browser label in Live Traffic.
* Fix: Added compensation for PHP 7.4 deprecation notice with get_magic_quotes_gpc.
* Fix: Fixed potential notice in dashboard widget when no updates are found.
* Fix: Updated JS hashing library to compensate for a variable name collision that could occur.
* Fix: Fixed an issue where certain symlinks could cause a scan to erroneously skip files.
* Fix: Fixed PHP memory test for newer PHP versions whose optimizations prevented it from allocating memory as desired.

= 7.4.1 - November 6, 2019 =
* Improvement: Updated the bundled GeoIP database.
* Improvement: Minor changes to ensure compatibility with PHP 7.4.
* Improvement: Updated the WHOIS lookup for better reliability.
* Improvement: Added better diagnostic data when the WAF MySQL storage engine is active.
* Improvement: Improved the messaging when switching between premium and free licenses.
* Change: Deprecated DNS changes scan.
* Change: The plugin will no longer email alerts when Central is managing them.
* Fix: Added error suppression to ignore_user_abort calls to silence it on hosts with it disabled.
* Fix: Improved path generation to better avoid outputting extra slashes in URLs.
* Fix: Applied a length limit to malware reporting to avoid failures due to large content size.

= 7.4.0 - August 22, 2019 =
* Improvement: Added a MySQL-based configuration and data storage for the WAF to expand the number of hosting environments supported. For more detail, see: https://www.wordfence.com/help/firewall/mysqli-storage-engine/
* Improvement: Updated bundled GeoIP database.
* Fix: Fixed several console notices when running via the CLI.

= 7.3.6 - July 31, 2019 =
* Improvement: Multiple "php.ini file in core directory" issues are now consolidated into a single issue for clearer scan results.
* Improvement: The AJAX error detection for false positive WAF blocks now better detects and processes the response for presenting the allowlisting prompt.
* Improvement: Added overdue cron detection and highlighting to diagnostics to help identify issues.
* Improvement: Added the necessary directives to exclude backwards compatibility code from creating warnings with phpcs for future compatibility with WP Tide.
* Improvement: Normalized all PHP require/include calls to use full paths for better code quality.
* Change: Removed deprecated high sensitivity scan option since current signatures are more accurate.
* Fix: Fixed the status circle tooltips not showing.
* Fix: IP detection at the WAF level better mirrors the main plugin exactly when using the automatic setting.
* Fix: Fixed a currently-unused code path in email address verification for the strict check.

= 7.3.5 - July 16, 2019 =
* Improvement: Improved tagging of the login endpoint for brute force protection.
* Improvement: Added additional information about reCAPTCHA to its setting control.
* Improvement: Added a constant that may be overridden to customize the expiration time of login verification email links.
* Improvement: reCAPTCHA keys are now tested on saving to prevent accidentally inputting a v2 key.
* Improvement: Added a setting to control the reCAPTCHA human/bot threshold.
* Improvement: Added a separate option to trigger removal of Login Security tables and data on deactivation.
* Improvement: Reworked the reCAPTCHA implementation to trigger the token check on login/registration form submission to avoid the token expiring.
* Fix: Widened the reCAPTCHA key fields to allow the full keys to be visible.
* Fix: Fixed encoding of the ellipsis character when reporting malware finds.
* Fix: Disabling the IP blocklist once again correctly clears the block cache.
* Fix: Addressed an issue when outbound UDP connections are blocked where the NTP check could log an error.
* Fix: Added handling for reCAPTCHA's JavaScript failing to load, which previously blocked logging in.
* Fix: Fixed the functionality of the button to send 2FA grace period notifications.
* Fix: Fixed a missing icon for some help links when running in standalone mode.

= 7.3.4 - June 17, 2019 =
* Improvement: Added security events and alerting features built into Wordfence Central.

= 7.3.3 - June 11, 2019 =
* Improvement: Added support for managing the login security settings to Wordfence Central.
* Improvement: Updated the bundled root CA certificate store.
* Improvement: Added a check and update flow for mod_php hosts with only the PHP5 directive set for the WAF's extended protection mode.
* Improvement: Added additional values to Diagnostics for debugging time-related issues, the new fatal error handler settings, and updated the PHP version check to reflect the new 5.6.20 requirement of WordPress.
* Change: Changed the autoloader for our copy of sodium_compat to always load after WordPress core does.
* Fix: Fixed the "removed from wordpress.org" detection for plugin, which was broken due to an API change.
* Fix: Fixed the bulk repair function in the scan results when it included core files.

= 7.3.2 - May 16, 2019 =
* Improvement: Updated sodium_compat to address an incompatibility that may occur with the pending WordPress 5.2.1 update.
* Improvement: Clarified text around the reCAPTCHA setting to indicate v3 keys must be used.
* Improvement: Added detection for Jetpack and a notice when XML-RPC authentication is disabled.
* Fix: Suppressed error messages on the NTP time check to compensate for hosts with UDP connections disabled.

= 7.3.1 - May 14, 2019 =
* Improvement: Two-factor authentication is new and improved, now available on all Premium and Free installations.
* Improvement: Added Google reCAPTCHA v3 support to the login and registration forms.
* Improvement: XML-RPC authentication may now be disabled or forced to require 2FA.
* Improvement: Reduced size of SVG assets.
* Improvement: Clarified text on "Maximum execution time for each scan stage" option.
* Improvement: Added detection for an additional config file that may be created and publicly visible on some hosts.
* Improvement: Improved detection for malformed malware scanning signatures.
* Change: Long-deprecated database tables will be removed.
* Change: Removed old performance logging code that's no longer used.
* Fix: Addressed a log notice when using the See Recent Traffic feature in Live Traffic.
* Fix: WAF attack data now correctly includes JSON payloads when appropriate.
* Fix: Fixed the text for Live Traffic entries that include a redirection message.
* Fix: Fixed an issue with synchronizing scan issues to Wordfence Central that prevented stale issues from being cleared.

= 7.2.5 - April 18, 2019 =
* Improvement: Added additional data breach records to the breached password check.
* Improvement: Added the Accept-Encoding compression header to WAF-related requests for better performance during rule updates.
* Improvement: Updated to the current GeoIP database.
* Improvement: Added additional controls to the Wordfence Central connection page to better reflect the current connection state.
* Change: Updated the text on the option to alert for scan results of a certain severity.

= 7.2.4 - March 26, 2019 =
* Improvement: Updated vulnerability database integration.
* Improvement: Better messaging when a WAF rule update fails to better indicate the cause.
* Fix: Removed a double slash that could occur in an image path.
* Fix: Adjusted timeouts to improve reliability of WAF rule updates on slower servers.
* Fix: Improved connection process with Wordfence Central for better reliability on servers with non-standard paths.
* Fix: Switched to autoloader with fastMult enabled on sodum_compat to minimize connection issues.

= 7.2.3 - February 28, 2019 =
* Improvement: Country names are now shown instead of two letter codes where appropriate.
* Improvement: Updated the service allowlist to reflect additions to the Facebook IP ranges.
* Improvement: Added alerting for when the WAF is disabled for any reason.
* Improvement: Additional alerting and troubleshooting steps for WAF configuration issues.
* Change: Live Traffic human/bot status will additionally be based on the browscap record in security-only mode.
* Change: Added dismissible prompt to switch Live Traffic to security-only mode.
* Fix: The scan issues alerting option is now set correctly for new installations.
* Fix: Fixed a transparency issue with flags for Switzerland and Nepal.
* Fix: Fixed the malware link image rendering in scan issue emails and switched to always use https.
* Fix: WAF-related scheduled tasks are now more resilient to connection timeouts or memory issues.
* Fix: Fixed Wordfence Central connection flow within the first time experience.

= 7.2.2 - February 14, 2019 =
* Improvement: Updated GeoIP database.
* Fix: Syncing requests from Wordfence Central no longer appear in Live Traffic.
* Fix: Addressed some display issues with the Wordfence Central panel on the Wordfence Dashboard.

= 7.2.1 - February 5, 2019 =
* Improvement: Integrated Wordfence with Wordfence Central, a new service allowing you to manage multiple Wordfence installations from a single interface.
* Improvement: Added a help link to the mode display when a host disabling Live Traffic is active.
* Improvement: Added an option for allowlisting ManageWP in "Allowlisted Services".
* Fix: Enqueued fonts used in admin notices on all admin pages.
* Fix: Change false positive user-reports link to use https.
* Fix: Fix reference to non-existent function when registering menus.

= 7.1.20 - January 8, 2019 =
* Fix: Fixed a commit error with 7.1.19

= 7.1.19 - January 8, 2019 =
* Improvement: Speed optimizations for WAF rule compilation.
* Improvement: Added Kosovo to country blocking.
* Improvement: Additional flexibility for allowlist rules.
* Fix: Added compensation for really long file lists in the "Exclude files from scan" setting.
* Fix: Fixed an issue where the GeoIP database update check would never get marked as completed.
* Fix: Login credentials passed as arrays no longer trigger a PHP notice from our filters.
* Fix: Text fixes to the WAF nginx help text.

= 7.1.18 - December 4, 2018 =
* Improvement: Removed unused font glyph ranges to reduce file count and size.
* Improvement: Switched flags to use a CSS sprite to reduce file count and size.
* Improvement: Added dates to each release in the changelog.
* Change: Live Traffic now defaults to only logging security events on new installations.
* Change: Added an upper limit to the maximum scan stage execution time if not explicitly overridden.
* Fix: Changed WAF file handling to skip some file actions if running via the CLI.
* Fix: Fixed an issue that could prevent files beginning with a period from working with the file restore function.
* Fix: Improved layout of options page controls on small screens.
* Fix: Fixed a typo in the htaccess update panel.
* Fix: Added compensation for Windows path separators in the WAF config handling.
* Fix: Fixed handling of case-insensitive tables in the Diagnostics table check.
* Fix: Better messaging by the status circles when the WAF config is inaccessible or corrupt.
* Fix: REST API hits now correctly follow the "Don't log signed-in users with publishing access" option.

= 7.1.17 - November 6, 2018 =
* Improvement: Increased frequency of filesystem permission check and update of the WAF config files.
* Improvement: More complete data removal when deactivating with remove tables and files checked.
* Improvement: Better diagnostics logging for GeoIP conflicts.
* Fix: Text fix in invalid username lockout message.
* Fix: PHP 7.3 syntax compatibility fixes.

= 7.1.16 - October 16, 2018 =
* Improvement: Service allowlisting can now be selectively toggled on or off per service.
* Improvement: Updated bundled GeoIP database.
* Change: Removed the "Disable Wordfence Cookies" option as we've removed all cookies it affected.
* Change: Updates that refresh country statistics are more efficient and now only affect the most recent records.
* Change: Changed the title of the Wordfence Dashboard so it's easier to identify when many tabs are open.
* Fix: Fixed an issue with country blocking and XML-RPC requests containing credentials.

= 7.1.15 - October 1, 2018 =
* Fix: Addressed a plugin conflict with the composer autoloader.

= 7.1.14 - October 1, 2018 =
* Improvement: Reduced queries and potential table size for rate limiting-related data.
* Improvement: Updated the internal browscap database.
* Improvement: Better error reporting for scan failures due to connectivity issues.
* Improvement: WAF-related file permissions will now lock down further when possible.
* Improvement: Hardening for sites on servers with insecure configuration, which should not be enabled on publicly accessible servers. Thanks Janek Vind.
* Change: Switched the minimum PHP version to 5.3.
* Fix: Prevent bypass of author enumeration prevention by using invalid parameters. Thanks Janek Vind.
* Fix: Wordfence crons will now automatically reschedule if missing for any reason.
* Fix: Fixed an issue where the block counts and total IPs blocked values on the dashboard might not agree.
* Fix: Corrected the message shown on Live Traffic when a country blocking bypass URL is used.
* Fix: Removed extra spacing in the example ranges for "Allowlisted IP addresses that bypass all rules"

= 7.1.12 - September 12, 2018 =
* Improvement: Updated bundled GeoIP database.
* Improvement: Restructured the WAF configuration storage to be more resilient on hosts with no file locking support.
* Change: Moved the settings import/export to the Tools page.
* Change: New installations will now use lowercase table names to avoid issues with some backup plugins and Windows-based sites.
* Fix: The notice and repair link for an unreadable WAF configuration now work correctly.
* Fix: Improved appearance of some stat components on smaller screens.
* Fix: Fixed duplicate entries with different status codes appearing in detailed live traffic.
* Fix: Added better caching for the breached password check to compensate for sites that prevent the cache from expiring correctly.
* Fix: Changing the frequency of the activity summary email now reschedules it.

= 7.1.11 - August 21, 2018 =
* Improvement: Added a custom message field that will show on all block pages.
* Improvement: Improved the standard appearance for block pages.
* Improvement: Live Traffic now better displays failed logins.
* Improvement: Added a constant to prevent direct MySQLi use for hosts with unsupported DB configurations.
* Improvement: Malware scan results have been modified to include both a public identifier and description.
* Change: Description updated on the Live Traffic page.
* Fix: Removed an empty file hash from the old Wordpress core file detection.
* Fix: Update locking now works on multisites that have removed the original site.

= 7.1.10 - July 31, 2018 =
* Improvement: Better labeling in Live Traffic for 301 and 302 redirects.
* Improvement: Login timestamps are now displayed in the site's configured time zone rather than UTC.
* Improvement: Added detection and a workaround for hosts with a non-functional MySQLi interface.
* Improvement: The prevent admin registration setting now works with WooCommerce's registration flow.
* Improvement: For hosts with varying URL values (e.g., AWS instances), notification and alert links now correctly use the canonical admin URL.
* Fix: Fixed a layout problem with the live traffic disabled notice.
* Fix: The scan stage that checks "How does Wordfence get IPs?" no longer shows a warning if the call fails.

= 7.1.9 - July 12, 2018 =
* Improvement: Added an "unsubscribe" link to plugin-generated alerts.
* Improvement: Added some additional flags.
* Change: Removed some unnecessary files from the bundled GeoIP library.
* Change: Updated wording in the Terms of Use/Privacy Policy agreement UI.
* Change: The minimum "Lock out after how many login failures" is now 2.
* Change: The diagnostics report now includes the scan issues for easier debugging.
* Fix: Multiple improvements to automatic updating to avoid broken updates on sites with low resources or slow file systems.
* Fix: Better text wrapping in the top failed logins widget.
* Fix: Onboarding CSS/JS is now correctly enqueued for multisite installations.
* Fix: Fixed a missing asset with the bundled jQueryUI library.
* Fix: Fixed memory calculation when using PHP's supported shorthand syntax.
* Fix: Better wrapping behavior on the reason column in the blocks table.
* Fix: Fixed an issue with an internal data structure to prevent error log entries when using mbstring functions.
* Fix: Improved bot detection when no user agent is sent.

= 7.1.8 - June 26, 2018 =
* Improvement: Better detection of removal status when uninstalling the WAF's auto-prepend file.
* Improvement: Switched optional mailing list signup to go directly through our servers rather than a third party.
* Fix: Fixed the dashboard erroneously showing the payment method as missing for some payment methods.
* Fix: If a premium license is deleted from wordfence.com, the plugin will now automatically downgrade rather than get stuck in an intermediate state.
* Fix: Changed some wording to consistently use "License" or "License Key".

= 7.1.7 - June 5, 2018 =
* Improvement: Added better support for keyboard navigation of options.
* Improvement: staging. and dev. subdomains are now supported for sharing premium licenses.
* Improvement: Bundled our interface font to avoid loading from a remote source and reduced the pages some assets were loaded on.
* Improvement: Added option to trim Live Traffic records after a specific number of days.
* Improvement: Updated to the current GeoIP2 database.
* Improvement: Extended the automatic redaction applied to attack data that may include sensitive information.
* Change: Removed a no-longer-used API call.
* Fix: Fixed a few options that couldn't be searched for on the all options page.
* Fix: Activity Report emails now detect and avoid symlink loops.

= 7.1.6 - May 22, 2018 =
* Fix: Added a workaround for sites with inaccessible WAF config files when reading php://input

= 7.1.5 - May 22, 2018 =
* Improvement: GDPR compliance updates.
* Improvement: The list of blocks now shows the most recently-added blocks at the top by default.
* Improvement: Added better table status display to Diagnostics to help with debugging.
* Improvement: Added deferred loading to Live Traffic avatars to improve performance with some plugins.
* Improvement: The server's own IP is now automatically allowlisted for known safe requests.
* Fix: Added a workaround to Live Traffic human/bot detection to compensate for other scripts that modify our event handlers.
* Fix: Fixed an error with Live Traffic human/bot detection when plugins change the load order.
* Fix: Fixed auto-enabling of some controls when pasting values.
* Fix: Fixed an instance where http links could be generated for emails rather than https.

= 7.1.4 - May 2, 2018 =
* Improvement: Added additional XSS detection capabilities.
* Change: Initial preparation for GDPR compliance. Additional changes will be included in an upcoming release to meet the GDPR deadline.
* Change: Reworked Live Traffic/Rate Limiting human and bot detection to function without cookies.
* Change: Removed the wfvt_ cookie as it was no longer necessary.
* Change: Better debug messaging for scan forking.
* Fix: PHP deprecation notices no longer suppress those of old OpenSSL or WordPress.
* Fix: Fixes to the deprecated OpenSSL version detection and alerting to handle non-patch version numbers.
* Fix: Added detection for and fixed a very large pcre.backtrack_limit setting that could cause scans to fail, when modified by other plugins.
* Fix: Scan issue alert emails no longer incorrectly show high sensitivity was enabled.
* Fix: Fixed wrapping of long strings on the Diagnostics page.

= 7.1.3 - April 18, 2018 =
* Improvement: Improved the performance of our config table status check.
* Improvement: The IP address of the user activating Wordfence is now used by the breached password check until an admin successfully logs in.
* Improvement: Added several new error displays for scan failures to help diagnose and fix issues.
* Improvement: Added the block duration to alerts generated when an IP is blocked.
* Improvement: A text version of scan results is now included in the activity log email.
* Improvement: The WAF install/uninstall process no longer asks to backup files that do not exist.
* Change: Began a phased rollout of moving brute force queries to be https-only.
* Change: Added the initial deprecation notice for PHP 5.2.
* Change: Suppressed a script tag on the diagnostics page from being output in the email version.
* Fix: Addressed an issue where plugins that return a null user during authentication would cause a PHP notice to be logged.
* Fix: Fixed an issue where plugins that use non-standard version formatting could end up with a inaccurate vulnerability status.
* Fix: Added a workaround for web email clients that erroneously encode some URL characters (e.g., #).

= 7.1.2 - April 4, 2018 =
* Improvement: Added support for filtering the blocks list.
* Improvement: Added a flow for generating the WAF autoprepend file and retrieving the path for manual installations.
* Improvement: Added a variety of new data values to the Diagnostics page to aid in debugging issues.
* Improvement: SVG files now have the JavaScript-based malware signatures run against them.
* Improvement: More descriptive text for the scan issue email when there's an unknown WordPress core version.
* Improvement: Added a dedicated error display that will show when a scan is detected as failed.
* Improvement: readme.html and wp-config-sample.php are no longer scanned for changes due to differences between languages (malware signatures still run).
* Improvement: When the license status changes, it now triggers a fresh pull of the WAF rules.
* Improvement: Added dedicated messaging for leftover WordPress core files that were not fully removed during upgrade.
* Improvement: Improved labeling in Live Traffic for hits blocked by the real-time IP blocklist.
* Improvement: Added forced wrapping to the file paths in the activity report email to avoid scroll bar overlap making them unreadable.
* Improvement: Updated the bundled GeoIP database.
* Improvement: Updated the bundled browscap database.
* Improvement: All emailed alerts now include a link to the generating site.
* Change: Minor text change to unify some terminology.
* Fix: Removed a remaining reference to the CDN version of Font Awesome.
* Fix: Removed an old reference to the pre-Wordfence 7.1 lockouts table.
* Fix: Scan results for malware detections in posts are no longer clickable.
* Fix: We now verify that there's a valid email address defined before attempting to send an alert and filter out any invalid ones.
* Fix: Added a workaround for GoDaddy/Limit Login Attempts suppressing the 2FA prompting.

= 7.1.1 - March 20, 2018 =
* Improvement: Added the ability to sort the blocks table.
* Improvement: Added short-term caching of breach check results.
* Improvement: The check for passwords leaked in breaches now allows a login if the user has previously logged in from the same IP successfully and displays an admin notice suggesting changing the password.
* Improvement: Switched the bundled select2 library to use to prefixed version to work around other plugins including older versions on our pages.
* Improvement: The scan page now displays when beta signatures are enabled since they can produce false positives.
* Improvement: Improved positioning of the "Wordfence is Working" message.
* Improvement: Added a character limit to the reason on blocks and forced wrapping to avoid the layout stretching too much.
* Fix: Fixed an issue with some table prefixing where multisite installations with rare configurations could result in unknown table warnings.
* Fix: Removed an older behavior with live traffic buttons that could allow them to open in a new tab and show nothing.
* Fix: Added a check for sites with inaccurate disk space function results to avoid showing an issue.
* Fix: Added a secondary check to the email summary cron to avoid repeated sending if the cron list is corrupted.
* Fix: Fixed a typo on the Advanced Comment Spam Filter page.

= 7.1.0 - March 1, 2018 =
* Improvement: Added a new feature to prevent attackers from successfully logging in to admin accounts whose passwords have been in data breaches.
* Improvement: Added pagination support to the scan issues.
* Improvement: Improved time zone handling for the WAF's learning mode.
* Improvement: Improved messaging on file-related scan issues when the file is wp-config.php.
* Improvement: Modified the appearance of the "How does Wordfence get IPs" option to be more clear.
* Improvement: Better messaging about the scan options that need to be enabled for free installations to achieve 100%.
* Improvement: The country blocking selection drawer behavior has been changed to now allow saving directly from it.
* Improvement: Increased the textarea size for the advanced firewall options to make editing easier.
* Improvement: The URL blocklist check now includes additional variants in some checks to more accurately match.
* Change: Adjusted messaging when blocks are loading.
* Change: Wording change for the option "Maximum execution time for each stage".
* Change: Permanent blocks now display "Permanent" rather than "Indefinite" for the expiration for consistency.
* Fix: Fixed the initial status code recorded for lockouts and blocks.
* Fix: Fixed PHP notices that could occur when using the bulk delete/repair scan tools.
* Fix: Improved the state updating for the scan bulk action buttons.
* Fix: Usernames in live traffic now correctly link to the corresponding profile page.
* Fix: Addressed a PHP warning that could occur if wordpress.org returned a certain format for the abandoned plugin check.
* Fix: Fixed a possible PHP notice when syncing attack data records without metadata attached.
* Fix: Modified the behavior of the disk space check to avoid a scan warning showing without an issue generated.
* Fix: Fixed a CSS glitch where the top controls could have extra space at the top when sites have long navigation menus.
* Fix: Updated some wording in the All Options search box.
* Fix: Removed an old link for "See Recent Traffic" on Live Traffic that went nowhere.

= 7.0.4 - February 12, 2018 =
* Change: Live Traffic records are no longer created for hits initiated by WP-CLI (e.g., manually running cron).
* Fix: Fixed an issue where the human/bot detection wasn't functioning.

= 7.0.4 =
* Fix: Re-added missing file to fix commit excluding it.

= 7.0.3 - February 12, 2018 =
* Improvement: Added an "All Options" page to enable developers and others to more rapidly configure Wordfence.
* Improvement: Improved messaging for when a page has been open for more than a day and the security token expires.
* Improvement: Relocated the "Always display expanded Live Traffic records" option to be more accessible.
* Improvement: Improved appearance and behavior of option checkboxes.
* Improvement: For plugins with incomplete header information, they're now shown with a fallback title in scan results as appropriate.
* Improvement: The country block rule in the blocks table now shows a count rather than a potentially large list of countries.
* Change: Modified behavior of the advanced country blocking options to always show.
* Fix: Fixed the "Make Permanent" button behavior for blocks created from Live Traffic.
* Fix: Better synchronization of block records to the WAF config to avoid duplicate queries.
* Fix: The diff viewer now forces wrapping to prevent long lines of text from stretching the layout.
* Fix: Fixed an issue where the scanned plugin count could be inaccurate due to forking during the plugin scan.
* Fix: Adjusted sizing on the country blocking options to prevent placeholder text from being cut off at some screen sizes.
* Fix: Block/Unblock now works correctly when viewing Live Traffic with it grouped by IP.
* Fix: Fixed an issue where the count of URLs checked was incorrect.

= 7.0.2 - January 31, 2018 =
* Improvement: Added CSS/JS filename versioning to address caching plugins not refreshing for plugin updates.
* Improvement: The premium key is no longer prompted for during installation if already present from an earlier version.
* Improvement: Added a check and corresponding notice if the WAF config is unreadable or invalid.
* Improvement: Improved live traffic sizing on smaller screens.
* Improvement: Added tour coverage for live traffic.
* Change: IPs blocked via live traffic now use the configurable how long is an IP blocked setting to match previous behavior.
* Change: Changed the option to enable live traffic to match the wording and style of other options.
* Change: Changed styling on the unknown country display in live traffic to match the common coloring.
* Change: Statistics that do not depend on the WAF for their data now display when it is in learning mode.
* Change: Scan issues that are indicative of a compromised site are moved to the top of the list.
* Change: Changed styling on unselected checkboxes.
* Fix: Quick scans no longer run daily if automatic scheduled scans are disabled.
* Fix: The update check in a quick scan no longer runs if the update check has been turned off for regular scans.
* Fix: Fixed the quick navigation letters in the country picker not scrolling.
* Fix: Fixed editing the country block configuration when there are a large number of other blocks.
* Fix: Addressed an issue where having the country block or a pattern block selected when clicking Make Permanent could break them.
* Fix: Live traffic entries with long user agents no longer cause the table to stretch.
* Fix: Fixed an issue where live traffic would stop loading new records if always display expanded records was on.
* Fix: Suppressed warnings on IP conversion functions when processing potentially incomplete data.
* Fix: Added a check in REST API hooks to avoid defining a constant twice.

= 7.0.1 - January 24, 2018 =
* Comprehensive UI refresh.
* Improvement: Updated bundled GeoIP database.

= 6.3.22 - November 30, 2017 =
* Fix: Addressed a warning that could occur on PHP 7.1 when reading php.ini size values.
* Fix: Fixed a warning by adjusting a query to remove old-style variable references.

= 6.3.21 - November 1, 2017 =
* Improvement: Updated bundled GeoIP database.
* Fix: Fixed a log warning that could occur during the scan for plugins not in the wordpress.org repository.

= 6.3.20 - October 12, 2017 =
* Improvement: The scan will now alert for a publicly visible .user.ini file.
* Fix: Fixed status code and human/bot tagging of block hit entries for live traffic and the Wordfence Security Network.
* Fix: Added internal throttling to ensure the daily cron does not run too frequently on some hosts.

= 6.3.19 - September 20, 2017 =
* Emergency Fix: Updated wpdb::prepare calls using %.6f since it is no longer supported.

= 6.3.18 - September 7, 2017 =
* Improvement: Reduced size of some JavaScript for faster loading.
* Improvement: Better block counting for advanced comment filtering.
* Improvement: Increased logging in debug mode for plugin updates to help resolve issues.
* Fix: Reduced the minimum duration of a scan stage to improve reliability on some hosts.

= 6.3.17 - August 24, 2017 =
* Improvement: Prepared code for upcoming scan improvement which will greatly increase scan performance by optimizing malware signatures.
* Improvement: Updated the bundled GeoIP database.
* Improvement: Better scan messaging when a publicly-reachable searchreplacedb2.php utility is found.
* Improvement: The no-cache constant for database caching is now set for W3TC for plugin updates and scans.
* Improvement: Added an additional home/siteurl resolution check for WPML installations.

= 6.3.16 - August 8, 2017 =
* Improvement: Introduced a new scan stage to check for malicious URLs and content within WordPress core, plugin, and theme options.
* Improvement: New scan stage includes a new check for TrafficTrade malware.
* Improvement: Reduced net memory usage during forked scan stages by up to 50%.
* Improvement: Reduced the number of queries executed for some configuration options.
* Improvement: Modified the default allowlisting to include the new core AJAX action in WordPress 4.8.1.
* Fix: Synchronized the scan option names between the main options page and smaller scan options page.
* Fix: Fixed CSS positioning issue for dashboard metabox with IPv6.
* Fix: Fixed a compatibility issue with determining the site's home_url when WPML is installed.

= 6.3.15 - July 24, 2017 =
* Improvement: Reduced memory usage on scan forking and during the known files scan stage.
* Improvement: Added additional scan options to allow for disabling the blocklist checks while still allowing malware scanning to be enabled.
* Improvement: Added a Wordfence Application Firewall code block for the lsapi variant of LiteSpeed.
* Improvement: Updated the bundled GeoIP database.
* Fix: Added a validation check to IP range allowlisting to avoid log warnings if they're malformed.

= 6.3.14 - July 17, 2017 =
* Improvement: Introduced smart scan distribution. Scan times are now distributed intelligently across servers to provide consistent server performance. 
* Improvement: Introduced light-weight scan that runs frequently to perform checks that do not use any server resources. 
* Improvement: If unable to successfully look up the status of an IP claiming to be Googlebot, the hit is now allowed.
* Improvement: Scan issue results for abandoned plugins and unpatched vulnerabilities include more info.
* Fix: Suppressed PHP notice with time formatting when a microtimestamp is passed.
* Fix: Improved binary data to HTML entity conversion to avoid wpdb stripping out-of-range UTF-8 sequences.
* Fix: Added better detection to SSL status, particularly for IIS.
* Fix: Fixed PHP notice in the diff renderer.
* Fix: Fixed typo in lockout alert.

= 6.3.12 - June 28, 2017 =
* Improvement: Adjusted the password audit to use a better cryptographic padding option.
* Improvement: Improved the option value entry process for the modified files exclusion list.
* Improvement: Added rel="noopener noreferrer" to all external links from the plugin for better interoperability with other scanners.
* Improvement: Added support to the WAF for validating URLs for future use in rules.
* Fix: Time formatting will now correctly handle :30 and :45 time zone offsets.
* Fix: Hosts using mod_lsapi will now be detected as Litespeed for WAF optimization.
* Fix: Added an option to allow automatic updates to function on Litespeed servers that have the global noabort set rather than site-local.
* Fix: Fixed a PHP notice that could occur when running a scan immediately after removing a plugin.

= 6.3.11 - June 15, 2017 =
* Improvement: The scan will alert for plugins that have not been updated in 2+ years or have been removed from the wordpress.org directory. It will also indicate if there is a known vulnerability.
* Improvement: Added a self-check to the scan to detect if it has stalled.
* Improvement: If WordPress auto-updates while a scan is running, the scan will self-abort and reschedule itself to try again later.
* Improvement: IP-based filtering in Live Traffic can now use wildcards.
* Improvement: Updated the bundled GeoIP database.
* Improvement: Added an anti-crawler feature to the lockout page to avoid crawlers erroneously following the unlock link.
* Improvement: The live traffic "Group By" options now dynamically show the results in a more useful format depending on the option selected.
* Improvement: Improved the unknown core files check to include all extra files in core locations regardless of whether or not the "Scan images, binary, and other files as if they were executable" option is on.
* Improvement: Better wording for the allowlisting IP range error message.
* Fix: Addressed a performance issue on databases with tens of thousands of tables when trying to load the diagnostics page.
* Fix: All dashboard and activity report email times are now displayed in the time zone configured for the WordPress installation.

= 6.3.10 - June 1, 2017 =
* Improvement: Reduction in overall memory usage and peak memory usage for the scanner.
* Improvement: Support for exporting a list of all blocked and locked out IP addresses.
* Improvement: Updated the WAF's CA certificate bundle.
* Improvement: Updated the browscap database.
* Improvement: Suppressed the automatic HTTP referer added by WordPress for API calls to reduce overall bandwidth usage.
* Improvement: When all issues for a scan stage have been previously ignored, the results now indicate this rather than saying problems were found.
* Fix: Worked around an issue with WordPress caching to allow password audits to succeed on sites with tens of thousands of users.
* Fix: Fixed an IPv6 detection issue with one form of IPv6 address.
* Fix: An empty ignored IP list for WAF alerts no longer creates a PHP notice.
* Fix: Better detection for when to use secure cookies.
* Fix: Fixed a couple issue types that were not able to be permanently ignored.
* Fix: Adjusted the changelog link in the scan results email to work for the new wordpress.org repository.
* Fix: Fixed some broken links in the activity summary email.
* Fix: Fixed a typo in the scan summary text.
* Fix: The increased attack rate emails now correctly identify blocklist blocks.
* Fix: Fixed an issue with the dashboard where it could show the last scan failed when one has never ran.
* Fix: Brute force records are now coalesced when possible prior to sending.

= 6.3.9 - May 17, 2017 =
* Improvement: Malware signature checking has been better optimized to improve overall speed.
* Improvement: Updated the bundled GeoIP database.
* Improvement: The memory tester now tests up to the configured scan limit rather than a fixed value.
* Improvement: Added a test to the diagnostics page that verifies permissions to the WAF config location.
* Improvement: The diagnostics page now contains a callback test for the server itself.
* Improvement: Updated the styling of dashboard notifications for better separation.
* Improvement: Added additional constants to the diagnostics page.
* Change: Wordfence now enters a read-only mode with its configuration files when run via the 'cli' PHP SAPI on a misconfigured web server to avoid file ownership changing.
* Change: Changed how administrator accounts are detected to compensate for managed WordPress sites that do not have the standard permissions.
* Change: The table list on the diagnostics page is now limited in length to avoid being exceedingly large on big multisite installations.
* Fix: Improved updating of WAF config values to minimize writing to disk.
* Fix: The blocklist's blocked IP records are now correctly trimmed when expired.
* Fix: Added error suppression to the WAF attack data functions to prevent corrupt records from breaking the no-cache headers.
* Fix: Fixed some incorrect documentation links on the diagnostics page.
* Fix: Fixed a typo in a constant on the diagnostics page.

= 6.3.8 - May 2, 2017 =
* Fix: Addressed an issue that could cause scans to time out on sites with tens of thousands of potential URLs in files, comments, and posts.

= 6.3.7 - April 25, 2017 =
* Improvement: All URLs are now checked against the Wordfence Domain Blocklist in addition to Google's.
* Improvement: Better page load performance for multisite installations with thousands of tables.
* Improvement: Updated the bundled GeoIP database.
* Improvement: Integrated blocklist blocking statistics into the dashboard for Premium users.
* Fix: Added locking to the automatic update process to ensure non-standard crons don't break Wordfence.
* Fix: Fixed an activation error on multisite installations on very old WordPress versions.
* Fix: Adjusted the behavior of the blocklist toggle for Free users.

= 6.3.6 - April 5, 2017 =
* Improvement: Optimized the malware signature scan to reduce memory usage.
* Improvement: Optimized the overall scan to make fewer network calls.
* Improvement: Running an update now automatically dismisses the corresponding scan issue if present.
* Improvement: Added a time limit to the live activity status so only current messages are shown.
* Improvement: WAF configuration files are now excluded by default from the recently modified files list in the activity report.
* Improvement: Background pausing for live activity and traffic may now be disabled.
* Improvement: Added additional WAF support to allow us to more easily address false positives.
* Improvement: Blocking pages presented by Wordfence now indicate the source and contain information to help diagnose caching problems.
* Fix: All external URLs in the tour are now https.
* Fix: Corrected a typo in the unlock email template.
* Fix: Fixed the target of a label on the options page.

= 6.3.5 - March 23, 2017 =
* Improvement: Sites can now specify a list of trusted proxies when using X-Forwarded-For for IP resolution.
* Improvement: Added options to customize which dashboard notifications are shown.
* Improvement: Improvements to the scanner's malware stage to avoid timing out on larger files.
* Improvement: Provided additional no-caching indicators for caches that erroneously save pages with HTTP error status codes.
* Improvement: Updated the bundled GeoIP database.
* Improvement: Optimized the country update process in the upgrade handler so it only updates changed records.
* Improvement: Added our own prefixed version of jQuery.DataTables to avoid conflicts with other plugins.
* Improvement: Changes to readme.txt and readme.md are now ignored by the scanner unless high sensitivity is on.
* Fix: Addressed an issue with multisite installations where they would execute the upgrade handler for each subsite.
* Fix: Added additional error handling to the blocked IP list to avoid outputting notices when another plugin resets the error handler.
* Fix: Made the description in the summary email for blocks resulting from the blocklist more descriptive.
* Fix: Updated the copyright date on several pages.
* Fix: Fixed incorrect wrapping of the Group by field on the live traffic page.

= 6.3.4 - March 13, 2017 =
* Improvement: Added a path for people blocked by the IP blocklist (Premium Feature) to report false positives.

= 6.3.3 - March 9, 2017 =
* New: Malicious IPs are now preemptively blocked by a regularly-updated blocklist. [Premium Feature]
* Improvement: Better layout and display for mobile screen sizes.
* Improvement: Dashboard chart data is now updated more frequently.
* Fix: Fixed database errors on notifications page on multisite installations.
* Fix: Fixed site URL detection for multisite installations.
* Fix: Fixed tour popup positioning on multisite.
* Fix: Increased the z-index of the AJAX error watcher alert.
* Fix: Addressed an additional way to enumerate authors with the REST JSON API.

= 6.3.2 - February 23, 2017 =
* Improvement: Improved the WAF's ability to inspect POST bodies.
* Improvement: Dashboard now shows up to 100 each of failed/successful logins.
* Improvement: Updated internal GeoIP database.
* Improvement: Updated internal browscap database.
* Improvement: Better documentation on Country Blocking regarding Google AdWords
* Advanced: Added constant "WORDFENCE_DISABLE_FILE_VIEWER" to prohibit file-viewing actions from Wordfence.
* Advanced: Added constant "WORDFENCE_DISABLE_LIVE_TRAFFIC" to prohibit live traffic from capturing regular site visits.
* Fix: Fixed a few links that didn't open the correct configuration pages.
* Fix: Unknown countries in the dashboard now show "Unknown" rather than empty.

= 6.3.1 - February 7, 2017 =
* Improvement: Locked out IPs are now enforced at the WAF level to reduce server load.
* Improvement: Added a "Show more" link to the IP block list and login attempts list.
* Improvement: Added network data for the top countries blocked list.
* Improvement: Added a notification when a premium key is installed on one site but registered for another URL.
* Improvement: Switching tabs in the various pages now updates the page title as well.
* Improvement: Various styling consistency improvements.
* Change: Separated the various blocking-related pages out from the Firewall top-level menu into "Blocking".
* Fix: Improved compatibility with our GeoIP interface.
* Fix: The updates available notification is refreshed after updates are installed.
* Fix: The scan notification is refreshed when issues are resolved or ignored.

= 6.3.0 - January 26, 2017 =
* Enhancement: Added Wordfence Dashboard for quick overview of security activity.
* Improvement: Simplified the UI by revamping menu structure and styling.
* Fix: Fixed minor issue with REST API user enumeration blocking.
* Fix: Fixed undefined index notices on password audit page.

= 6.2.10 - January 12, 2017 =
* Improvement: Better reporting for failed brute force login attempts.
* Change: Reworded setting for ignored IPs in the WAF alert email.
* Change: Updated support link on scan page.
* Fix: When a key is in place on multiple sites, it's now possible to downgrade the ones not registered for it.
* Fix: Addressed an issue where the increased attack rate emails would send repeatedly if the threshold value was missing.
* Fix: Typo fix in firewall rule 11 name.

= 6.2.9 - December 27, 2016 =
* Improvement: Updated internal GeoIP database.
* Improvement: Better error handling when a site is unreachable publicly.
* Fix: Fixed a URL in alert emails that did not correctly detect when sent from a multisite installation.
* Fix: Addressed an issue where the scan did not alert about a new WordPress version.

= 6.2.8 - December 12, 2016 =
* Improvement: Added support for hiding the username information revealed by the WordPress 4.7 REST API. Thanks Vladimir Smitka.
* Improvement: Added vulnerability scanning for themes.
* Improvement: Reduced memory usage by up to 90% when scanning comments.
* Improvement: Performance improvements for the dashboard widget.
* Improvement: Added progressive loading of addresses on the blocked IP list.
* Improvement: The diagnostics page now displays a config reading/writing test.
* Change: Support for the Falcon cache has been removed.
* Fix: Better messaging when the WAF rules are manually updated.
* Fix: The proxy detection check frequency has been reduced and no longer alerts if the server is unreachable.
* Fix: Adjusted the behavior of parsing the X-Forwarded-For header for better accuracy. Thanks Jason Woods.
* Fix: Typo fix on the options page.
* Fix: Scan issue for known core file now shows the correct links.
* Fix: Links in "unlock" emails now work for IPv6 and IPv4-mapped-IPv6 addresses.
* Fix: Restricted caching of responses from the Wordfence Security Network.
* Fix: Fixed a recording issue with Wordfence Security Network statistics.

= 6.2.7 - December 1, 2016 =
* Improvement: WordPress 4.7 improvements for the Web Application Firewall.
* Improvement: Updated signatures for hash-based malware detection.
* Improvement: Automatically attempt to detect when a site is behind a proxy and has IP information in a different field.
* Improvement: Added additional contextual help links.
* Improvement: Significant performance improvement for determining the connecting IP.
* Improvement: Better messaging for two-factor recovery codes.
* Fix: Adjusted message when trying to block an IP in the allowlist.
* Fix: Error log download links now work on Windows servers.
* Fix: Avoid running out of memory when viewing very large activity logs.
* Fix: Fixed warning that could be logged when following an unlock email link.
* Fix: Tour popups on options page now scroll into view correctly.

= 6.2.6 - November 17, 2016 =
* Improvement: Improved formatting of attack data when it contains binary characters.
* Improvement: Updated internal GeoIP database.
* Improvement: Improved the ordering of rules in the malware scan so more specific rules are checked first.
* Fix: Country blocking redirects are no longer allowed to be cached.
* Fix: Fixed an issue with 2FA on multisite where the site could report URLs with different schemes depending on the state of plugin loading.

= 6.2.5 - November 9, 2016 =
* Fix: Fixed an issue that could occur on older WordPress versions when processing login attempts

= 6.2.4 - November 9, 2016 =
* Improvement: Scan times for very large sites with huge numbers of files are greatly improved.
* Improvement: Added a configurable time limit for scans to help reduce overall server load and identify configuration problems.
* Improvement: Email-based logins are now covered by "Don't let WordPress reveal valid users in login errors".
* Improvement: Extended rate limiting support to the login page.
* Fix: Fixed a case where files in the site root with issues could have them added multiple times.
* Fix: Improved IP detection in the WAF when using an IP detection method that can have multiple values.
* Fix: Added a safety check for when the database fails to return its max_allowed_packet value.
* Fix: Added safety checks for when the configuration table migration has failed.
* Fix: Added a couple rare failed login error codes to brute force detection.
* Fix: Fixed a sequencing problem when adding detection for bot/human that led to it being called on every request.
* Fix: Suppressed errors if a file is removed between the start of a scan and later scan stages.
* Fix: Addressed a problem where the scan exclusions list was not checked correctly in some situations.

= 6.2.3 - October 26, 2016 =
* Improvement: Reworked blocking for IP ranges, country blocking, and direct IP blocking to minimize server impact when under attack.
* Improvement: Live traffic better indicates the action taken by country blocking when it redirects a visitor.
* Improvement: Added support for finding server logs to the Diagnostics page to help with troubleshooting.
* Improvement: Allowlisted StatusCake IP addresses.
* Improvement: Updated GeoIP database.
* Improvement: Disabling Wordfence now sends an alert.
* Improvement: Improved detection for uploaded PHP content in the firewall.
* Fix: Eliminated memory-related errors resulting from the scan on sites with very large numbers of issues and low memory.
* Fix: Fixed admin page layout for sites using RTL languages.
* Fix: Reduced overhead of the dashboard widget.
* Fix: Improved performance of checking for Allowlisted IPs.
* Fix: Changes to the default plugin hello.php are now detected correctly in scans.
* Fix: Fixed IPv6 warning in the dashboard widget.

= 6.2.2 - October 12, 2016 =
* Fix: Replaced a slow query in the dashboard widget that could affect sites with very large numbers of users.

= 6.2.1 - October 11, 2016 =
* Improvement: Now performing scanning for PHP code in all uploaded files in real-time.
* Improvement: Improved handling of bad characters and IPv6 ranges in Advanced Blocking.
* Improvement: Live traffic and scanning activity now display a paused notice when real-time updates are suspended while in the background.
* Improvement: The file system scan alerts for files flagged by antivirus software with a '.suspected' extension.
* Improvement: New alert option to get notified only when logins are from a new location/device.
* Change: First phase for removing the Falcon cache in place, which will add a notice of its pending removal.
* Fix: Included country flags for Kosovo and Curaçao.
* Fix: Fixed the .htaccess directives used to hide files found by the scanner.
* Fix: Dashboard widget shows correct status for failed logins by deleted users.
* Fix: Removed duplicate issues for modified files in the scan results.
* Fix: Suppressed warning from reverse lookup on IPv6 addresses without valid DNS records.
* Fix: Fixed file inclusion error with themes lacking a 404 page.
* Fix: CSS fixes for activity report email.

= 6.2.0 - September 27, 2016 =
* Improvement: Massive performance boost in file system scan.
* Improvement: Added low resource usage scan option for shared hosts.
* Improvement: Aggregated login attempts when checking the Wordfence Security Network for brute force attackers to reduce total requests.
* Improvement: Now displaying scan time in a more readable format rather than total seconds.
* Improvement: Added PHP7 compatible .htaccess directives to disable code execution within uploads directory.
* Fix: Added throttling to sync the WAF attack data.
* Fix: Removed unnecessary single quote in copy containing "IP's".
* Fix: Fixed rare, edge case where cron key does not match the key in the database.
* Fix: Fixed bug with regex matching carriage returns in the .htaccess based IP block list.
* Fix: Fixed scans failing in subdirectory sites when updating malware signatures.
* Fix: Fixed infinite loop in scan caused by symlinks.
* Fix: Remove extra slash from "File restored OK" message in scan results.

= 6.1.17 - September 9, 2016 =
* Fix: Replaced calls to json_decode with our own implentation for hosts without the JSON extension enabled.

= 6.1.16 - September 8, 2016 =
* Improvement: Now performing malware scanning on all uploaded files in real-time.
* Improvement: Added Web Application Firewall activity to Wordfence summary email.
* Fix: Now using 503 response code in the page displayed when an IP is locked out.
* Fix: `wflogs` directory is now correctly removed on uninstall.
* Fix: Fixed recently introduced bug which caused the Allowlisted 404 URLs feature to no longer work.
* Fix: Added try/catch to uncaught exception thrown when pinging the API key.
* Improvement: Improved performance of the Live Traffic page in Firefox.
* Improvement: Updated GeoIP database.

= 6.1.15 - August 25, 2016 =
* Improvement: Removed file-based config caching, added support for caching via WordPress's object cache.
* Improvement: Allowlisted Uptime Robot's IP range.
* Fix: Notify users if suPHP_ConfigPath is in their WAF setup, and prompt to update Extended Protection.
* Fix: Fixed bug with allowing logins on admin accounts that are not fully activated with invalid 2FA codes when 2FA is required for all admins.
* Fix: Removed usage of `wp_get_sites()` which was deprecated in WordPress 4.6.
* Fix: Fixed PHP notice from `Undefined index: url` with custom/premium plugins.
* Improvement: Converted the banned URLs input to a textarea.

= 6.1.14 - August 11, 2016 =
* Improvement: Support downloading a file of 2FA recovery codes.
* Fix: Fixed PHP Notice: Undefined index: coreUnknown during scans.
* Improvement: Add note to options page that login security is necessary for 2FA to work.
* Fix: Fixed WAF false positives introduced with WordPress 4.6.
* Improvement: Update Geo IP database.

= 6.1.12 - July 26, 2016 =
* Fix: Fixed fatal error on sites running Wordfence 6.1.11 in subdirectory and 6.1.10 or lower in parent directory.
* Fix: Added a few common files to be excluded from unknown WordPress core file scan.

= 6.1.11 - July 25, 2016 =
* Improvement: Alert on added files to wp-admin, wp-includes.
* Improvement: 2FA is now available via any authenticator program that accepts TOTP secrets.
* Fix: Fixed bug with specific Advanced Blocking user-agent patterns causing 500 errors.
* Improvement: Plugin updates are now only a critical issue if there is a security related fix, and a warning otherwise. A link to the changelog is included.
* Fix: Added group writable permissions to Firewall's configuration files.
* Improvement: Changed allowlist entry area to textbox on options page.
* Fix: Move flags and logo served from wordfence.com over to locally hosted files.
* Fix: Fixed issues with scan in WordPress 4.6 beta.
* Fix: Fixed bug where Firewall rules could be missing on some sites running IIS.
* Improvement: Added browser-based malware signatures for .js, .html files in the malware scan.
* Fix: Added error suppression to `dns_get_record`.

= 6.1.10 - June 22, 2016 =
* Fix: Fixed fatal error in the event wflogs is not writable.

= 6.1.9 - June 21, 2016 =
* Fix: Using WP-CLI causes error Undefined index: SERVER_NAME.
* Improvement: Hooked up restore/delete file scan tools to Filesystem API.
* Fix: Reworked country blocking authentication check for access to XMLRPC.
* Improvement: Added option to require cellphone sign-in on all admin accounts.
* Improvement: Updated IPv6 GeoIP lite data.
* Fix: Removed suPHP_ConfigPath from WAF installation process.
* Fix: Prevent author names from being found through /wp-json/oembed.
* Improvement: Added better solutions for fixing wordfence-waf.php, .user.ini, or .htaccess in scan.
* Improvement: Added a method to view which files are currently used for WAF and to remove without reinstalling Wordfence.
* Improvement: Changed rule compilation to use atomic writes.
* Improvement: Removed security levels from Options page.
* Improvement: Added option to disable ajaxwatcher (for allowlisting only for Admins) on the front end.

= 6.1.8 - May 26, 2016 =
* Fix: Change wfConfig::set_ser to split large objects into multiple queries.
* Fix: Fixed bug in multisite with "You do not have sufficient permissions to access this page" error after logging in.
* Improvement: Update Geo IP database.
* Fix: Fixed deadlock when NFS is used for WAF file storage, in wfWAFAttackDataStorageFileEngine::addRow().
* Fix: Added third param to http_build_query for hosts with arg_separator.output set.
* Improvement: Show admin notice if WAF blocks an admin (mainly needed for ajax requests).
* Improvement: Clarify error message "Error reading config data, configuration file could be corrupted."
* Improvement: Added better crawler detection.
* Improvement: Add currentUserIsNot('administrator') to any generic firewall rules that are not XSS based.
* Improvement: Update URLs in Wordfence for documentation about LiteSpeed and lockouts.
* Improvement: Show message on scan results when a result is caused by enabling "Scan images and binary files as if they were executable" or...
* Fix: Suppressed warning: dns_get_record(): DNS Query failed.
* Fix: Suppressed warning gzinflate() error in scan logs.
* Fix: On WAF roadblock page: Warning: urlencode() expects parameter 1 to be string, array given ...
* Fix: Scheduled update for WAF rules doesn't decrease from 7 days, to 12 hours, when upgrading to a premium account.
* Improvement: Better message for dashboard widget when no failed logins.

= 6.1.7 - May 10, 2016 =
* Security Fix: Fixed reflected XSS vulnerability: CVSS 6.1 (Medium). Thanks Kacper Szurek.

= 6.1.6 - May 9, 2016 =
* Fix: Fixed bug with 2FA not properly handling email address login.
* Fix: Show logins/logouts when Live Traffic is disabled.
* Fix: Fixed bug with PCRE versions < 7.0 (repeated subpattern is too long).
* Fix: Now able to delete allowlisted URL/params containing ampersands and non-UTF8 characters.
* Improvement: Reduced 2FA activation code to expire after 30 days.
* Improvement: Live Traffic now only shows verified Googlebot under Google Crawler filter for new visits.
* Improvement: Adjusted permissions on Firewall log/config files to be 0640.
* Fix: Fixed false positive from Maldet in the wfConfig table during the scan.

= 6.1.5 - April 28, 2016 = 
* Fix: WordPress language files no longer flagged as changed.
* Improvement: Accept wildcards in "Immediately block IP's that access these URLs."
* Fix: Fixed bug when multiple authors have published posts, /?author=N scans show an author archive page.
* Fix: Fixed issue with IPv6 mapped IPv4 addresses not being treated as IPv4.
* Improvement: Added WordPress version and various constants to Diagnostics report.
* Fix: Fixed bug with Windows users unable to save Firewall config.
* Improvement: Include option for IIS on Windows in Firewall config process, and recommend manual php.ini change only.
* Fix: Made the 'administrator email address' admin notice dismissable.

= 6.1.4 - April 20, 2016 =
* Fix: Fixed potential bug with 'stored data not found after a fork. Got type: boolean'.
* Improvement: Added bulk actions and filters to WAF allowlist table.
* Improvement: Added a check while in learning mode to verify the response is not 404 before whitelising.
* Fix: Added index to attackLogTime. wfHits trimmed on runInstall now.
* Fix: Fixed attack data sync for hosts that cannot use wp-cron.
* Improvement: Use wftest@wordfence.com as the Diagnostics page default email address.
* Improvement: When WFWAF_ENABLED is set to false to disable the firewall, show this on the Firewall page.
* Fix: Prevent warnings when $_SERVER is empty.
* Fix: Bug fix for illegal string offset.
* Fix: Hooked up multibyte string functions to binary safe equivalents.
* Fix: Hooked up reverse IP lookup in Live Traffic.
* Fix: Add the user the web server (or PHP) is currently running as to Diagnostics page.
* Improvement: Pause Live Traffic after scrolling past the first entry.
* Improvement: Move "Permanently block all temporarily blocked IP addresses" button to top of blocked IP list.
* Fix: Added JSON fallback for PHP installations that don't have JSON enabled.

= 6.1.3 - April 14, 2016 =
* Improvement: Added dismiss button to the Wordfence WAF setup admin notice.
* Fix: Removed .htaccess and .user.ini from publicly accessible config and backup file scan.
* Fix: Removed the disallow file mods for admins created outside of WordPress.
* Fix: Fixed bug with 'Hide WordPress version' causing issues with reCAPTCHA.
* Improvement: Added instructions for NGINX users to restrict access to .user.ini during Firewall configuration.
* Fix: Fixed bug with multiple API calls to 'get_known_files'.

= 6.1.2 - April 12, 2016 =
* Fix: Fixed fatal error when using a allowlisted IPv6 range and connecting with an IPv6 address.

= 6.1.1 - April 12, 2016 =
* Enhancement: Added Web Application Firewall
* Enhancement: Added Diagnostics page
* Enhancement: Added new scans:
	* Admins created outside of WordPress
	* Publicly accessible common (database or wp-config.php) backup files
* Improvement: Updated Live Traffic with filters and to include blocked requests in the feed.

You can find a [complete changelog](https://www.wordfence.com/help/advanced/changelog/) on our documentation site.
