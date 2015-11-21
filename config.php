<?php

/*
 * Set our Environment
 */
define("ENV", "DEV");

/*
 * Setup our date range
 * Default: start -> today -1 week, end: today
 */
$dates = array (
    'start' => date('Y-m-d', strtotime("now -1 week")),
    'end' => date('Y-m-d', strtotime("now"))
);

/*
 * What Metrics do we want to use?
 * Ref: http://goo.gl/ziXYeX
 */
$metrics = array(
    #'ga:uniqueUsers',
    'ga:users',
    'ga:sessions',
    'ga:percentNewSessions',
    'ga:avgSessionDuration',
    'ga:sessionDuration',
    'ga:pageviews',
    'ga:uniquePageviews',
    'ga:timeOnPage',
    'ga:avgTimeOnPage',
    'ga:exitRate'
);

/*
 * Dimensions segment Metrics
 */
$dimensions = array(
    'ga:country',
    'ga:region',
    'ga:city',
    'ga:source',
    'ga:medium'
);

/*
 * Relates to $dimensions
 * can contain duplicate sort
 * values on a 1-to-1 connection
 */
$sort = array(
    'sessions',
    'users'
);

/*
 * $opts
 * Can contain: 
 *  - Dimensions (comma delimited)
 *  - Sort (Metric defined)
 *  - Max-Results (Int)
 */
$opts = array(
    'dimensions' => implode($dimensions),
    'max-results' => 10,
    'sort' => '-ga:users'
);

/*
 * Ignore below line
 */
set_time_limit(3600);

spl_autoload_extensions(".php");
spl_autoload_register();

define("METRICS", implode(',', $metrics));
define("DIMENSIONS", implode(',', $dimensions));
define("DATES", serialize($dates));
define("REQ_OPTS", serialize($opts));

/*
 * Tell which key set we should be using
 */
switch ( strtoupper(ENV) ) {
    case "DEV":
        define("KEY_FILE", "key/{KEY_FILE}");
        define("CLIENT_ID", "{CLIENT_ID}.apps.googleusercontent.com");
        define("CLIENT_EMAIL", "{CLIENT_ID}@developer.gserviceaccount.com");
        break;
}

define("APP_NAME", "Test API");

require_once './requires.php';