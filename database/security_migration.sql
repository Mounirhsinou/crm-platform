-- Security Migration
-- Version: 1.0.0

-- Table for tracking login attempts for rate limiting
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `attempt_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `is_successful` TINYINT(1) DEFAULT 0,
    INDEX `idx_email_ip` (`email`, `ip_address`),
    INDEX `idx_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Note: Indices on users table (idx_two_factor, idx_is_active) are now included 
-- in the master schema.sql for fresh installations.
