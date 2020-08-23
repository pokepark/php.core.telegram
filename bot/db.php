<?php
// Check defines
if($config->DB_HOST && $config->DB_NAME && $config->DB_USER && $config->DB_PASSWORD) {
    // Establish PDO connection
    $dbh = new PDO("mysql:host=" . $config->DB_HOST . ";dbname=" . $config->DB_NAME . ";charset=utf8mb4", $config->DB_USER, $config->DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    $dbh->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
} else {
    // Write error to log.
    debug_log("Failed to connect to Database!",'!');
    debug_log("Make sure DB_HOST, DB_NAME, DB_USER and DB_PASSWORD are defined!", '!');
}
