-- =============================================================================
-- Migration: 007_create_activities_table.sql
-- Description: Creates immutable activity log entries for important CRM actions
-- =============================================================================

-- Run this file after 006_create_follow_ups_table.sql:
--   mysql -u <user> -p <database> < database/migrations/007_create_activities_table.sql

CREATE TABLE IF NOT EXISTS `activities` (
    `id`          INT UNSIGNED       NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED       NULL,
    `entity_type` VARCHAR(50)        NOT NULL,
    `entity_id`   INT UNSIGNED       NULL,
    `action`      VARCHAR(50)        NOT NULL,
    `description` TEXT               NULL,
    `metadata`    JSON               NULL,
    `created_at`  TIMESTAMP          NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_activities_user_id` (`user_id`),
    KEY `idx_activities_entity_type` (`entity_type`),
    KEY `idx_activities_entity_id` (`entity_id`),
    KEY `idx_activities_action` (`action`),
    KEY `idx_activities_created_at` (`created_at`),
    KEY `idx_activities_entity_created` (`entity_type`, `entity_id`, `created_at`),

    CONSTRAINT `fk_activities_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Immutable CRM activity and audit trail';
