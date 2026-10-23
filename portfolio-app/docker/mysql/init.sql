-- Runs once, when the MySQL volume is first created: a separate database for automated tests.
CREATE DATABASE IF NOT EXISTS portfolio_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON portfolio_test.* TO 'portfolio'@'%';
FLUSH PRIVILEGES;
