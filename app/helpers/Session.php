<?php
/**
 * Session Helper Class
 * Manages session operations
 */

class Session
{
    /**
     * Start session if not already started
     */
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Set session variable
     * 
     * @param string $key Session key
     * @param mixed $value Session value
     */
    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get session variable
     * 
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session variable exists
     * 
     * @param string $key Session key
     * @return bool
     */
    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session variable
     * 
     * @param string $key Session key
     */
    public static function remove($key)
    {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy session
     */
    public static function destroy()
    {
        self::start();
        session_unset();
        session_destroy();
    }

    /**
     * Set flash message
     * 
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message text
     */
    public static function setFlash($type, $message)
    {
        self::start();
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Get and remove flash message
     * 
     * @param string $type Message type
     * @return string|null
     */
    public static function getFlash($type)
    {
        self::start();
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }

    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public static function isAuthenticated()
    {
        self::start();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Set user session (login)
     * 
     * @param array $user User data
     */
    public static function login($user)
    {
        self::start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['company_id'] = $user['company_id'];
        $_SESSION['company_name'] = $user['company_name'] ?? '';
        $_SESSION['language'] = $user['language'] ?? 'en';
        $_SESSION['login_time'] = time();
    }

    /**
     * Clear user session (logout)
     */
    public static function logout()
    {
        self::destroy();
    }

    /**
     * Regenerate session ID (security measure)
     */
    public static function regenerate()
    {
        self::start();
        session_regenerate_id(true);
    }
}
