-- =============================================================================
-- Seeder: 001_seed_admin_user.sql
-- Description: Inserts the initial Admin user
--
-- IMPORTANT: This file does NOT contain a real password.
--            You must generate a bcrypt hash locally before running this file.
--
-- -----------------------------------------------------------------------------
-- Step 1: Generate a bcrypt hash for your chosen password
-- -----------------------------------------------------------------------------
--
-- Run this command in your terminal (replace "your_password" with a real one):
--
--   php -r "echo password_hash('your_password', PASSWORD_BCRYPT, ['cost' => 12]) . PHP_EOL;"
--
-- Example output (your output will look different — each hash is unique):
--   $2y$12$eImiTXuWVxfM37uY4JANjQe5Xo8Hf5Q9rT1uWvH3cJ8lJkm5Z2NK
--
-- -----------------------------------------------------------------------------
-- Step 2: Replace the placeholder below with your generated hash, then run:
--
--   mysql -u <user> -p <database> < database/seeders/001_seed_admin_user.sql
-- -----------------------------------------------------------------------------

INSERT INTO `users` (`first_name`, `last_name`, `email`, `password_hash`, `role`, `is_active`)
VALUES (
    'Admin',
    'User',
    'admin@example.com',
    'REPLACE_WITH_BCRYPT_HASH',   -- paste the output of the php command above
    'admin',
    1
);
