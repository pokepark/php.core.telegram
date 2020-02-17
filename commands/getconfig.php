<?php
// Write to log.
debug_log('GETCONFIG()');

// For debug.
// debug_log($update);
// debug_log($data);

// Check access.
bot_access_check($update, 'config-get');

// Get all allowed configs.
$allowed = explode(',', $config->ALLOWED_TELEGRAM_CONFIG);
$msg = '<b>' . getTranslation('config') . ':</b>' . CR . CR;

// Get config restrictions for boolean input
$allowed_bool = explode(',', $config->ALLOW_ONLY_TRUE_FALSE);

// Get config restrictions for numeric input
$allowed_numbers = explode(',', $config->ALLOW_ONLY_NUMBERS);

// Get config aliases.
$afile = CONFIG_PATH . '/alias.json';
if(is_file($afile)) {
    $str = file_get_contents($afile);
    $json = json_decode($str, true);
}

// Write to log.
debug_log('User requested the allowed telegram configs');
debug_log('Allowed telegram configs: ' . $config->ALLOWED_TELEGRAM_CONFIG);
debug_log('Allow only boolean input: ' . $config->ALLOW_ONLY_TRUE_FALSE);
debug_log('Allow only numeric input: ' . $config->ALLOW_ONLY_NUMBERS);

// Any configs allowed?
if(!empty($config->ALLOWED_TELEGRAM_CONFIG)) {
    foreach($allowed as $cfg) {
        // Get alias.
        $alias = ''; 
        if(isset($json[$cfg])){
            $alias = $json[$cfg];
            $msg .= $alias . ' = ' . (empty(constant($cfg)) ? '<i>' . getTranslation('no_value') . '</i>' : constant($cfg));
        } else {
            $msg .= $cfg . ' = ' . (empty(constant($cfg)) ? '<i>' . getTranslation('no_value') . '</i>' : constant($cfg));
        }
        // Only bool?
        if(in_array($cfg, $allowed_bool)) {
            $msg .= SP . '<i>(' . getTranslation('help_only_bool') . ')</i>' . CR;

        // Only numbers?
        } else if(in_array($cfg, $allowed_numbers)) {
            $msg .= SP . '<i>(' . getTranslation('help_only_numbers') . ')</i>' . CR;

        // Any type
        } else {
            $msg .= CR;
        }
    }
} else {
    $msg .= getTranslation('not_supported');
}

sendMessage($update['message']['chat']['id'], $msg);

?>
