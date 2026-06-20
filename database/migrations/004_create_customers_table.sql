-- =============================================================================
-- Migration: 004_create_customers_table.sql
-- Description: Creates the customers table for account management
-- =============================================================================

-- Run this file after 001_create_users_table.sql:
--   mysql -u <user> -p <database> < database/migrations/004_create_customers_table.sql

CREATE TABLE IF NOT EXISTS `customers` (
    `id`              INT UNSIGNED                         NOT NULL AUTO_INCREMENT,
    `assigned_user_id` INT UNSIGNED                        NULL,
    `first_name`      VARCHAR(100)                         NOT NULL,
    `last_name`       VARCHAR(100)                         NOT NULL,
    `company`         VARCHAR(150)                         NULL,
    `email`           VARCHAR(255)                         NULL,
    `phone`           VARCHAR(50)                          NULL,
    `address`         VARCHAR(255)                         NULL,
    `city`            VARCHAR(100)                         NULL,
    `postal_code`     VARCHAR(30)                          NULL,
    `country`         VARCHAR(100)                         NULL,
    `customer_status` ENUM('active', 'inactive', 'vip')    NOT NULL DEFAULT 'active',
    `notes`           TEXT                                 NULL,
    `created_at`      TIMESTAMP                            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP                            NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                                      ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_customers_assigned_user_id` (`assigned_user_id`),
    KEY `idx_customers_customer_status` (`customer_status`),
    KEY `idx_customers_company` (`company`),
    KEY `idx_customers_created_at` (`created_at`),
    KEY `idx_customers_assigned_status_created` (`assigned_user_id`, `customer_status`, `created_at`),

    CONSTRAINT `fk_customers_assigned_user`
        FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Stores customer accounts';
