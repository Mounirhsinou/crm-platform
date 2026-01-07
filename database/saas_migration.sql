-- ============================================
-- Multi-Tenant SaaS Migration
-- Version: 1.0
-- Date: 2026-01-02
-- Description: Adds company isolation and renames roles
-- ============================================

-- 1. Create Companies table
CREATE TABLE IF NOT EXISTS `companies` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_name` VARCHAR(255) NOT NULL,
  `logo_path` VARCHAR(255) DEFAULT NULL,
  `owner_id` INT UNSIGNED DEFAULT NULL, -- Will link to users table
  `owner_email` VARCHAR(255) DEFAULT NULL, -- Primary account email for ownership
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_owner_email` (`owner_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add company_id to users
ALTER TABLE `users` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `users` ADD CONSTRAINT `fk_users_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `users` ADD KEY `idx_company_id` (`company_id`);

-- Add foreign key for companies.owner_id (circular reference, owner_id can be NULL initially)
ALTER TABLE `companies` ADD CONSTRAINT `fk_companies_user` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 3. Add company_id to resource tables
ALTER TABLE `clients` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `clients` ADD CONSTRAINT `fk_clients_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `clients` ADD KEY `idx_company_id` (`company_id`);

ALTER TABLE `deals` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `deals` ADD CONSTRAINT `fk_deals_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `deals` ADD KEY `idx_company_id` (`company_id`);

ALTER TABLE `followups` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `followups` ADD CONSTRAINT `fk_followups_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `followups` ADD KEY `idx_company_id` (`company_id`);

ALTER TABLE `invoices` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `invoices` ADD CONSTRAINT `fk_invoices_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `invoices` ADD KEY `idx_company_id` (`company_id`);

ALTER TABLE `settings` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `settings` ADD CONSTRAINT `fk_settings_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `settings` ADD KEY `idx_company_id` (`company_id`);

-- 4. Activity Logs and Audit
ALTER TABLE `activity_logs` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `activity_logs` ADD CONSTRAINT `fk_activity_logs_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `activity_logs` ADD KEY `idx_company_id` (`company_id`);

-- 5. Rename role slug and name
UPDATE `roles` SET `name` = 'Owner', `slug` = 'owner' WHERE `slug` = 'super_admin';

-- 6. Update permissions to include "owner" bypass logic (conceptually)
-- In code, 'owner' will bypass, but we keep the permissions JSON for UI checks if needed.
UPDATE `roles` SET `description` = 'Workspace Owner with full access' WHERE `slug` = 'owner';

-- 7. Add company_id to leads
ALTER TABLE `leads` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `leads` ADD CONSTRAINT `fk_leads_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `leads` ADD KEY `idx_company_id` (`company_id`);

-- 8. SMTP Settings
ALTER TABLE `smtp_settings` RENAME COLUMN `user_id` TO `company_id`;
ALTER TABLE `smtp_settings` DROP FOREIGN KEY `fk_smtp_user`;
ALTER TABLE `smtp_settings` ADD CONSTRAINT `fk_smtp_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `smtp_settings` DROP INDEX `user_id`;
ALTER TABLE `smtp_settings` ADD UNIQUE KEY `company_id` (`company_id`);

-- 9. Email Campaigns & Logs
ALTER TABLE `email_campaigns` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `email_campaigns` ADD CONSTRAINT `fk_campaigns_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `email_campaigns` ADD KEY `idx_company_id` (`company_id`);

-- 10. Messaging Settings
ALTER TABLE `messaging_settings` RENAME COLUMN `user_id` TO `company_id`;
ALTER TABLE `messaging_settings` DROP INDEX `user_id_type_unique`; -- If it exists, or handle by name
ALTER TABLE `messaging_settings` ADD KEY `idx_company_id` (`company_id`);

-- 11. Activities (Timeline)
ALTER TABLE `activities` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `activities` ADD CONSTRAINT `fk_activities_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `activities` ADD KEY `idx_company_id` (`company_id`);

-- 12. Payment Templates (Public Links)
ALTER TABLE `payment_templates` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `payment_templates` ADD CONSTRAINT `fk_payment_templates_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `payment_templates` ADD KEY `idx_company_id` (`company_id`);

-- 13. Payments
ALTER TABLE `payments` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `payments` ADD CONSTRAINT `fk_payments_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `payments` ADD KEY `idx_company_id` (`company_id`);

-- 14. Message Logs
ALTER TABLE `message_logs` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `message_logs` ADD CONSTRAINT `fk_message_logs_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `message_logs` ADD KEY `idx_company_id` (`company_id`);

-- 15. Tasks
ALTER TABLE `tasks` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `tasks` ADD CONSTRAINT `fk_tasks_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `tasks` ADD KEY `idx_company_id` (`company_id`);

-- 16. Email Logs
ALTER TABLE `email_logs` ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `email_logs` ADD KEY `idx_company_id` (`company_id`);
