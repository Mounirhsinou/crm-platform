-- ============================================
-- Two-Factor Authentication Migration
-- Description: Adds 2FA support with backup codes
-- ============================================

USE `crm_db`;

-- ============================================
-- Update users table for 2FA
-- ============================================
ALTER TABLE `users` 
ADD COLUMN `two_factor_secret` VARCHAR(255) NULL AFTER `auth_provider`,
ADD COLUMN `two_factor_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `two_factor_secret`,
ADD COLUMN `two_factor_verified_at` TIMESTAMP NULL AFTER `two_factor_enabled`;

-- ============================================
-- Table: backup_codes
-- Description: Stores backup codes for 2FA recovery
-- ============================================
CREATE TABLE IF NOT EXISTS `backup_codes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `code` VARCHAR(10) NOT NULL,
  `used` TINYINT(1) NOT NULL DEFAULT 0,
  `used_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_code` (`code`),
  CONSTRAINT `fk_backup_codes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- End of 2FA Migration
-- ============================================
