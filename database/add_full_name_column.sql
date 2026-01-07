-- ============================================
-- Migration: Add full_name to users table
-- Date: 2025-12-31
-- Description: Adds full_name column to users table for register page
-- ============================================

USE `crm_db`;

-- Add full_name column to users table
ALTER TABLE `users` 
ADD COLUMN `full_name` VARCHAR(255) NULL AFTER `company_name`;

-- Update existing users with a default full_name (optional)
-- UPDATE `users` SET `full_name` = `company_name` WHERE `full_name` IS NULL;

-- ============================================
-- End of Migration
-- ============================================
