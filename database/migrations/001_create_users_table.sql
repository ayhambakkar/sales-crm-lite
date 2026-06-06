-- =============================================================================
-- Migration: 001_create_users_table.sql
-- Description: Creates the users table for authentication and access control
-- =============================================================================

-- Run this file against an existing database:
--   mysql -u <user> -p <database> < database/migrations/001_create_users_table.sql

CREATE TABLE IF NOT EXISTS `users` (
    `id`             INT UNSIGNED                    NOT NULL AUTO_INCREMENT,
    `first_name`     VARCHAR(100)                    NOT NULL,
    `last_name`      VARCHAR(100)                    NOT NULL,
    `email`          VARCHAR(255)                    NOT NULL,
    `password_hash`  VARCHAR(255)                    NOT NULL,
    `role`           ENUM('admin', 'sales_rep')      NOT NULL DEFAULT 'sales_rep',
    `is_active`      TINYINT(1)                      NOT NULL DEFAULT 1,
    `last_login_at`  DATETIME                        NULL,
    `created_at`     TIMESTAMP                       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP                       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                              ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Stores user accounts, credentials and role assignments';
