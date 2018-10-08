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
$apiKey = $_GET['apikey'];

// Check if hashed api key is matching config.
if (hash('sha512', $apiKey) == strtolower(CONFIG_HASH)) {
    // Split the api key.
    $splitKey = explode(':', $apiKey);

    // Set constants.
    define('API_KEY', $apiKey);

// Api key is wrong!
} else {
    // Echo data.
    sendMessageEcho(MAINTAINER_ID, $_SERVER['REMOTE_ADDR'] . ' ' . isset($_SERVER['HTTP_X_FORWARDED_FOR']) . ' ' . $apiKey);
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
