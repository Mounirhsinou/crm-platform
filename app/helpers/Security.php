<?php
/**
 * Security Helper Class
 * Handles CSRF protection, input sanitization, and security utilities
 */

class Security
{
    /**
     * Generate CSRF token
     * 
     * @return string
     */
    public static function generateCsrfToken()
    {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool
     */
    public static function validateCsrfToken($token)
    {
        if (!isset($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    /**
     * Log a security-related event
     * 
     * @param string $type Event type (e.g., 'unauthorized_access', 'failed_login')
     * @param string $description Detailed description
     * @param string $severity 'low', 'medium', 'high', 'critical'
     */
    public static function logSecurityEvent($type, $description, $severity = 'low')
    {
        $logDir = dirname(dirname(__DIR__)) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $userId = $_SESSION['user_id'] ?? 'guest';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? 'Unknown';

        $entry = "[" . date('Y-m-d H:i:s') . "] [$severity] [TYPE: $type] [USER: $userId] [IP: $ip] [URI: $uri]\n" .
            "Description: $description\n" .
            "User Agent: $ua\n" .
            "--------------------------------------------------\n";

        file_put_contents($logDir . '/security.log', $entry, FILE_APPEND);

        // Also record in ActivityLog if possible (using direct PDO to avoid dependency loops if needed)
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO activity_logs (company_id, user_id, action_type, description, ip_address, user_agent) 
                                 VALUES (:cid, :uid, :type, :desc, :ip, :ua)");
            $stmt->execute([
                ':cid' => $_SESSION['company_id'] ?? null,
                ':uid' => is_numeric($userId) ? $userId : null,
                ':type' => 'security_' . $type,
                ':desc' => "[$severity] " . $description,
                ':ip' => $ip,
                ':ua' => $ua
            ]);
        } catch (Exception $e) {
            // Silently fail activity log if DB is down, just keep file log
        }
    }

    /**
     * Sanitize input string
     * 
     * @param string $data Input data
     * @return string Sanitized data
     */
    public static function sanitize($data)
    {
        if ($data === null)
            return '';
        $data = trim((string) $data);
        $data = stripslashes($data);
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize array of data
     * 
     * @param array $data Input array
     * @return array Sanitized array
     */
    public static function sanitizeArray($data)
    {
        $sanitized = [];
        if (!is_array($data))
            return [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = self::sanitize($value);
            }
        }
        return $sanitized;
    }

    /**
     * Hash password
     * 
     * @param string $password Plain password
     * @return string Hashed password
     */
    public static function hashPassword($password)
    {
        return password_hash((string) $password, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
    }

    /**
     * Verify password
     * 
     * @param string $password Plain password
     * @param string $hash Hashed password
     * @return bool
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify((string) $password, (string) $hash);
    }

    /**
     * Escape output for HTML
     * 
     * @param string $data Data to escape
     * @return string Escaped data
     */
    public static function escape($data)
    {
        return htmlspecialchars((string) ($data ?? ''), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate random string
     * 
     * @param int $length String length
     * @return string
     */
    public static function randomString($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }
}
