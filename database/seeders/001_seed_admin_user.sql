-- =============================================================================
-- Seeder: 001_seed_admin_user.sql
-- Description: Inserts or refreshes the local development Admin user
--
-- Local development login:
--   Email:    admin@example.com
--   Password: password
--
-- -----------------------------------------------------------------------------
-- Password hash
-- -----------------------------------------------------------------------------
--
-- The password above is stored as a bcrypt hash generated with:
--
--   php -r "echo password_hash('password', PASSWORD_BCRYPT, ['cost' => 12]) . PHP_EOL;"
--
-- -----------------------------------------------------------------------------
-- Usage
-- -----------------------------------------------------------------------------
--
--   mysql -u <user> -p <database> < database/seeders/001_seed_admin_user.sql
--
-- This seeder is idempotent. Re-running it will not create a duplicate admin user
-- because users.email has a unique index. It will refresh the local admin details.
-- -----------------------------------------------------------------------------

INSERT INTO `users` (`first_name`, `last_name`, `email`, `password_hash`, `role`, `is_active`)
VALUES (
    'Admin',
    'User',
    'admin@example.com',
    '$2y$12$95i3r1E6HJ/otfdj1TFvKen4.p97qWwGNWG2P4JMdJUA4AzSqzPt6',
    'admin',
    1
)
ON DUPLICATE KEY UPDATE
    `first_name`    = VALUES(`first_name`),
    `last_name`     = VALUES(`last_name`),
    `password_hash` = VALUES(`password_hash`),
    `role`          = VALUES(`role`),
    `is_active`     = VALUES(`is_active`);
