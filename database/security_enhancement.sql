-- ============================================
-- Security Enhancement Migration
-- Description: Adds Google OAuth support and password change
-- ============================================

USE `crm_db`;

-- ============================================
-- Update users table for OAuth support
-- ============================================
ALTER TABLE `users` 
ADD COLUMN `google_id` VARCHAR(255) NULL AFTER `password_hash`,
ADD COLUMN `auth_provider` ENUM('local', 'google') NOT NULL DEFAULT 'local' AFTER `google_id`,
ADD UNIQUE KEY `google_id` (`google_id`);

-- ============================================
-- End of Security Enhancement Migration
-- ============================================
