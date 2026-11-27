-- Migration 012: Add loyalty_points column to parties table

-- [SQLITE]
ALTER TABLE parties ADD COLUMN loyalty_points REAL DEFAULT 0.00;

-- [MYSQL]
ALTER TABLE `parties` ADD COLUMN `loyalty_points` DECIMAL(12,2) DEFAULT 0.00;
