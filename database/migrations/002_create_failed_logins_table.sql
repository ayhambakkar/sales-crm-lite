-- =============================================================================
-- Migration: 002_create_failed_logins_table.sql
-- Description: Tracks failed login attempts for simple login throttling
-- =============================================================================

-- Run this file after 001_create_users_table.sql:
--   mysql -u <user> -p <database> < database/migrations/002_create_failed_logins_table.sql

CREATE TABLE IF NOT EXISTS `failed_logins` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`        VARCHAR(255) NOT NULL,
    `ip_address`   VARCHAR(45)  NOT NULL,
    `attempted_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_failed_logins_email_ip_attempted_at` (`email`, `ip_address`, `attempted_at`),
    KEY `idx_failed_logins_attempted_at` (`attempted_at`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Tracks failed authentication attempts for login throttling';
