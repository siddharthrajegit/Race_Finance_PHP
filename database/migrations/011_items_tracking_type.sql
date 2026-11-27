-- Migration 011: Add tracking_type ('none','batch','serial') to items table

-- [SQLITE]
ALTER TABLE items ADD COLUMN tracking_type TEXT DEFAULT 'none';

-- [MYSQL]
ALTER TABLE `items` ADD COLUMN `tracking_type` ENUM('none','batch','serial') DEFAULT 'none';
