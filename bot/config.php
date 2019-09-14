<?php

/*
 * Set bot config.
 */

// Get all config files.
$jsons = str_replace(CONFIG_PATH . '/', '', glob(CONFIG_PATH . '/*.json'));

// Put telegram.json on top of the config files array.
array_unshift($jsons, 'telegram.json');
$jsons = array_unique($jsons);

// Remove config.json and add it at the end of the config files array again.
$jsons = array_diff($jsons, ["config.json"]);
$jsons[] = "config.json";

// Remove alias.json
$jsons = array_diff($jsons, ["alias.json"]);

// Write to log.
foreach ($jsons as $index => $filename) {
    // Add path to file.
    $cfile = CONFIG_PATH . '/' . $filename;

    // Get config from file.
    if(is_file($cfile)) {
        $str = file_get_contents($cfile);
        $config = json_decode($str, true);
        // Make sure JSON is valid.
        if(!(is_string($str) && is_array(json_decode($str, true)) && (json_last_error() === JSON_ERROR_NONE))) {
            error_log('Invalid JSON: ' . $cfile);
            continue;
        }

        // Check file permissions.
        if((fileperms($cfile) & 0777) !== 0600) {
            error_log('Insecure file permissions: ' . $cfile . ' (0' . decoct(fileperms($cfile) & 0777) . ') - recommended file permissions: 0600');
        }

        // Define constants.
        foreach($config as $key => $val) {
            // Skip comments starting and ending with 2 underscores, e.g. __SQL-CONFIG__
            if(substr($key, 0, 2) == '__' && substr($key, -2)) continue;

            // Make "True" and "False" real true and false
            if($val == "true") {
                $val = true;
            } else if($val == "false") {
                $val = false;
            }

            // Define constants.
            defined($key) or define($key, $val);
        }
    }
}
