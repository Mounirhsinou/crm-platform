<?php
/**
 * Language Helper Class
 * Handles multi-language translations
 */

class Lang
{
    private static $translations = [];
    private static $currentLang = 'en';
    private static $fallbackLang = 'en';

    /**
     * Initialize language system
     * 
     * @param string $lang Language code (en, fr, ar)
     */
    public static function init($lang = null)
    {
        // Determine language: parameter > session > default
        if (!$lang) {
            $lang = Session::get('language', 'en');
        }

        self::$currentLang = $lang;
        self::load($lang);
    }

    /**
     * Load translation file
     * 
     * @param string $lang
     */
    private static function load($lang)
    {
        $file = APP_PATH . '/lang/' . $lang . '.php';

        if (file_exists($file)) {
            self::$translations = require $file;
        } else {
            // Fallback to English if file not found
            $fallbackFile = APP_PATH . '/lang/' . self::$fallbackLang . '.php';
            if (file_exists($fallbackFile)) {
                self::$translations = require $fallbackFile;
            }
        }
    }

    /**
     * Get translated text
     * 
     * @param string $key
     * @param array $placeholders
     * @return string
     */
    public static function get($key, $placeholders = [])
    {
        $text = self::$translations[$key] ?? $key;

        if (!empty($placeholders)) {
            foreach ($placeholders as $placeholder => $value) {
                $text = str_replace(':' . $placeholder, $value, $text);
            }
        }

        return $text;
    }

    /**
     * Get current language code
     * 
     * @return string
     */
    public static function current()
    {
        return self::$currentLang;
    }

    /**
     * Check if current language is RTL
     * 
     * @return bool
     */
    public static function isRtl()
    {
        return self::$currentLang === 'ar';
    }
}

/**
 * Global translation helper function
 */
function _t($key, $placeholders = [])
{
    return Lang::get($key, $placeholders);
}
