<?php

/*
 * Set bot config.
 */

// Make sure JSON is valid.
function check_json_array($json, $file) {
  if(is_array($json)) {
    if(json_last_error() === JSON_ERROR_NONE) {
      return True;
    } else {
      error_log('Invalid JSON (' . json_last_error()  . '): ' . $file);
      return False;
    }
  } else {
    error_log('Reading config failed(' . $file . '), faulty config array: ' . gettype($json));
  }
}
// Check file permissions.
function check_secure($path) {
  if((fileperms($path) & 0777) !== 0600) {
    error_log('Insecure file permissions: ' . $path . ' (0' . decoct(fileperms($path) & 0777) . ') - recommended file permissions: 0600');
  }
}

function migrate_config($config){
  foreach($config as $key => $val) {
    // Make "True" and "False" real true and false
    if($val == "true") {
      $val = true;
    } else if($val == "false") {
      $val = false;
    }
  }
  return $config;
}

// Build and return a full config as a json object
function build_config() {
  // Get default config files without their full path, e.g. 'defaults-config.json'
  $default_configs = str_replace(CONFIG_PATH . '/', '', glob(CONFIG_PATH . '/defaults-*.json'));

  // Collection point for individual configfile arrays, will eventually be converted to a json object
  $config = Array();

  foreach ($default_configs as $index => $filename) {
    $dfile = CONFIG_PATH . '/' . $filename; // config defaults, e.g. defaults-config.json
    $cfile = CONFIG_PATH . '/' . str_replace('defaults-', '', $filename); // custom config overrides e.g. config.json

    // Get default config as an array so we can do an array merge later
    $config_array = json_decode(file_get_contents($dfile), true);
    if(!check_json_array($config_array, $dfile)) {
      die('Default config not valid JSON, cannot continue: ' . $dfile);
    }

    // If we have a custom config, use it to override items
    if(is_file($cfile)) {
      // custom configs contain sensitive info, warn if they're not safe, but still proceed
      check_secure($cfile);
      $custom_config = json_decode(file_get_contents($cfile), true);
        if(check_json_array($custom_config, $cfile)) {
          // Merge any custom values in, overriding defaults
          $config_array = array_merge($config_array, $custom_config);
        }
    }

    // Merge the sub-configfile into the main config
    $config = array_merge($config, $config_array);
  }

  // perform config migrations
  //TODO(artanicus): The migrated config should perhaps be saved back to disk instead of this catchall compat-mode
  $config = migrate_config($config);

  // Return the whole multi-source config as an Object
  return (Object)$config;
}

// Object, access a config option with e.g. $config->VERSION
$config = build_config();
?>
