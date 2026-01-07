<?php
/**
 * Password Generator Helper
 * Generates secure random passwords
 */

class PasswordGenerator
{
    /**
     * Generate a secure random password
     * 
     * @param int $length Password length (default: 12)
     * @return string Generated password
     */
    public static function generate($length = 12)
    {
        if ($length < 8) {
            $length = 8; // Minimum length for security
        }

        // Character sets
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()-_=+';

        // Ensure at least one character from each set
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];

        // Fill the rest with random characters from all sets
        $allChars = $uppercase . $lowercase . $numbers . $symbols;
        $remainingLength = $length - 4;

        for ($i = 0; $i < $remainingLength; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle the password to randomize character positions
        $password = str_shuffle($password);

        return $password;
    }

    /**
     * Validate password strength
     * 
     * @param string $password
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validate($password)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
