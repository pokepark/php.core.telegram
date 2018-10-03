<?php
// Write to log
debug_log('DDOS Check');

// Update ID file.
$id_file = DDOS_PATH . '/update_id';

// Update the update_id and reject old updates
if (file_exists($id_file) && filesize($id_file) > 0) {
    // Get update_ids from Telegram and locally stored in the file
    $update_id = $update['update_id'];
    $last_update_id = file_get_contents($id_file);
    // End script if update_id is older than stored update_id
    if ($update_id < $last_update_id) {
        debug_log('ERROR! Received old update_id!','!');
        debug_log('New update_id: ' . $update_id,'!');
        debug_log('Old update_id: ' . $last_update_id,'!');
        debug_log('Stopping execution now...','!');
        exit();
    }
} else {
    // Create file with initial update_id
    $update_id = 1;
}

// Write update_id to file
file_put_contents($id_file, $update_id);

// Init DDOS count
$ddos_count = 0;

// DDOS protection
if (isset($update['callback_query'])) {
    // Init empty data array.
    $data = [];
    // Get callback query data
    if ($update['callback_query']['data']) {
        // Split callback data and assign to data array.
        $splitData = explode(':', $update['callback_query']['data']);
        $splitAction = explode('_', $splitData[1]);
        $action = $splitAction[0];
        // Check the action
        if ($action == 'vote') {
            // Get the user_id and set the related ddos file
            $ddos_id = $update['callback_query']['from']['id'];
            $ddos_file = (DDOS_PATH . '/' . $ddos_id);
            // Check if ddos file exists and is not empty
            if (file_exists($ddos_file) && filesize($ddos_file) > 0) {
                // Get current time and last modification time of file
                $now = date("YmdHi");
                $lastchange = date("YmdHi", filemtime($ddos_file));
                // Get DDOS count or rest DDOS count if new minute
                if ($now == $lastchange) {
                    // Get DDOS count from file
                    $ddos_count = file_get_contents($ddos_file);
                    $ddos_count = $ddos_count + 1;
                // Reset DDOS count to 1
                } else {
                    $ddos_count = 1;
                }
                // Exit if DDOS of user_id count is exceeded.
                if ($ddos_count > DDOS_MAXIMUM) {
                    exit();
                // Update DDOS count in file
                } else {
                    file_put_contents($ddos_file, $ddos_count);
                }
            // Create file with initial DDOS count
            } else {
                $ddos_count = 1;
                file_put_contents($ddos_file, $ddos_count);
            }
        }
    }
}
