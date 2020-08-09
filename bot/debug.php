<?php
/**
 * Get last insert id from db.
 * @return mixed
 */
function my_insert_id()
{
    global $db;

    return $db->insert_id;
}

/**
 * Naive DB query without proper param handling.
 * You should prefer doing your own prepare, bindParam & execute!
 * @param $query
 * @return PDOStatement
 */
function my_query($query, $cleanup_query = false)
{
    global $dbh;
    global $config;

    if($config->DEBUG_SQL) {
        if ($cleanup_query == true) {
            debug_log($query, '?', true);
        } else {
            debug_log($query, '?');
        }
    }
    $stmt = $dbh->prepare($query);
    if ($stmt && $stmt->execute()) {
        if ($cleanup_query == true) {
            debug_log('Query success', '$', true);
        } else {
            debug_log('Query success', '$');
        }
    } else {
        if ($cleanup_query == true) {
            debug_log($dbh->errorInfo(), '!', true);
        } else {
            debug_log($dbh->errorInfo(), '!');
        }
    }

    return $stmt;
}

/**
 * Write debug log.
 * @param $val
 * @param string $type
 */
function debug_log($val, $type = '*', $cleanup_log = false)
{
    global $config;
    // Write to log only if debug is enabled.
    if ($config->DEBUG === true){
        if(!$config->DEBUG_LOGFILE || !$config->CLEANUP_LOGFILE) {
          error_log('DEBUG set but DEBUG_LOGFILE or CLEANUP_LOGFILE is not!');
        }

        $date = @date('Y-m-d H:i:s');
        $usec = microtime(true);
        $date = $date . '.' . str_pad(substr($usec, 11, 4), 4, '0', STR_PAD_RIGHT);

        $bt = debug_backtrace();
        $bl = '';

        while ($btl = array_shift($bt)) {
            if ($btl['function'] == __FUNCTION__) continue;
            $bl = '[' . basename($btl['file']) . ':' . $btl['line'] . '] ';
            break;
        }

        if (gettype($val) != 'string') $val = var_export($val, 1);
        $rows = explode("\n", $val);
        foreach ($rows as $v) {
            if ($cleanup_log == true) {
                error_log('[' . $date . '][' . getmypid() . '] ' . $bl . $type . ' ' . $v . "\n", 3, $config->CLEANUP_LOGFILE);
            } else {
                error_log('[' . $date . '][' . getmypid() . '] ' . $bl . $type . ' ' . $v . "\n", 3, $config->DEBUG_LOGFILE);
            }
        }
    }
}

/**
 * Write cleanup log.
 * @param $val
 * @param string $type
 * @param bool $cleanup_log
 */
function cleanup_log($val, $type = '*')
{
    debug_log($val, $type, $cleanup_log = true);
}
