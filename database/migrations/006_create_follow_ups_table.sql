-- =============================================================================
-- Migration: 006_create_follow_ups_table.sql
-- Description: Creates follow-up tasks linked to leads or customers
-- =============================================================================

-- Run this file after 005_add_lead_conversion_fields.sql:
--   mysql -u <user> -p <database> < database/migrations/006_create_follow_ups_table.sql

CREATE TABLE IF NOT EXISTS `follow_ups` (
    `id`               INT UNSIGNED                             NOT NULL AUTO_INCREMENT,
    `assigned_user_id` INT UNSIGNED                             NULL,
    `lead_id`          INT UNSIGNED                             NULL,
    `customer_id`      INT UNSIGNED                             NULL,
    `title`            VARCHAR(150)                             NOT NULL,
    `description`      TEXT                                     NULL,
    `due_at`           DATETIME                                 NOT NULL,
    `status`           ENUM('open', 'done', 'cancelled')        NOT NULL DEFAULT 'open',
    `priority`         ENUM('low', 'medium', 'high')            NOT NULL DEFAULT 'medium',
    `completed_at`     DATETIME                                 NULL,
    `created_at`       TIMESTAMP                                NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP                                NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                                           ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_follow_ups_assigned_user_id` (`assigned_user_id`),
    KEY `idx_follow_ups_lead_id` (`lead_id`),
    KEY `idx_follow_ups_customer_id` (`customer_id`),
    KEY `idx_follow_ups_status` (`status`),
    KEY `idx_follow_ups_due_at` (`due_at`),
    KEY `idx_follow_ups_priority` (`priority`),
    KEY `idx_follow_ups_assigned_status_due` (`assigned_user_id`, `status`, `due_at`),

    CONSTRAINT `fk_follow_ups_assigned_user`
        FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT `fk_follow_ups_lead`
        FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT `fk_follow_ups_customer`
        FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Stores scheduled CRM follow-up tasks';
