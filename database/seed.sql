-- ============================================
-- CRM Demo Data
-- Version: 1.2.0
-- Description: Seeding data for Demo Company and Admin User
-- ============================================

-- 1. Create Demo Company
INSERT INTO `companies` (`id`, `company_name`, `owner_email`) VALUES
(1, 'Demo Company Ltd.', 'demo@crm.com');

-- 2. Insert Demo User (Owner)
-- Password: demo123
INSERT INTO `users` (`id`, `company_id`, `role_id`, `email`, `password_hash`, `full_name`, `is_active`) VALUES
(1, 1, 1, 'demo@crm.com', '$2y$10$rVItzvSm65s8IFG5IIkRWOEsmK69wdYAvlm3bCMDSKbUu/SNnXO7i', 'Demo Admin', 1);

-- Link company owner_id back to the user
UPDATE `companies` SET `owner_id` = 1 WHERE `id` = 1;

-- 3. Demo Clients
INSERT INTO `clients` (`company_id`, `user_id`, `name`, `phone`, `email`, `notes`, `created_at`) VALUES
(1, 1, 'John Smith', '+1-555-0101', 'john.smith@email.com', 'Interested in premium package.', DATE_SUB(NOW(), INTERVAL 30 DAY)),
(1, 1, 'Sarah Johnson', '+1-555-0102', 'sarah.j@company.com', 'Corporate client.', DATE_SUB(NOW(), INTERVAL 25 DAY)),
(1, 1, 'Michael Brown', '+1-555-0103', 'mbrown@business.net', 'Referred by John Smith.', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(1, 1, 'Emily Davis', '+1-555-0104', 'emily.davis@startup.io', 'Startup founder.', DATE_SUB(NOW(), INTERVAL 15 DAY));

-- 4. Demo Deals
INSERT INTO `deals` (`company_id`, `user_id`, `client_id`, `title`, `amount`, `status`, `created_at`) VALUES
(1, 1, 1, 'Premium Package - Q1 2025', 5000.00, 'completed', DATE_SUB(NOW(), INTERVAL 28 DAY)),
(1, 1, 2, 'Corporate License - Annual', 12000.00, 'in_progress', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(1, 1, 3, 'Bulk Order - 50 Units', 8500.00, 'in_progress', DATE_SUB(NOW(), INTERVAL 18 DAY));

-- 5. Demo Invoices
INSERT INTO `invoices` (`company_id`, `user_id`, `client_id`, `deal_id`, `invoice_number`, `amount`, `status`, `created_at`) VALUES
(1, 1, 1, 1, 'INV-2025-0001', 5000.00, 'paid', DATE_SUB(NOW(), INTERVAL 25 DAY)),
(1, 1, 2, 2, 'INV-2025-0002', 12000.00, 'unpaid', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- 6. Demo Settings
INSERT INTO `settings` (`company_id`, `user_id`, `setting_key`, `setting_value`) VALUES
(1, 1, 'company_address', '123 Business Street, Suite 100, City, State 12345'),
(1, 1, 'company_phone', '+1-555-0100'),
(1, 1, 'invoice_prefix', 'INV'),
(1, 1, 'currency_symbol', '$'),
(1, 1, 'timezone', 'America/New_York');
