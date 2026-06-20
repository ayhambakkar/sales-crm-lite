-- =============================================================================
-- Migration: 003_create_leads_table.sql
-- Description: Creates the leads table for the first CRM module
-- =============================================================================

-- Run this file after 001_create_users_table.sql:
--   mysql -u <user> -p <database> < database/migrations/003_create_leads_table.sql

CREATE TABLE IF NOT EXISTS `leads` (
    `id`               INT UNSIGNED                                      NOT NULL AUTO_INCREMENT,
    `assigned_user_id` INT UNSIGNED                                      NULL,
    `first_name`       VARCHAR(100)                                      NOT NULL,
    `last_name`        VARCHAR(100)                                      NOT NULL,
    `company`          VARCHAR(150)                                      NULL,
    `email`            VARCHAR(255)                                      NULL,
    `phone`            VARCHAR(50)                                       NULL,
    `source`           VARCHAR(100)                                      NULL,
    `status`           ENUM('new', 'contacted', 'qualified', 'lost', 'converted')
                                                                          NOT NULL DEFAULT 'new',
    `priority`         ENUM('low', 'medium', 'high')                     NOT NULL DEFAULT 'medium',
    `estimated_value`  DECIMAL(12, 2)                                    NULL,
    `notes`            TEXT                                              NULL,
    `created_at`       TIMESTAMP                                         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP                                         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                                                   ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_leads_assigned_user_id` (`assigned_user_id`),
    KEY `idx_leads_status` (`status`),
    KEY `idx_leads_priority` (`priority`),
    KEY `idx_leads_created_at` (`created_at`),
    KEY `idx_leads_assigned_status_created` (`assigned_user_id`, `status`, `created_at`),

    CONSTRAINT `fk_leads_assigned_user`
        FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Stores CRM sales leads';
