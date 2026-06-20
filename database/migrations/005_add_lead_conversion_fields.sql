-- =============================================================================
-- Migration: 005_add_lead_conversion_fields.sql
-- Description: Adds lead-to-customer conversion tracking
-- =============================================================================

-- Run this file after 004_create_customers_table.sql:
--   mysql -u <user> -p <database> < database/migrations/005_add_lead_conversion_fields.sql

ALTER TABLE `leads`
    ADD COLUMN `converted_customer_id` INT UNSIGNED NULL AFTER `notes`,
    ADD COLUMN `converted_at` DATETIME NULL AFTER `converted_customer_id`,
    ADD KEY `idx_leads_converted_customer_id` (`converted_customer_id`),
    ADD KEY `idx_leads_status_converted_at` (`status`, `converted_at`),
    ADD CONSTRAINT `fk_leads_converted_customer`
        FOREIGN KEY (`converted_customer_id`) REFERENCES `customers` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
