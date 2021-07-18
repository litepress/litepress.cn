<?php
define('WORDFENCE_EPOCH', 1312156800);
define('WORDFENCE_API_VERSION', '2.26');
define('WORDFENCE_API_URL_SEC', 'https://noc1.wordfence.com/');
define('WORDFENCE_API_URL_NONSEC', 'http://noc1.wordfence.com/');
define('WORDFENCE_API_URL_BASE_SEC', WORDFENCE_API_URL_SEC . 'v' . WORDFENCE_API_VERSION . '/');
define('WORDFENCE_BREACH_URL_BASE_SEC', WORDFENCE_API_URL_SEC . 'passwords/');
define('WORDFENCE_HACKATTEMPT_URL_SEC', 'https://noc3.wordfence.com/');
if (!defined('WORDFENCE_CENTRAL_URL_SEC')) { define('WORDFENCE_CENTRAL_URL_SEC', 'https://www.wordfence.com/central'); }
if (!defined('WORDFENCE_CENTRAL_API_URL_SEC')) { define('WORDFENCE_CENTRAL_API_URL_SEC', 'https://www.wordfence.com/api/wf'); }
if (!defined('WORDFENCE_CENTRAL_PUBLIC_KEY')) { define('WORDFENCE_CENTRAL_PUBLIC_KEY', "\xb6\x33\x81\x05\xdf\xdf\xec\xcf\xf3\xe3\x36\xc6\xf0\x99\xc6\xf7\xca\x05\x36\xca\x87\x54\x53\x43\x31\xf2\xc6\x0d\xe1\x3d\x55\x0f"); }
define('WORDFENCE_MAX_SCAN_LOCK_TIME', 86400); //Increased this from 10 mins to 1 day because very big scans run for a long time. Users can use kill.
define('WORDFENCE_DEFAULT_MAX_SCAN_TIME', 10800);
if (!defined('WORDFENCE_SCAN_ISSUES_MAX_REPORT')) { define('WORDFENCE_SCAN_ISSUES_MAX_REPORT', 1500); }
define('WORDFENCE_TRANSIENTS_TIMEOUT', 3600); //how long are items cached in seconds e.g. files downloaded for diffing
define('WORDFENCE_MAX_IPLOC_AGE', 86400); //1 day
define('WORDFENCE_CRAWLER_VERIFY_CACHE_TIME', 604800); 
define('WORDFENCE_REVERSE_LOOKUP_CACHE_TIME', 86400);
define('WORDFENCE_MAX_FILE_SIZE_TO_PROCESS', 52428800); //50 megs
define('WORDFENCE_TWO_FACTOR_GRACE_TIME_AUTHENTICATOR', 90);
define('WORDFENCE_TWO_FACTOR_GRACE_TIME_PHONE', 1800);
if (!defined('WORDFENCE_DISABLE_LIVE_TRAFFIC')) { define('WORDFENCE_DISABLE_LIVE_TRAFFIC', false); }
if (!defined('WORDFENCE_SCAN_ISSUES_PER_PAGE')) { define('WORDFENCE_SCAN_ISSUES_PER_PAGE', 100); }
if (!defined('WORDFENCE_BLOCKED_IPS_PER_PAGE')) { define('WORDFENCE_BLOCKED_IPS_PER_PAGE', 100); }
if (!defined('WORDFENCE_DISABLE_FILE_VIEWER')) { define('WORDFENCE_DISABLE_FILE_VIEWER', false); }
if (!defined('WORDFENCE_SCAN_FAILURE_THRESHOLD')) { define('WORDFENCE_SCAN_FAILURE_THRESHOLD', 300); }
if (!defined('WORDFENCE_SCAN_START_FAILURE_THRESHOLD')) { define('WORDFENCE_SCAN_START_FAILURE_THRESHOLD', 15); }
if (!defined('WORDFENCE_PREFER_WP_HOME_FOR_WPML')) { define('WORDFENCE_PREFER_WP_HOME_FOR_WPML', false); } //When determining the unfiltered `home` and `siteurl` with WPML installed, use WP_HOME and WP_SITEURL if set instead of the database values
if (!defined('WORDFENCE_SCAN_MIN_EXECUTION_TIME')) { define('WORDFENCE_SCAN_MIN_EXECUTION_TIME', 8); }
if (!defined('WORDFENCE_SCAN_MAX_INI_EXECUTION_TIME')) { define('WORDFENCE_SCAN_MAX_INI_EXECUTION_TIME', 90); }
if (!defined('WORDFENCE_ALLOW_DIRECT_MYSQLI')) { define('WORDFENCE_ALLOW_DIRECT_MYSQLI', true); }
if (!defined('WORDFENCE_NOC3_FAILED_BACKOFF_TIME')) { define('WORDFENCE_NOC3_FAILED_BACKOFF_TIME', 60); }
