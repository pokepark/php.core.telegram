<?php
// Check if version is defined in config.
defined('VERSION') or define('VERSION', '1.0.0.0');
$current = VERSION;
$nodot_current = str_replace('.', '', $current);

// Get version from VERSION file.
$lfile = ROOT_PATH . '/VERSION';
if(is_file($lfile) && filesize($lfile)) {
    $latest = file($lfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $latest = $latest[0];
} else {
    $latest = '1.0.0.0';
}
$nodot_latest = str_replace('.', '', $latest);

// Compare versions.
if($nodot_current == $nodot_latest) {
    debug_log($current, 'Your bot version:');
} else {
    // Current version not defined in config!
    if($nodot_current == '1000') { 
        debug_log('Failed to determine your bot version!', '!');

        // Tell user bot maintainance is required!
        if(defined('MAINTAINER_ID') && !empty(MAINTAINER_ID)) {
            // Echo data.
            $msg = 'ERROR! BOT MAINTAINANCE REQUIRED!' . CR . 'FAILED TO GET YOUR BOT VERSION!' . CR;
            $msg .= 'Server: ' . $_SERVER['SERVER_ADDR'] . CR;
            $msg .= 'User: ' . $_SERVER['REMOTE_ADDR'] . ' ' . isset($_SERVER['HTTP_X_FORWARDED_FOR']) . CR;
            $msg .= 'Your version: ' . $current . CR . 'Latest version: ' . $latest;
            sendMessageEcho(MAINTAINER_ID, $msg);
        } else {
            // Write to standard error log.
            error_log('ERROR! The constant MAINTAINER_ID is not defined!');
            error_log('ERROR! BOT MAINTAINANCE REQUIRED! FAILED TO GET YOUR BOT VERSION! --- Your version: ' . $current . ' --- Latest version: ' . $latest);
        }
        // Exit script.
        exit();

    // Latest version unavailable!
    } else if($nodot_latest == '1000') {
        debug_log('Failed to determine the latest bot version!', '!');

        // Tell user bot maintainance is required!
        if(defined('MAINTAINER_ID') && !empty(MAINTAINER_ID)) {
            // Echo data.
            $msg = 'ERROR! BOT MAINTAINANCE REQUIRED!' . CR . 'FAILED TO GET THE LATEST BOT VERSION!' . CR;
            $msg .= 'Server: ' . $_SERVER['SERVER_ADDR'] . CR;
            $msg .= 'User: ' . $_SERVER['REMOTE_ADDR'] . ' ' . isset($_SERVER['HTTP_X_FORWARDED_FOR']) . CR;
            $msg .= 'Your version: ' . $current . CR . 'Latest version: ' . $latest;
            sendMessageEcho(MAINTAINER_ID, $msg);
        } else {
            // Write to standard error log.
            error_log('ERROR! The constant MAINTAINER_ID is not defined!');
            error_log('ERROR! BOT MAINTAINANCE REQUIRED! FAILED TO GET THE LATEST BOT VERSION! --- Your version: ' . $current . ' --- Latest version: ' . $latest);
        } 
        // Exit script.
        exit();

    // Check for upgrade files.
    } else {
        debug_log('Your bot version: ' . $current);
        debug_log('Latest bot version: ' . $latest);
        debug_log('Performing bot upgrade check now...');
        $upgrade = bot_upgrade_check($current, $latest);

        // Upgrade needed?
        if(!$upgrade) {
            debug_log('Your bot version differs from the latest bot version in ' . ROOT_PATH . '/VERSION', '!');
            debug_log('Please update your bot version in your configuration!', '!');
            debug_log('Continuing with this warning as no SQL upgrade is required!', '!');
        } else {
            // Tell user an upgrade is required!
            if(defined('MAINTAINER_ID') && !empty(MAINTAINER_ID)) {
                // Echo data.
                sendMessageEcho(MAINTAINER_ID, 'ERROR! BOT UPGRADE REQUIRED!' . CR . 'Server: ' . $_SERVER['SERVER_ADDR'] . CR . 'User: ' . $_SERVER['REMOTE_ADDR'] . ' ' . isset($_SERVER['HTTP_X_FORWARDED_FOR']) . CR . 'Your version: ' . $current . CR . 'Latest version: ' . $latest);
            } else {
                // Write to standard error log.
                error_log('ERROR! The constant MAINTAINER_ID is not defined!');
                error_log('ERROR! BOT UPGRADE REQUIRED! --- Your version: ' . $current . ' --- Latest version: ' . $latest);
            }
            // Exit script.
            exit();
        }
    }
}
