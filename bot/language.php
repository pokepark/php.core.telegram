<?php
/**
 * Define private and public language
*/
defined('LANGUAGE_PRIVATE') or define('LANGUAGE_PRIVATE', '');
defined('LANGUAGE_PUBLIC') or define('LANGUAGE_PUBLIC', 'EN');

/**
 * Call the translation function with override parameters.
 * @param $text
 * @return translation
 */
function getPublicTranslation($text)
{
    $translation = getTranslation($text, true, LANGUAGE_PUBLIC);

    return $translation;
}

/**
 * Gets a table translation out of the json file.
 * @param $text
 * @param $override
 * @param $override_language
 * @return translation
 */
function getTranslation($text, $override = false, $override_language = USERLANGUAGE)
{
    debug_log($text,'T:');
    $translation = '';

    // Set language
    $language = USERLANGUAGE;

    // Override language?
    if($override == true && $override_language != '') {
        $language = $override_language;
    }

    // Pokemon name?
    if(strpos($text, 'pokemon_id_') === 0) {
        // Translation filename
        $tfile = CORE_LANG_PATH . '/pokemon_' . strtolower($language) . '.json';

        // Make sure file exists, otherwise use English language as fallback.
        if(!is_file($tfile)) {
            $language = DEFAULT_LANGUAGE;
            $tfile = CORE_LANG_PATH . '/pokemon_' . strtolower($language) . '.json';
        }

        // Get ID from string - e.g. 150 from pokemon_id_150
        $pokemon_id = substr($text, strrpos($text, '_') + 1);
        $str = file_get_contents($tfile);

        // Index starts at 0, so pokemon_id minus 1 for the correct name!
        $json = json_decode($str, true);
        $translation = $json[$pokemon_id - 1];

    // Pokemon form?
    } else if(strpos($text, 'pokemon_form_') === 0) {
        // Translation filename
        $tfile = CORE_LANG_PATH . '/pokemon_forms.json';

        $str = file_get_contents($tfile);
        $json = json_decode($str, true);

    // Pokemon type?
    } else if(strpos($text, 'pokemon_type_') === 0) {
        // Translation filename
        $tfile = CORE_LANG_PATH . '/pokemon_types.json';

        $str = file_get_contents($tfile);
        $json = json_decode($str, true);

    // Specific translation file?
    // E.g. Translation = hello_world_123, then check if hello_world.json exists.
    } else if(is_file(BOT_LANG_PATH . '/' . substr($text, 0, strrpos($text, '_')) . '.json')) {
        // Translation filename
        $tfile = BOT_LANG_PATH . '/' . substr($text, 0, strrpos($text, '_')) . '.json';

        $str = file_get_contents($tfile);
        $json = json_decode($str, true);

    // Other translation
    } else {
        // Custom language file.
        $tfile = CUSTOM_PATH . '/language.json';
        if(is_file($tfile)) {
            $str = file_get_contents($tfile);
            $json = json_decode($str, true);
        }

        // Core language file.
        if(!(isset($json[$text]))){
            // Translation filename
            $tfile = CORE_LANG_PATH . '/language.json';

            // Make sure file exists.
            if(is_file($tfile)) {
                $str = file_get_contents($tfile);
                $json = json_decode($str, true);
            }
        }

        // Bot language file. 
        if(!(isset($json[$text]))){
            // Translation filename
            $tfile = BOT_LANG_PATH . '/language.json';

            // Make sure file exists.
            if(is_file($tfile)) {
                $str = file_get_contents($tfile);
                $json = json_decode($str, true);
            }
        }

        // Translation not in core or bot language file? 
        if(!(isset($json[$text]))){
            // Get all bot specific language files
            $langfiles = glob(BOT_LANG_PATH . '/*.json');

            // Find translation in the right file
            foreach($langfiles as $file) {
                $tfile = $file;
                $str = file_get_contents($file);
                $json = json_decode($str, true);
                // Exit foreach once found
                if(isset($json[$text])) {
                    break;
                }
            }
        }
    }

    // Debug log translation file
    //debug_log($tfile,'T:');

    // Return pokemon name or translation
    if(strpos($text, 'pokemon_id_') === 0) {
        return $translation;
    } else {
        // Fallback to English when there is no language key
        if(isset($json[$text][$language])){
            $translation = $json[$text][$language];
        } else {
            $language = DEFAULT_LANGUAGE;
            $translation = $json[$text][$language];
        }
        return $translation;
    }
}
