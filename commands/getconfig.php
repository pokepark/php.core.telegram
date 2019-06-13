<?php
// Write to log.
debug_log('GETCONFIG()');

// For debug.
// debug_log($update);
// debug_log($data);

// Check access.
bot_access_check($update, 'config-get');

// Get all allowed configs.
$allowed = explode(',', ALLOWED_TELEGRAM_CONFIG);
$msg = '<b>' . getTranslation('config') . ':</b>' . CR . CR;

// Write to log.
debug_log('User requested the allowed telegram configs');
debug_log('Allowed telegram configs: ' . ALLOWED_TELEGRAM_CONFIG);

// Any configs allowed?
if(!empty(ALLOWED_TELEGRAM_CONFIG)) {
    foreach($allowed as $cfg) {
        $msg .= $cfg . ' = ' . constant($cfg) . CR;
    }
} else {
    $msg .= getTranslation('not_supported');
}

sendMessage($update['message']['chat']['id'], $msg);

?>
