<?php
// Write to log.
debug_log('GETCONFIG()');

// For debug.
// debug_log($update);
// debug_log($data);

// Check access.
bot_access_check($update, 'config-get');

// Get all allowed configs.
defined('ALLOWED_TELEGRAM_CONFIG') or define('ALLOWED_TELEGRAM_CONFIG', '');
$allowed = explode(',', ALLOWED_TELEGRAM_CONFIG);
$msg = '<b>' . getTranslation('config') . ':</b>' . CR . CR;

// Get config restrictions for boolean input
defined('ALLOW_ONLY_TRUE_FALSE') or define('ALLOW_ONLY_TRUE_FALSE', '');
$allowed_bool = explode(',', ALLOW_ONLY_TRUE_FALSE);

// Get config restrictions for numeric input
defined('ALLOW_ONLY_NUMBERS') or define('ALLOW_ONLY_NUMBERS', '');
$allowed_numbers = explode(',', ALLOW_ONLY_NUMBERS);

// Write to log.
debug_log('User requested the allowed telegram configs');
debug_log('Allowed telegram configs: ' . ALLOWED_TELEGRAM_CONFIG);
debug_log('Allow only boolean input: ' . ALLOW_ONLY_TRUE_FALSE);
debug_log('Allow only numeric input: ' . ALLOW_ONLY_NUMBERS);

// Any configs allowed?
if(!empty(ALLOWED_TELEGRAM_CONFIG)) {
    foreach($allowed as $cfg) {
        $msg .= $cfg . ' = ' . (empty(constant($cfg)) ? '<i>' . getTranslation('no_value') . '</i>' : constant($cfg));
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
