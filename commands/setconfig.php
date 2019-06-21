<?php
// Write to log.
debug_log('SETCONFIG()');

// For debug.
// debug_log($update);
// debug_log($data);

// Check access.
bot_access_check($update, 'config-set');

// Get config name and value.
$input = trim(substr($update['message']['text'], 10));

// Get delimiter count.
$count = substr_count($input, " ");

// Get allowed telegram configs.
defined('ALLOWED_TELEGRAM_CONFIG') or define('ALLOWED_TELEGRAM_CONFIG', '');
$allowed = explode(',', ALLOWED_TELEGRAM_CONFIG);

// Get config restrictions for boolean input
defined('ALLOW_ONLY_TRUE_FALSE') or define('ALLOW_ONLY_TRUE_FALSE', '');
$bool_only = explode(',', ALLOW_ONLY_TRUE_FALSE);

// Get config restrictions for numeric input
defined('ALLOW_ONLY_NUMBERS') or define('ALLOW_ONLY_NUMBERS', '');
$numbers_only = explode(',', ALLOW_ONLY_NUMBERS);

// Write to log.
debug_log('User submitted a telegram config change');
debug_log('Allowed telegram configs: ' . ALLOWED_TELEGRAM_CONFIG);
debug_log('Allow only boolean input: ' . ALLOW_ONLY_TRUE_FALSE);
debug_log('Allow only numeric input: ' . ALLOW_ONLY_NUMBERS);

// 0 means we reset config option value to ""
if($count == 0) {
    // Upper input.
    $config_name = strtoupper($input);
    $config_value = '"" (' . getTranslation('no_value') . ' / ' . getTranslation('resetted') . ')';
    debug_log('Reset for the config value ' . $config_name . ' was requested by the user');

// 1 means we set the config option to the given value
} else if($count >= 1) {
    // Config name and value.
    $cfg_name_value = explode(' ', $input, 2);
    $config_name = strtoupper($cfg_name_value[0]);
    $config_value = $cfg_name_value[1];
    debug_log('Change for the config option ' . $config_name . ' was requested by the user');

// Set config_name to avoid undefined variable for if clause below.
} else {
    $config_name = 'not_supported';
}

// Real config name or alias?
$alias = '';
$afile = CONFIG_PATH . '/alias.json';
if(is_file($afile)) {
    debug_log('Checking alias for config option ' . $config_name);
    $str = file_get_contents($afile);
    $json = json_decode($str, true);
    $alias = array_search($config_name, $json);
    if ($alias !== false) {
        debug_log('Config option ' . $config_name . ' is an alias for ' . $alias);
        $help = $config_name;
        $config_name = strtoupper($alias);
        $alias = $help;
    } else {
        debug_log('No alias found. Seems ' . $config_name . ' is the config option name');
    }
}

// Assume restrictions.
$restrict = 'yes';

// Init additional error info.
$msg_error_info = '';

// Make sure it's allowed to update the value via telegram.
if(in_array($config_name, $allowed)) {
    // Only bool?
    if(in_array($config_name, $bool_only)) {
        if(strcasecmp($config_value, "true") == 0 || strcasecmp($config_value, "false") == 0) {
            $config_value = strtolower($config_value);
            $restrict = 'no';
        } else if($config_value == "0" || $config_value == "1") {
            $restrict = 'no';
        } else {
            debug_log('Boolean value expected. Got this value: ' . $config_value);
            $msg_error_info .= getTranslation('help_bool_expected');
        }
    

    // Only numbers?
    } else if(in_array($config_name, $numbers_only)) {
        if(is_numeric($config_value)) {
            $restrict = 'no';
        } else {
            debug_log('Number expected. Got this value: ' . $config_value);
            $msg_error_info .= getTranslation('help_number_expected');
        }

    // No restriction on input type.
    } else {
        $restrict = 'no';
    }
}

// Update config.
if(in_array($config_name, $allowed) && $restrict == 'no') {
    // Prepare data, replace " with '
    $config_value = str_replace('"', "'", $config_value);
    $data = '{"' . $config_name . '":' . '"' . $config_value . '"}';
    debug_log($data, 'CONFIG:');

    // Write to file.
    if(!(is_array($data) && is_string(json_decode($data, true)) && (json_last_error() === JSON_ERROR_NONE))) {
        file_put_contents(CONFIG_PATH . '/' . $config_name . '.json', $data);
        chmod(CONFIG_PATH . '/' . $config_name . '.json', 0600);
        $msg = getTranslation('config_updated') . ':' . CR . CR;
        $msg .= '<b>' . (empty($alias) ? $config_name : $alias) . '</b>' . CR;
        $msg .= getTranslation('old_value') . SP . constant($config_name) . CR;
        $msg .= getTranslation('new_value') . SP . $config_value . CR;
        debug_log('Changed the config value for ' . $config_name . ' from ' . constant($config_name) . ' to ' . $config_value);
        debug_log('Changed the file permissions for the config file ' . CONFIG_PATH . '/' . $config_name . '.json to 0600');
    } else {
        $msg_error_info = getTranslation('internal_error');
        $msg = '<b>' . getTranslation('invalid_input') . '</b>' . (!empty($msg_error_info) ? (CR . $msg_error_info) : '') . CR . CR;
    }

// Tell user how to set config and what is allowed to be set by config.
} else {
    $msg = '<b>' . getTranslation('invalid_input') . '</b>' . (!empty($msg_error_info) ? (CR . $msg_error_info) : '') . CR . CR;
    $msg .= '<b>' . getTranslation('config') . ':</b>' . CR;
    // Any configs allowed?
    if(!empty(ALLOWED_TELEGRAM_CONFIG)) {
        $msg .= '<code>/setconfig' . SP . getTranslation('option_value') . '</code>' . CR;
        foreach($allowed as $cfg) {
            // Get alias.
            $alias = ''; 
            if(isset($json[$cfg])){
                $alias = $json[$cfg];
                $msg .= '<code>/setconfig</code>' . SP . $alias . SP . (empty(constant($cfg)) ? '<i>' . getTranslation('no_value') . '</i>' : constant($cfg));
            } else {
                $msg .= '<code>/setconfig</code>' . SP . $cfg . SP . (empty(constant($cfg)) ? '<i>' . getTranslation('no_value') . '</i>' : constant($cfg));
            }

            // Only bool?
            if(in_array($cfg, $bool_only)) {
                $msg .= SP . '<i>(' . getTranslation('help_only_bool') . ')</i>' . CR;

            // Only numbers?
            } else if(in_array($cfg, $numbers_only)) {
                $msg .= SP . '<i>(' . getTranslation('help_only_numbers') . ')</i>' . CR;

            // Any type
            } else {
                $msg .= CR;
            }
        }
    } else {
        $msg .= getTranslation('not_supported');
    }
    debug_log('Unsupported request for a telegram config change: ' . $input);
}

// Send message.
sendMessage($update['message']['chat']['id'], $msg);

?>
