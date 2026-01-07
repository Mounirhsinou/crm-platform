<?php
/**
 * TOTP (Time-based One-Time Password) Helper
 * Implements RFC 6238 for Google Authenticator
 */

class TOTP
{
    /**
     * Generate a random Base32 secret
     * 
     * @param int $length
     * @return string
     */
    public static function generateSecret($length = 16)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }

        return $secret;
    }

    /**
     * Verify a TOTP code
     * 
     * @param string $secret Base32 encoded secret
     * @param string $code 6-digit code to verify
     * @param int $window Time window (±steps)
     * @return bool
     */
    public static function verify($secret, $code, $window = 1)
    {
        $timestamp = time();
        $timeStep = 30;

        // Check current time and ±window
        for ($i = -$window; $i <= $window; $i++) {
            $time = floor($timestamp / $timeStep) + $i;

            if (self::generateCode($secret, $time) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate TOTP code for a given time
     * 
     * @param string $secret
     * @param int $time
     * @return string
     */
    private static function generateCode($secret, $time)
    {
        $key = self::base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $time);

        // HMAC-SHA1
        $hash = hash_hmac('sha1', $time, $key, true);

        // Dynamic truncation
        $offset = ord($hash[19]) & 0xf;

        $code = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;

        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Decode Base32 string
     * 
     * @param string $secret
     * @return string
     */
    private static function base32Decode($secret)
    {
        $secret = strtoupper($secret);
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $v = 0;
        $vbits = 0;

        for ($i = 0, $j = strlen($secret); $i < $j; $i++) {
            $v <<= 5;
            $v += stripos($alphabet, $secret[$i]);
            $vbits += 5;

            while ($vbits >= 8) {
                $vbits -= 8;
                $output .= chr($v >> $vbits);
                $v &= ((1 << $vbits) - 1);
            }
        }

        return $output;
    }

    /**
     * Get QR code URL for Google Authenticator
     * 
     * @param array $user
     * @param string $secret
     * @return string
     */
    public static function getQRCodeUrl($user, $secret)
    {
        $issuer = urlencode(APP_NAME);
        $account = urlencode($user['email']);

        $otpauth = "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}";

        // Using Google Charts API for QR code
        return "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=" . urlencode($otpauth);
    }

    /**
     * Get provisioning URI for manual entry
     * 
     * @param array $user
     * @param string $secret
     * @return string
     */
    public static function getProvisioningUri($user, $secret)
    {
        $issuer = urlencode(APP_NAME);
        $account = urlencode($user['email']);

        return "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}";
    }
}
