<?php
/**
 * Bot upgrade check
 * @param $current
 * @param $latest
 * @return bool
*/
function bot_upgrade_check($current, $latest)
{
    // Get upgrade sql files.
    $upgrade_files = array();
    $upgrade_files = str_replace(UPGRADE_PATH . '/','', glob(UPGRADE_PATH . '/*.sql'));

    // Remove dots from current and latest version for easier comparison.
    $nodot_current = str_replace('.', '', $current);
    $nodot_latest = str_replace('.', '', $latest);

    // Same version?
    if($nodot_current == $nodot_latest) {
        // No upgrade needed.
        debug_log('Bot version check succeeded!');
        return false;
    } else {
        // Check if upgrade files exists.
        if(is_array($upgrade_files) && count($upgrade_files) > 0) {
            // Upgrade required?
            $require_upgrade = false;
            // Check each sql filename.
            foreach ($upgrade_files as $ufile)
            {
                // Skip every older sql file from array.
                $nodot_ufile = str_replace('.', '', str_replace('.sql', '', $ufile));
                if($nodot_ufile <= $nodot_current) {
                    continue;
                } else {
                    // Set upgrade required to true and log every sql file required for upgrade.
                    $require_upgrade = true;
                    debug_log('REQUIRED SQL UPGRADE FILE FOUND:' . UPGRADE_PATH . '/' . $ufile, '!');
                }
            }
            // Upgrade required.
            return $require_upgrade;
        } else {
            // No upgrade files found! Return false as versions did not match but no upgrades are required!
            debug_log('NO SQL UPGRADE FILES FOUND', '!');
            return false;
        }
    }
}


?>
