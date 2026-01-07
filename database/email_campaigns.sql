-- ============================================
-- Email Campaign Module - Database Schema
-- Description: SMTP settings, campaigns, and email logs
-- ============================================

USE `crm_db`;

-- ============================================
-- Table: smtp_settings
-- Description: User SMTP configuration
-- ============================================
CREATE TABLE IF NOT EXISTS `smtp_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `smtp_host` VARCHAR(255) NOT NULL,
  `smtp_port` INT NOT NULL DEFAULT 587,
  `smtp_username` VARCHAR(255) NOT NULL,
  `smtp_password` TEXT NOT NULL COMMENT 'Encrypted',
  `smtp_encryption` ENUM('ssl', 'tls') DEFAULT 'tls',
  `from_name` VARCHAR(255) NOT NULL,
  `from_email` VARCHAR(255) NOT NULL,
  `is_verified` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_smtp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: email_campaigns
-- Description: Email campaigns
-- ============================================
CREATE TABLE IF NOT EXISTS `email_campaigns` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `recipient_type` ENUM('all', 'filtered', 'manual') DEFAULT 'all',
  `recipient_ids` TEXT COMMENT 'JSON array of client IDs',
  `status` ENUM('draft', 'sending', 'sent', 'failed') DEFAULT 'draft',
  `total_recipients` INT DEFAULT 0,
  `sent_count` INT DEFAULT 0,
  `failed_count` INT DEFAULT 0,
  `scheduled_at` TIMESTAMP NULL,
  `sent_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_campaign_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: email_logs
-- Description: Email sending logs
-- ============================================
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` INT UNSIGNED NOT NULL,
  `client_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `status` ENUM('sent', 'failed') NOT NULL,
  `error_message` TEXT,
  `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_campaign_id` (`campaign_id`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_status` (`status`),
  UNIQUE KEY `unique_send` (`campaign_id`, `client_id`),
  CONSTRAINT `fk_log_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `email_campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_log_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- End of Email Campaign Schema
-- ============================================
