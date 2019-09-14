<?php
// Cleanup request received.
if (isset($update['cleanup']) && CLEANUP == true) {
    cleanup_log('Cleanup request received...');
    // Check access to cleanup of bot
    if ($update['cleanup']['secret'] == CLEANUP_SECRET) {
        // Get telegram cleanup value if specified.
        if (isset($update['cleanup']['telegram'])) {
            $telegram = $update['cleanup']['telegram'];
        } else {
            $telegram = 2;
        }
        // Get database cleanup value if specified.
        if (isset($update['cleanup']['database'])) {
            $database = $update['cleanup']['database'];
        } else {
            $database = 2;
        }
        if (function_exists('run_cleanup')) {
            // Write cleanup info to database.
            cleanup_log('Running cleanup now!');
            // Run cleanup
            run_cleanup($telegram, $database);
        } else {
            cleanup_log('No function found to run cleanup!', 'ERROR:');
            cleanup_log('Add a function named "run_cleanup" to run cleanup for telegram messages and database entries!', 'ERROR:');
            cleanup_log('Arguments of that function need to be values to run/not run the cleanup for telegram $telegram and the database $database.', 'ERROR:');
            cleanup_log('For example: function run_cleanup($telegram, $database)', 'ERROR:');
        }
    } else {
        cleanup_log('Error! Wrong cleanup secret supplied!', '!');
    }
    // Exit after cleanup
    exit();
} else if (isset($update['cleanup'])) {
    cleanup_log('Cleanup request received...');
    cleanup_log('Cleanup is disabled!');
    cleanup_log('Please enable it in the config!');
    cleanup_log('Exiting now...');
    exit();
}
