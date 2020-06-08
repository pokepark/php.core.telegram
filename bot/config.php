<?php

/*
 * Set bot config.
 */

// Import json, make sure it's valid, die if not.
function get_config_array($file) {
  $file_contents = file_get_contents($file);

  if(! is_string($file_contents)){
    error_log('Unable to read config file, check permissions: ' . $file);
    die('Config file not readable, cannot continue: ' . $file);
  }
  
  $config_array = json_decode($file_contents, true);

  if(json_last_error() !== JSON_ERROR_NONE) {
    error_log('Invalid JSON (' . json_last_error_msg()  . '): ' . $file);
    die('Config file not valid JSON, cannot continue: ' . $file);
  }

  return $config_array;
}

function migrate_config($config){
  foreach($config as $key => $val) {
    // Make "True" and "False" real true and false
    if($val == "true") {
      $config[$key] = true;
    } else if($val == "false") {
      $config[$key] = false;
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

  // Iterate over subconfigs getting defaults and merging in custom overrides
  foreach ($default_configs as $index => $filename) {
    $dfile = CONFIG_PATH . '/' . $filename; // config defaults, e.g. defaults-config.json
    $cfile = CONFIG_PATH . '/' . str_replace('defaults-', '', $filename); // custom config overrides e.g. config.json

    // Get default config as an array so we can do an array merge later
    $config_array = get_config_array($dfile);

    // If we have a custom config, use it to override items
    if(is_file($cfile)) {
      $custom_config = get_config_array($cfile);
      // merge any custom config overrides into the subconfig
      $config_array = array_merge($config_array, $custom_config);
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
