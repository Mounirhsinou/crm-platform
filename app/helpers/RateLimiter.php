<?php
/**
 * RateLimiter Helper Class
 * Prevents brute-force attacks by tracking failed attempts
 */

class RateLimiter
{
    private $db;
    private $maxAttempts = 5;
    private $lockoutTime = 900; // 15 minutes in seconds

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Check if a user/IP is currently locked out
     * 
     * @param string $email
     * @param string $ip
     * @return bool
     */
    public function isLockedOut($email, $ip)
    {
        $since = date('Y-m-d H:i:s', time() - $this->lockoutTime);

        $sql = "SELECT COUNT(*) as count FROM login_attempts 
                WHERE (email = :email OR ip_address = :ip) 
                AND attempt_time > :since 
                AND is_successful = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':since', $since);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result['count'] >= $this->maxAttempts;
    }

    /**
     * Record a login attempt
     * 
     * @param string $email
     * @param string $ip
     * @param bool $isSuccessful
     */
    public function recordAttempt($email, $ip, $isSuccessful)
    {
        $sql = "INSERT INTO login_attempts (email, ip_address, is_successful) 
                VALUES (:email, :ip, :success)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':success', $isSuccessful ? 1 : 0);
        $stmt->execute();

        // Optional: Clean up old attempts
        if (rand(1, 100) === 1) {
            $this->cleanup();
        }
    }

    /**
     * Get remaining attempts before lockout
     * 
     * @param string $email
     * @param string $ip
     * @return int
     */
    public function getRemainingAttempts($email, $ip)
    {
        $since = date('Y-m-d H:i:s', time() - $this->lockoutTime);

        $sql = "SELECT COUNT(*) as count FROM login_attempts 
                WHERE (email = :email OR ip_address = :ip) 
                AND attempt_time > :since 
                AND is_successful = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':since', $since);
        $stmt->execute();
        $result = $stmt->fetch();

        return max(0, $this->maxAttempts - (int) $result['count']);
    }

    /**
     * Get lockout time remaining in minutes
     * 
     * @param string $email
     * @param string $ip
     * @return int
     */
    public function getLockoutTimeRemaining($email, $ip)
    {
        $since = date('Y-m-d H:i:s', time() - $this->lockoutTime);

        $sql = "SELECT MAX(attempt_time) as last_attempt FROM login_attempts 
                WHERE (email = :email OR ip_address = :ip) 
                AND is_successful = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':ip', $ip);
        $stmt->execute();
        $result = $stmt->fetch();

        if (!$result['last_attempt'])
            return 0;

        $wait = strtotime($result['last_attempt']) + $this->lockoutTime - time();
        return max(0, ceil($wait / 60));
    }

    /**
     * Clean up old attempts
     */
    private function cleanup()
    {
        $expiry = date('Y-m-d H:i:s', time() - (86400 * 7)); // 7 days
        $sql = "DELETE FROM login_attempts WHERE attempt_time < :expiry";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':expiry', $expiry);
        $stmt->execute();
    }
}
