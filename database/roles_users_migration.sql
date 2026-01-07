-- ============================================
-- Role & User Management System Migration
-- Version: 1.0
-- Date: 2026-01-02
-- Description: Adds role-based access control
-- ============================================

-- ============================================
-- Table: roles
-- Description: Stores user roles and permissions
-- ============================================
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `permissions` JSON NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Seed default roles with permissions
-- ============================================

-- Super Admin: Full system access
INSERT INTO `roles` (`name`, `slug`, `description`, `permissions`) VALUES
('Super Admin', 'super_admin', 'Full system access including user management and security settings', 
'{
  "clients": ["view", "create", "edit", "delete"],
  "deals": ["view", "create", "edit", "delete"],
  "invoices": ["view", "create", "edit", "delete", "send"],
  "followups": ["view", "create", "edit", "delete"],
  "reports": ["view", "export"],
  "settings": ["view", "edit"],
  "payments": ["view", "edit"],
  "security": ["view", "edit"],
  "integrations": ["view", "edit"],
  "users": ["view", "create", "edit", "delete"]
}');

-- Admin: Manage clients, deals, invoices, reports (no payment keys, no security)
INSERT INTO `roles` (`name`, `slug`, `description`, `permissions`) VALUES
('Admin', 'admin', 'Manage clients, deals, invoices, and reports without access to payment or security settings',
'{
  "clients": ["view", "create", "edit", "delete"],
  "deals": ["view", "create", "edit", "delete"],
  "invoices": ["view", "create", "edit", "delete", "send"],
  "followups": ["view", "create", "edit", "delete"],
  "reports": ["view", "export"],
  "settings": ["view", "edit"]
}');

-- Moderator: Manage clients, deals, follow-ups, tasks (no delete, no settings)
INSERT INTO `roles` (`name`, `slug`, `description`, `permissions`) VALUES
('Moderator', 'moderator', 'Manage clients, deals, and follow-ups with limited permissions',
'{
  "clients": ["view", "create", "edit"],
  "deals": ["view", "create", "edit"],
  "followups": ["view", "create", "edit", "delete"],
  "reports": ["view"]
}');

-- Developer: Access integrations & API keys only
INSERT INTO `roles` (`name`, `slug`, `description`, `permissions`) VALUES
('Developer', 'developer', 'Access to integrations and API configuration only',
'{
  "integrations": ["view", "edit"],
  "settings": ["view"]
}');

-- Viewer: Read-only access
INSERT INTO `roles` (`name`, `slug`, `description`, `permissions`) VALUES
('Viewer', 'viewer', 'Read-only access to clients, deals, and invoices',
'{
  "clients": ["view"],
  "deals": ["view"],
  "invoices": ["view"],
  "reports": ["view"]
}');

-- ============================================
-- Alter users table to add role support
-- ============================================

-- Add role_id column
ALTER TABLE `users` 
ADD COLUMN `role_id` INT UNSIGNED DEFAULT NULL AFTER `password_hash`,
ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Add must_change_password flag for first-login flow
ALTER TABLE `users` 
ADD COLUMN `must_change_password` TINYINT(1) DEFAULT 0 AFTER `role_id`;

-- Add is_active flag for enable/disable functionality
ALTER TABLE `users` 
ADD COLUMN `is_active` TINYINT(1) DEFAULT 1 AFTER `must_change_password`;

-- Add index for role_id
ALTER TABLE `users` 
ADD KEY `idx_role_id` (`role_id`);

-- Add index for is_active
ALTER TABLE `users` 
ADD KEY `idx_is_active` (`is_active`);

-- ============================================
-- Migrate existing users to Super Admin role
-- ============================================

-- Set all existing users as Super Admin (role_id = 1)
UPDATE `users` SET `role_id` = 1 WHERE `role_id` IS NULL;

-- ============================================
-- End of Migration
-- ============================================
