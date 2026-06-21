-- =============================================================================
-- Seeder: 002_seed_demo_crm_data.sql
-- Description: Creates idempotent demo users and CRM records for local demos
--
-- Local development logins:
--   Admin:     admin@example.com / password
--   Sales Rep: sales@example.com / password
--
-- Usage:
--   mysql -u root sales_crm < database/seeders/002_seed_demo_crm_data.sql
--
-- Run this after all migrations and after 001_seed_admin_user.sql.
-- Re-running this seeder refreshes demo users and relative follow-up due dates
-- without creating duplicate sample records.
-- =============================================================================

SET @demo_password_hash := '$2y$12$95i3r1E6HJ/otfdj1TFvKen4.p97qWwGNWG2P4JMdJUA4AzSqzPt6';

INSERT INTO `users` (`first_name`, `last_name`, `email`, `password_hash`, `role`, `is_active`)
VALUES
    ('Admin', 'User', 'admin@example.com', @demo_password_hash, 'admin', 1),
    ('Sales', 'Rep', 'sales@example.com', @demo_password_hash, 'sales_rep', 1)
ON DUPLICATE KEY UPDATE
    `first_name`    = VALUES(`first_name`),
    `last_name`     = VALUES(`last_name`),
    `password_hash` = VALUES(`password_hash`),
    `role`          = VALUES(`role`),
    `is_active`     = VALUES(`is_active`);

SET @admin_id := (SELECT `id` FROM `users` WHERE `email` = 'admin@example.com' LIMIT 1);
SET @sales_rep_id := (SELECT `id` FROM `users` WHERE `email` = 'sales@example.com' LIMIT 1);

INSERT INTO `leads` (
    `assigned_user_id`,
    `first_name`,
    `last_name`,
    `company`,
    `email`,
    `phone`,
    `source`,
    `status`,
    `priority`,
    `estimated_value`,
    `notes`
)
SELECT
    @admin_id,
    'Jamie',
    'Chen',
    'Northstar Analytics',
    'jamie.chen@example.com',
    '+1 555 0101',
    'Website',
    'qualified',
    'high',
    24000.00,
    'Interested in a pilot for the analytics team.'
WHERE NOT EXISTS (SELECT 1 FROM `leads` WHERE `email` = 'jamie.chen@example.com');

INSERT INTO `leads` (
    `assigned_user_id`,
    `first_name`,
    `last_name`,
    `company`,
    `email`,
    `phone`,
    `source`,
    `status`,
    `priority`,
    `estimated_value`,
    `notes`
)
SELECT
    @admin_id,
    'Morgan',
    'Lee',
    'Harbor Retail Group',
    'morgan.lee@example.com',
    '+1 555 0102',
    'Referral',
    'converted',
    'medium',
    9000.00,
    'Converted sample account for demo dashboards.'
WHERE NOT EXISTS (SELECT 1 FROM `leads` WHERE `email` = 'morgan.lee@example.com');

INSERT INTO `leads` (
    `assigned_user_id`,
    `first_name`,
    `last_name`,
    `company`,
    `email`,
    `phone`,
    `source`,
    `status`,
    `priority`,
    `estimated_value`,
    `notes`
)
SELECT
    @sales_rep_id,
    'Priya',
    'Shah',
    'Summit Solar',
    'priya.shah@example.com',
    '+1 555 0103',
    'LinkedIn',
    'new',
    'high',
    18000.00,
    'Needs a follow-up on implementation timeline.'
WHERE NOT EXISTS (SELECT 1 FROM `leads` WHERE `email` = 'priya.shah@example.com');

INSERT INTO `leads` (
    `assigned_user_id`,
    `first_name`,
    `last_name`,
    `company`,
    `email`,
    `phone`,
    `source`,
    `status`,
    `priority`,
    `estimated_value`,
    `notes`
)
SELECT
    @sales_rep_id,
    'Carlos',
    'Rivera',
    'Brightline Logistics',
    'carlos.rivera@example.com',
    '+1 555 0104',
    'Trade Show',
    'lost',
    'low',
    5000.00,
    'Not a fit this quarter.'
WHERE NOT EXISTS (SELECT 1 FROM `leads` WHERE `email` = 'carlos.rivera@example.com');

UPDATE `leads`
SET
    `assigned_user_id` = @admin_id,
    `first_name` = 'Jamie',
    `last_name` = 'Chen',
    `company` = 'Northstar Analytics',
    `phone` = '+1 555 0101',
    `source` = 'Website',
    `status` = 'qualified',
    `priority` = 'high',
    `estimated_value` = 24000.00,
    `notes` = 'Interested in a pilot for the analytics team.'
WHERE `email` = 'jamie.chen@example.com';

UPDATE `leads`
SET
    `assigned_user_id` = @admin_id,
    `first_name` = 'Morgan',
    `last_name` = 'Lee',
    `company` = 'Harbor Retail Group',
    `phone` = '+1 555 0102',
    `source` = 'Referral',
    `status` = 'converted',
    `priority` = 'medium',
    `estimated_value` = 9000.00,
    `notes` = 'Converted sample account for demo dashboards.'
WHERE `email` = 'morgan.lee@example.com';

UPDATE `leads`
SET
    `assigned_user_id` = @sales_rep_id,
    `first_name` = 'Priya',
    `last_name` = 'Shah',
    `company` = 'Summit Solar',
    `phone` = '+1 555 0103',
    `source` = 'LinkedIn',
    `status` = 'new',
    `priority` = 'high',
    `estimated_value` = 18000.00,
    `notes` = 'Needs a follow-up on implementation timeline.'
WHERE `email` = 'priya.shah@example.com';

UPDATE `leads`
SET
    `assigned_user_id` = @sales_rep_id,
    `first_name` = 'Carlos',
    `last_name` = 'Rivera',
    `company` = 'Brightline Logistics',
    `phone` = '+1 555 0104',
    `source` = 'Trade Show',
    `status` = 'lost',
    `priority` = 'low',
    `estimated_value` = 5000.00,
    `notes` = 'Not a fit this quarter.'
WHERE `email` = 'carlos.rivera@example.com';

INSERT INTO `customers` (
    `assigned_user_id`,
    `first_name`,
    `last_name`,
    `company`,
    `email`,
    `phone`,
    `address`,
    `city`,
    `postal_code`,
    `country`,
    `customer_status`,
    `notes`
)
SELECT
    @admin_id,
    'Morgan',
    'Lee',
    'Harbor Retail Group',
    'morgan.lee@example.com',
    '+1 555 0102',
    '100 Harbor Way',
    'Seattle',
    '98101',
    'United States',
    'active',
    'Converted from lead for the MVP demo.'
WHERE NOT EXISTS (SELECT 1 FROM `customers` WHERE `email` = 'morgan.lee@example.com');

INSERT INTO `customers` (
    `assigned_user_id`,
    `first_name`,
    `last_name`,
    `company`,
    `email`,
    `phone`,
    `address`,
    `city`,
    `postal_code`,
    `country`,
    `customer_status`,
    `notes`
)
SELECT
    @admin_id,
    'Dana',
    'Kim',
    'Meridian Health',
    'dana.kim@example.com',
    '+1 555 0201',
    '45 Meridian Plaza',
    'Boston',
    '02110',
    'United States',
    'vip',
    'VIP account with quarterly expansion potential.'
WHERE NOT EXISTS (SELECT 1 FROM `customers` WHERE `email` = 'dana.kim@example.com');

INSERT INTO `customers` (
    `assigned_user_id`,
    `first_name`,
    `last_name`,
    `company`,
    `email`,
    `phone`,
    `address`,
    `city`,
    `postal_code`,
    `country`,
    `customer_status`,
    `notes`
)
SELECT
    @sales_rep_id,
    'Riley',
    'Patel',
    'Bluebird Studios',
    'riley.patel@example.com',
    '+1 555 0202',
    '72 Studio Lane',
    'Austin',
    '78701',
    'United States',
    'active',
    'Active customer assigned to the sales rep.'
WHERE NOT EXISTS (SELECT 1 FROM `customers` WHERE `email` = 'riley.patel@example.com');

INSERT INTO `customers` (
    `assigned_user_id`,
    `first_name`,
    `last_name`,
    `company`,
    `email`,
    `phone`,
    `address`,
    `city`,
    `postal_code`,
    `country`,
    `customer_status`,
    `notes`
)
SELECT
    @sales_rep_id,
    'Sofia',
    'Garcia',
    'Greenfield Foods',
    'sofia.garcia@example.com',
    '+1 555 0203',
    '18 Market Street',
    'Denver',
    '80202',
    'United States',
    'inactive',
    'Inactive sample account for status filtering.'
WHERE NOT EXISTS (SELECT 1 FROM `customers` WHERE `email` = 'sofia.garcia@example.com');

UPDATE `customers`
SET
    `assigned_user_id` = @admin_id,
    `first_name` = 'Morgan',
    `last_name` = 'Lee',
    `company` = 'Harbor Retail Group',
    `phone` = '+1 555 0102',
    `address` = '100 Harbor Way',
    `city` = 'Seattle',
    `postal_code` = '98101',
    `country` = 'United States',
    `customer_status` = 'active',
    `notes` = 'Converted from lead for the MVP demo.'
WHERE `email` = 'morgan.lee@example.com';

UPDATE `customers`
SET
    `assigned_user_id` = @admin_id,
    `first_name` = 'Dana',
    `last_name` = 'Kim',
    `company` = 'Meridian Health',
    `phone` = '+1 555 0201',
    `address` = '45 Meridian Plaza',
    `city` = 'Boston',
    `postal_code` = '02110',
    `country` = 'United States',
    `customer_status` = 'vip',
    `notes` = 'VIP account with quarterly expansion potential.'
WHERE `email` = 'dana.kim@example.com';

UPDATE `customers`
SET
    `assigned_user_id` = @sales_rep_id,
    `first_name` = 'Riley',
    `last_name` = 'Patel',
    `company` = 'Bluebird Studios',
    `phone` = '+1 555 0202',
    `address` = '72 Studio Lane',
    `city` = 'Austin',
    `postal_code` = '78701',
    `country` = 'United States',
    `customer_status` = 'active',
    `notes` = 'Active customer assigned to the sales rep.'
WHERE `email` = 'riley.patel@example.com';

UPDATE `customers`
SET
    `assigned_user_id` = @sales_rep_id,
    `first_name` = 'Sofia',
    `last_name` = 'Garcia',
    `company` = 'Greenfield Foods',
    `phone` = '+1 555 0203',
    `address` = '18 Market Street',
    `city` = 'Denver',
    `postal_code` = '80202',
    `country` = 'United States',
    `customer_status` = 'inactive',
    `notes` = 'Inactive sample account for status filtering.'
WHERE `email` = 'sofia.garcia@example.com';

SET @lead_jamie_id := (SELECT `id` FROM `leads` WHERE `email` = 'jamie.chen@example.com' LIMIT 1);
SET @lead_morgan_id := (SELECT `id` FROM `leads` WHERE `email` = 'morgan.lee@example.com' LIMIT 1);
SET @lead_priya_id := (SELECT `id` FROM `leads` WHERE `email` = 'priya.shah@example.com' LIMIT 1);
SET @customer_morgan_id := (SELECT `id` FROM `customers` WHERE `email` = 'morgan.lee@example.com' LIMIT 1);
SET @customer_dana_id := (SELECT `id` FROM `customers` WHERE `email` = 'dana.kim@example.com' LIMIT 1);
SET @customer_sofia_id := (SELECT `id` FROM `customers` WHERE `email` = 'sofia.garcia@example.com' LIMIT 1);

UPDATE `leads`
SET
    `status` = 'converted',
    `converted_customer_id` = @customer_morgan_id,
    `converted_at` = DATE_SUB(NOW(), INTERVAL 3 DAY)
WHERE `id` = @lead_morgan_id;

INSERT INTO `follow_ups` (
    `assigned_user_id`,
    `lead_id`,
    `customer_id`,
    `title`,
    `description`,
    `due_at`,
    `status`,
    `priority`,
    `completed_at`
)
SELECT
    @admin_id,
    @lead_jamie_id,
    NULL,
    'Call Jamie about pilot scope',
    'Confirm success criteria and next stakeholders.',
    DATE_ADD(NOW(), INTERVAL 2 DAY),
    'open',
    'high',
    NULL
WHERE @lead_jamie_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM `follow_ups`
      WHERE `title` = 'Call Jamie about pilot scope'
        AND `lead_id` = @lead_jamie_id
  );

INSERT INTO `follow_ups` (
    `assigned_user_id`,
    `lead_id`,
    `customer_id`,
    `title`,
    `description`,
    `due_at`,
    `status`,
    `priority`,
    `completed_at`
)
SELECT
    @sales_rep_id,
    @lead_priya_id,
    NULL,
    'Send Priya implementation timeline',
    'Overdue demo task to show follow-up urgency.',
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    'open',
    'high',
    NULL
WHERE @lead_priya_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM `follow_ups`
      WHERE `title` = 'Send Priya implementation timeline'
        AND `lead_id` = @lead_priya_id
  );

INSERT INTO `follow_ups` (
    `assigned_user_id`,
    `lead_id`,
    `customer_id`,
    `title`,
    `description`,
    `due_at`,
    `status`,
    `priority`,
    `completed_at`
)
SELECT
    @admin_id,
    NULL,
    @customer_dana_id,
    'Review Dana quarterly plan',
    'Completed demo follow-up for customer history.',
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    'done',
    'medium',
    NOW()
WHERE @customer_dana_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM `follow_ups`
      WHERE `title` = 'Review Dana quarterly plan'
        AND `customer_id` = @customer_dana_id
  );

INSERT INTO `follow_ups` (
    `assigned_user_id`,
    `lead_id`,
    `customer_id`,
    `title`,
    `description`,
    `due_at`,
    `status`,
    `priority`,
    `completed_at`
)
SELECT
    @sales_rep_id,
    NULL,
    @customer_sofia_id,
    'Reconnect with Sofia next quarter',
    'Cancelled demo task for lifecycle coverage.',
    DATE_ADD(NOW(), INTERVAL 5 DAY),
    'cancelled',
    'low',
    NULL
WHERE @customer_sofia_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM `follow_ups`
      WHERE `title` = 'Reconnect with Sofia next quarter'
        AND `customer_id` = @customer_sofia_id
  );

UPDATE `follow_ups`
SET
    `assigned_user_id` = @admin_id,
    `customer_id` = NULL,
    `description` = 'Confirm success criteria and next stakeholders.',
    `due_at` = DATE_ADD(NOW(), INTERVAL 2 DAY),
    `status` = 'open',
    `priority` = 'high',
    `completed_at` = NULL
WHERE `title` = 'Call Jamie about pilot scope'
  AND `lead_id` = @lead_jamie_id;

UPDATE `follow_ups`
SET
    `assigned_user_id` = @sales_rep_id,
    `customer_id` = NULL,
    `description` = 'Overdue demo task to show follow-up urgency.',
    `due_at` = DATE_SUB(NOW(), INTERVAL 2 DAY),
    `status` = 'open',
    `priority` = 'high',
    `completed_at` = NULL
WHERE `title` = 'Send Priya implementation timeline'
  AND `lead_id` = @lead_priya_id;

UPDATE `follow_ups`
SET
    `assigned_user_id` = @admin_id,
    `lead_id` = NULL,
    `description` = 'Completed demo follow-up for customer history.',
    `due_at` = DATE_SUB(NOW(), INTERVAL 1 DAY),
    `status` = 'done',
    `priority` = 'medium',
    `completed_at` = NOW()
WHERE `title` = 'Review Dana quarterly plan'
  AND `customer_id` = @customer_dana_id;

UPDATE `follow_ups`
SET
    `assigned_user_id` = @sales_rep_id,
    `lead_id` = NULL,
    `description` = 'Cancelled demo task for lifecycle coverage.',
    `due_at` = DATE_ADD(NOW(), INTERVAL 5 DAY),
    `status` = 'cancelled',
    `priority` = 'low',
    `completed_at` = NULL
WHERE `title` = 'Reconnect with Sofia next quarter'
  AND `customer_id` = @customer_sofia_id;
