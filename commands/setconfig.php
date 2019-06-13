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
$allowed = explode(',', ALLOWED_TELEGRAM_CONFIG);

// Write to log.
debug_log('User submitted a telegram config change');

// 0 means we reset config option value to ""
if($count == 0) {
    // Upper input.
    $config_name = strtoupper($input);
    $config_value = '"" (' . getTranslation('no_value') . ' / ' . getTranslation('resetted') . ')';
    debug_log('Reset for the config value ' . $config_name . ' was requested by the user');

// 1 means we set the config option to the given value
} else if($count == 1) {
    // Config name and value.
    $cfg_name_value = explode(' ', $input);
    $config_name = strtoupper($cfg_name_value[0]);
    $config_value = $cfg_name_value[1];
    debug_log('Change for the config value ' . $config_name . ' was requested by the user');

// Set config_name to avoid undefined variable for if clause below.
} else {
    $config_name = 'not_supported';
}

// Make sure it's allowed to update the value via telegram.
if(in_array($config_name, $allowed)) {
    $data = '{"' . $config_name . '":' . '"' . $config_value . '"}';
    file_put_contents(CONFIG_PATH . '/' . $config_name . '.json', $data);
    $msg = getTranslation('config_updated') . ':' . CR . CR;
    $msg .= '<b>' . $config_name . '</b>' . CR;
    $msg .= getTranslation('old_value') . SP . constant($config_name) . CR;
    $msg .= getTranslation('new_value') . SP . $config_value . CR;
    debug_log('Changed the config value for ' . $config_name . ' from ' . constant($config_name) . ' to ' . $config_value);

// Tell user how to set config and what is allowed to be set by config.
} else {
    $msg = getTranslation('invalid_input') . CR . CR;
    $msg .= '<b>' . getTranslation('config') . ':</b>' . CR;
    // Any configs allowed?
    if(!empty(ALLOWED_TELEGRAM_CONFIG)) {
        $msg .= '<code>/setconfig' . SP . getTranslation('option_value') . '</code>' . CR;
        foreach($allowed as $cfg) {
            $msg .= '<code>/setconfig</code>' . SP . $cfg . SP . constant($cfg) . CR;
        }
    } else {
        $msg .= getTranslation('not_supported');
    }
    debug_log('Unsupported request for a telegram config change: ' . $input);
}

// Send message.
sendMessage($update['message']['chat']['id'], $msg);

?>
