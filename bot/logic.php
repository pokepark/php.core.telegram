<?php

/**
 * Bot access check.
 * @param $update
 * @param $permission
 * @param $return_result
 * @return bool (if requested)
 */
function bot_access_check($update, $permission = 'access-bot', $return_result = false)
{
    // Start with deny access
    $allow_access = false;

    // Get telegram ID to check access from $update - either message, callback_query or inline_query
    $update_type = '';
    $update_type = !empty($update['message']['from']['id']) ? 'message' : $update_type; 
    $update_type = (empty($update_type) && !empty($update['callback_query']['from']['id'])) ? 'callback_query' : $update_type; 
    $update_type = (empty($update_type) && !empty($update['inline_query']['from']['id'])) ? 'inline_query' : $update_type; 
    $update_id = $update[$update_type]['from']['id'];

    // Write to log.
    debug_log('Telegram message type: ' . $update_type);
    debug_log('Checking access for ID: ' . $update_id);
    debug_log('Checking permission: ' . $permission);

    // Get all chat files for groups/channels like -100111222333
    // Creators
    $creator_chats = array();
    $creator_chats = str_replace(ACCESS_PATH . '/creator','',glob(ACCESS_PATH . '/creator-*'));

    // Admins
    $admin_chats = array();
    $admin_chats = str_replace(ACCESS_PATH . '/admins','',glob(ACCESS_PATH . '/admins-*'));

    // Members
    $member_chats = array();
    $member_chats = str_replace(ACCESS_PATH . '/members','',glob(ACCESS_PATH . '/members-*'));

    // Access chats
    $access_chats = array();
    $access_chats = str_replace(ACCESS_PATH . '/access','',glob(ACCESS_PATH . '/access-*'));
    $access_chats = array_merge($access_chats, $creator_chats, $admin_chats, $member_chats);

    // Make sure BOT_ADMINS are defined.
    defined('BOT_ADMINS') or define('BOT_ADMINS', '');

    // Add Admins if a group/channel
    if(!empty(BOT_ADMINS)) {
        $bot_admins = explode(',',BOT_ADMINS);
        foreach($bot_admins as $admin) {
            // Ignore individuals, add only groups/channels
            if($admin[0] == '-') {
                array_unshift($access_chats,$admin);
            }
        }
    }
    // Add update_id
    if (is_file(ACCESS_PATH . '/access' . $update_id)) {
        $access_chats[] = $update_id;
    }
    // Delete duplicates
    $access_chats = array_unique($access_chats);

    // Check each chat
    debug_log('Checking these chats:');
    debug_log($access_chats);
    
    // Check access and permission
    foreach($access_chats as $chat) {
        // Get chat object 
        debug_log("Getting chat object for '" . $chat . "'");
        $chat_obj = get_chat($chat);

        // Check chat object for proper response.
        if($chat_obj['ok'] == true) {
            debug_log('Proper chat object received, continuing with access check.');
        } else {
            debug_log('Chat ' . $chat . ' does not exist! Continuing with next chat...');
            continue;
        }
        
        // Group/channel?
        if($chat[0] == '-') {
           // Get chat member object and check status
           debug_log("Getting user from chat '" . $chat . "'");
           $chat_obj = get_chatmember($chat, $update_id);

           // Make sure we get a proper response
           if($chat_obj['ok'] == true) {
                debug_log('Proper chat object received, continuing with access check.');

                // Admin?
                $admins = explode(',',BOT_ADMINS);
                if(in_array($chat,$admins) || in_array($update_id,$admins)) {
                    debug_log('Positive result on access check for Bot Admins');
                    debug_log('Bot Admins: ' . BOT_ADMINS);
                    debug_log('chat: ' . $chat);
                    debug_log('update_id: ' . $update_id);
                    $allow_access = true;
                    break;
                } else {
                    // Get access file based on user status/role.
                    debug_log('Role of user ' . $chat_obj['result']['user']['id'] . ' : ' . $chat_obj['result']['status']);

                    // Creator
                    if($chat_obj['result']['status'] == 'creator' && is_file(ROOT_PATH . '/access/creator' . $chat)) {
                        $access_file = file_get_contents(ROOT_PATH . '/access/creator' . $chat);

                    // Admin 
                    } else if($chat_obj['result']['status'] == 'administrator' && is_file(ROOT_PATH . '/access/admins' . $chat)) {
                        $access_file = file_get_contents(ROOT_PATH . '/access/admins' . $chat);

                    // Member
                    } else if($chat_obj['result']['status'] == 'member' && is_file(ROOT_PATH . '/access/members' . $chat)) {
                        $access_file = file_get_contents(ROOT_PATH . '/access/members' . $chat);

                    // Any other user status/role.
                    } else if(is_file(ROOT_PATH . '/access/access' . $chat)) {
                        $access_file = file_get_contents(ROOT_PATH . '/access/access' . $chat);
                    }

                    //debug_log('Access file:');
                    //debug_log($access_file);
                   
                    // Check user status/role and permission to access the function
                    if($chat_obj['result']['user']['id'] == $update_id && (strpos($access_file,$permission) !== FALSE)) {
                        debug_log($chat_obj['result']['status'] . $chat, 'Positive result on access check in file:');
                        $allow_access = true;
                        break;
                    } else {
                        // Deny access
                        debug_log($chat_obj['result']['status'] . $chat, 'Negative result on access check in file:');
                        debug_log('Continuing with next chat...');
                        continue;
                    }
                }
            } else {
                // Invalid chat
                debug_log('Chat ' . $chat . ' does not exist! Continuing with next chat...');
                continue;
            }
        // Private chat
        } else {
            // Get chat object 
            debug_log("Getting chat object for '" . $chat . "'");
            $chat_obj = get_chat($chat);

            // Check chat object for proper response.
            if($chat_obj['ok'] == true) {
                debug_log('Proper chat object received, continuing with access check.');

                // Admin?
                $admins = explode(',',BOT_ADMINS);
                if(in_array($chat,$admins) || in_array($update_id,$admins)) {
                    debug_log('Positive result on access check for Bot Admins');
                    $allow_access = true;
                    break;
                } else {
                    // Get access file
                    $access_file = file_get_contents(ROOT_PATH . '/access/access' . $chat);

                    // ID matching $chat, private chat type and permission to access the function
                    if($chat_obj['result']['id'] == $update_id && $chat_obj['result']['type'] == 'private' && (strpos($access_file,$permission) !== FALSE)) {
                        debug_log('Positive result on access check in file: access' . $chat);
                        $allow_access = true;
                        break;
                    } else if($chat_obj['result']['type'] == 'private') {
                        // Result was ok, but access not granted. Continue with next chat if type is private.
                        debug_log('Negative result on access check in file: access' . $chat);
                        debug_log('Continuing with next chat...');
                        continue;
                    }
                }
            } else {
                // Invalid chat
                debug_log('Chat ' . $chat . ' does not exist! Continuing with next chat...');
                continue;
            }
        }
    }
    
    // Result of access check?
    // Prepare logging of id, username and/or first_name
    $msg = '';
    $msg .= !empty($update[$update_type]['from']['id']) ? 'Id: ' . $update[$update_type]['from']['id']  . CR : '';
    $msg .= !empty($update[$update_type]['from']['username']) ? 'Username: ' . $update[$update_type]['from']['username'] . CR : '';
    $msg .= !empty($update[$update_type]['from']['first_name']) ? 'First Name: ' . $update[$update_type]['from']['first_name'] . CR : '';

    // Public access?
    if(empty(BOT_ADMINS)) {
        debug_log('Bot access is not restricted! Allowing access for user: ' . CR . $msg);
        $allow_access = true;
    }

    // Allow or deny access to the bot and log result
    if ($allow_access && !$return_result) {
        debug_log('Allowing access to the bot for user:' . CR . $msg);
    } else if ($allow_access && $return_result) {
        debug_log('Allowing access to the bot for user:' . CR . $msg);
        return $allow_access;
    } else if (!$allow_access && $return_result) {
        debug_log('Denying access to the bot for user:' . CR . $msg);
        return $allow_access;
    } else {
        debug_log('Denying access to the bot for user:' . CR . $msg);
        $response_msg = '<b>' . getTranslation('bot_access_denied') . '</b>';
        // Edit message or send new message based on value of $update_type
        if ($update_type == 'callback_query') {
            // Empty keys.
            $keys = [];

            // Telegram JSON array.
            $tg_json = array();

            // Edit message.
            $tg_json[] = edit_message($update, $response_msg, $keys, false, true);

            // Answer the callback.
            $tg_json[] = answerCallbackQuery($update[$update_type]['id'], getTranslation('bot_access_denied'), true);

            // Telegram multicurl request.
            curl_json_multi_request($tg_json);
        } else {
            sendMessage($update[$update_type]['from']['id'], $response_msg);
        }
        exit;
    }
}


/*
 * Update user
 * @param $update
*/
function update_user($update) 
{
    global $ddos_count;

    // Check DDOS count
    if ($ddos_count < 2) {
        // Update the user.
        $userUpdate = update_userdb($update);

        // Write to log.
        debug_log('Update user: ' . $userUpdate);
    }
}


/**
 * Update user.
 * @param $update
 * @return bool|mysqli_result
 */
function update_userdb($update)
{
    global $db;

    $name = '';
    $nick = '';
    $sep = '';

    if (isset($update['message']['from'])) {
        $msg = $update['message']['from'];
    }

    if (isset($update['callback_query']['from'])) {
        $msg = $update['callback_query']['from'];
    }

    if (isset($update['inline_query']['from'])) {
        $msg = $update['inline_query']['from'];
    }

    if (!empty($msg['id'])) {
        $id = $msg['id'];

    } else {
        debug_log('No id', '!');
        debug_log($update, '!');
        return false;
    }

    if ($msg['first_name']) {
        $name = $msg['first_name'];
        $sep = ' ';
    }

    if (isset($msg['last_name'])) {
        $name .= $sep . $msg['last_name'];
    }

    if (isset($msg['username'])) {
        $nick = $msg['username'];
    }

    // Create or update the user.
    $request = my_query(
        "
        INSERT INTO users
        SET         user_id = {$id},
                    nick    = '{$db->real_escape_string($nick)}',
                    name    = '{$db->real_escape_string($name)}'
        ON DUPLICATE KEY
        UPDATE      nick    = '{$db->real_escape_string($nick)}',
                    name    = '{$db->real_escape_string($name)}'
        "
    );

    return $request;
}


/**
 * Get user language.
 * @param $language_code
 * @return string
 */
function get_user_language($language_code)
{
    $languages = $GLOBALS['languages'];

    // Get languages from normal translation.
    if(array_key_exists($language_code, $languages)) {
        $userlanguage = $languages[$language_code];
    } else {
        $userlanguage = DEFAULT_LANGUAGE;
    }

    debug_log('User language: ' . $userlanguage);

    return $userlanguage;
}


/**
 * Get date from datetime value.
 * @param $datetime_value
 * @param $tz
 * @return string
 */
function dt2date($datetime_value, $tz = TIMEZONE)
{
    // Create a object with UTC timezone
    $datetime = new DateTime($datetime_value, new DateTimeZone('UTC'));

    // Change the timezone of the object without changing it's time
    $datetime->setTimezone(new DateTimeZone($tz));

    return $datetime->format('Y-m-d');
}

/**
 * Get time from datetime value.
 * @param $datetime_value
 * @param $format
 * @param $tz
 * @return string
 */
function dt2time($datetime_value, $format = 'H:i', $tz = TIMEZONE)
{
    // Create a object with UTC timezone
    $datetime = new DateTime($datetime_value, new DateTimeZone('UTC'));

    // Change the timezone of the object without changing it's time
    $datetime->setTimezone(new DateTimeZone($tz));

    return $datetime->format($format);
}

/**
 * Format utc date from datetime value.
 * @param $datetime_value
 * @return string
 */
function utcdate($datetime_value)
{
    // Create a object with UTC timezone
    $datetime = new DateTime($datetime_value, new DateTimeZone('UTC'));

    return $datetime->format('Y-m-d');
}

/**
 * Format utc time from datetime value.
 * @param $datetime_value
 * @param $format
 * @return string
 */
function utctime($datetime_value, $format = 'H:i')
{
    // Create a object with UTC timezone
    $datetime = new DateTime($datetime_value, new DateTimeZone('UTC'));

    return $datetime->format($format);
}

/**
 * Get current utc datetime.
 * @param $format
 * @return string
 */
function utcnow($format = 'Y-m-d H:i:s')
{
    // Create a object with UTC timezone
    $datetime = new DateTime('now', new DateTimeZone('UTC'));

    return $datetime->format($format);
}


/**
 * Inline key array.
 * @param $buttons
 * @param $columns
 * @return array
 */
function inline_key_array($buttons, $columns)
{
    $result = array();
    $col = 0;
    $row = 0;

    foreach ($buttons as $v) {
        $result[$row][$col] = $v;
        $col++;

        if ($col >= $columns) {
            $row++;
            $col = 0;
        }
    }
    return $result;
}


/**
 * Universal key.
 * @param $keys
 * @param $id
 * @param $action
 * @param $arg
 * @param $text
 * @return array
 */
function universal_key($keys, $id, $action, $arg, $text = '0')
{
    $keys[] = [
            array(
                'text'          => $text,
                'callback_data' => $id . ':' . $action . ':' . $arg
            )
        ];

    // Write to log.
    //debug_log($keys);

    return $keys;
}


/**
 * Universal key.
 * @param $keys
 * @param $id
 * @param $action
 * @param $arg
 * @param $text
 * @return array
 */
function universal_inner_key($keys, $id, $action, $arg, $text = '0')
{
    $keys = array(
                'text'          => $text,
                'callback_data' => $id . ':' . $action . ':' . $arg
            );

    // Write to log.
    //debug_log($keys);

    return $keys;
}

/**
 * Share keys.
 * @param $id
 * @param $action
 * @param $update
 * @return array
 */
function share_keys($id, $action, $update)
{
    // Check access.
    $share_access = bot_access_check($update, 'share-any-chat', true);

    // Add share button if not restricted to allow sharing to any chat.
    if ($share_access == true) {
        debug_log('Adding general share key to inline keys');
        // Set the keys.
        $keys[] = [
            [
                'text'                => getTranslation('share'),
                'switch_inline_query' => basename(ROOT_PATH) . ':' . strval($id)
            ]
        ];
    }

    // Add buttons for predefined sharing chats.
    if(defined('SHARE_CHATS') && !empty(SHARE_CHATS)) {
        // Add keys for each chat.
        $chats = explode(',', SHARE_CHATS);
        foreach($chats as $chat) {
            // Get chat object 
            debug_log("Getting chat object for '" . $chat . "'");
            $chat_obj = get_chat($chat);

            // Check chat object for proper response.
            if ($chat_obj['ok'] == true) {
                debug_log('Proper chat object received, continuing to add key for this chat: ' . $chat_obj['result']['title']);
                $keys[] = [
                    [
                        'text'          => getTranslation('share_with') . ' ' . $chat_obj['result']['title'],
                        'callback_data' => $id . ':' . $action . ':' . $chat
                    ]
                ];
            }
        }
    }

    return $keys;
}
