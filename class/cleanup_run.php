<?php
// Cleanup request received.
if (isset($update['cleanup']) && CLEANUP == true) {
    cleanup_log('Cleanup process request received...');
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
        // Run cleanup based on type
        $cleanup_type = $update['cleanup']['type'];
        cleanup_log('Calling ' . $cleanup_type . ' cleanup process now!');
        // Raids cleanup
        if ($cleanup_type == 'raid') {
            run_raids_cleanup($telegram, $database);
        } else if ($cleanup_type == 'quest') {
            run_quests_cleanup($telegram, $database);
        } else {
            cleanup_log('Error! Wrong cleanup type supplied!', '!');
        }
    } else {
        cleanup_log('Error! Wrong cleanup secret supplied!', '!');
    }
    // Exit after cleanup
    exit();
} 
