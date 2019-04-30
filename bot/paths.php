<?php
// Core symlinked?
if(is_link($parent . '/core')) {
    $corepath = readlink($parent . '/core');
    define('ROOT_PATH', $parent);
    define('CORE_PATH', $corepath);

// Core inside bot dir
} else {
    define('ROOT_PATH', dirname(__DIR__,2));
    define('CORE_PATH', ROOT_PATH . '/core');
}

// Core Paths
define('CORE_TG_PATH', CORE_PATH . '/telegram');
define('CORE_BOT_PATH', CORE_PATH . '/bot');
define('CORE_LANG_PATH', CORE_PATH . '/lang');

// Bot Paths
define('BOT_LANG_PATH', ROOT_PATH . '/lang');
define('ACCESS_PATH', ROOT_PATH . '/access');
define('DDOS_PATH', ROOT_PATH . '/ddos');
define('CUSTOM_PATH', ROOT_PATH . '/custom');