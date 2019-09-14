<?php
// Check defines
if(defined('DB_HOST') && defined('DB_NAME') && defined ('DB_USER') && defined('DB_PASSWORD')) {
    // Establish PDO connection
    $dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    $dbh->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);

    // Establish mysqli connection.
    $db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $db->set_charset('utf8mb4');

    // Error connecting to db.
    if ($db->connect_errno) {
        // Write connection error to log.
        debug_log("Failed to connect to Database!" . $db->connect_error(), '!');
        // Echo data.
        sendMessage($update['message']['chat']['id'], "Failed to connect to Database!\nPlease contact " . MAINTAINER . " and forward this message...\n");
    }
} else {
    // Write error to log.
    debug_log("Failed to connect to Database!",'!');
    debug_log("Make sure DB_HOST, DB_NAME, DB_USER and DB_PASSWORD are defined!", '!');
}
