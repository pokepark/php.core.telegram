<?php
// Write to log
debug_log('API Key Check');

// Set error reporting in debug mode.
if ('DEBUG' === true) {
    error_reporting(E_ALL ^ E_NOTICE);
}

// Tell telegram 'OK'
http_response_code(200);

// Get current unix timestamp as float.
$start = microtime(true);

// Get api key from get parameters.
if(isset($_GET['apikey'])) {
    $apiKey = $_GET['apikey'];
} else {
    $apiKey = 'MISSING!';
}

// Check if hashed api key is matching config.
defined('APIKEY_HASH') or define('APIKEY_HASH', '');
if (hash('sha512', $apiKey) == strtolower(APIKEY_HASH)) {
    // Split the api key.
    $splitKey = explode(':', $apiKey);

    // Set constants.
    define('API_KEY', $apiKey);

// Api key is wrong!
} else {
    if(defined('MAINTAINER_ID') && !empty(MAINTAINER_ID)) {
        // Echo data.
        sendMessageEcho(MAINTAINER_ID, 'ERROR! WRONG APIKEY!' . CR . 'Server: ' . $_SERVER['SERVER_ADDR'] . CR . 'User: ' . $_SERVER['REMOTE_ADDR'] . ' ' . isset($_SERVER['HTTP_X_FORWARDED_FOR']) . CR . 'APIKEY: ' . $apiKey);
    } else {
        // Write to standard error log.
        error_log('ERROR! The constant MAINTAINER_ID is not defined!');
    }
    // And exit script.
    exit();
}

// Get content if not already
if (!(isset($update))) {
    // Get content from POST data.
    $content = file_get_contents('php://input');

    // Decode the json string.
    $update = json_decode($content, true);
} else {
    debug_log('Already got content from POST data', '!');
}

// Update var is false.
$log_prefix = '<';
if (!$update) {
    $log_prefix = '!';
}

// Write to log.
debug_log($update, $log_prefix);
