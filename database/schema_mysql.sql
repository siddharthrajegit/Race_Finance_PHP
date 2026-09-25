-- ==============================================================================
-- RACE FINANCE - MySQL Database Schema (cPanel phpMyAdmin Import)
-- Character Set: utf8mb4, Engine: InnoDB
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+05:30";

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) UNIQUE NULL,
  `phone` VARCHAR(50) UNIQUE NULL,
  `password` VARCHAR(255) NULL,
  `google_id` VARCHAR(255) UNIQUE NULL,
  `avatar` VARCHAR(255) NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'user',
  `status` VARCHAR(50) NOT NULL DEFAULT 'active',
  `subscription_expires_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Firms / Businesses Table
CREATE TABLE IF NOT EXISTS `firms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `gstin` VARCHAR(50) NULL,
  `pan` VARCHAR(50) NULL,
  `phone` VARCHAR(50) NULL,
  `email` VARCHAR(255) NULL,
  `address` TEXT NULL,
  `city` VARCHAR(100) NULL,
  `state` VARCHAR(100) NULL,
  `state_code` VARCHAR(10) NULL,
  `pincode` VARCHAR(20) NULL,
  `bank_name` VARCHAR(255) NULL,
  `bank_account_no` VARCHAR(100) NULL,
  `bank_ifsc` VARCHAR(50) NULL,
  `bank_branch` VARCHAR(255) NULL,
  `upi_id` VARCHAR(100) NULL,
  `terms` TEXT NULL,
  `logo_path` VARCHAR(255) NULL,
  `signature_path` VARCHAR(255) NULL,
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Parties (Customers & Suppliers)
CREATE TABLE IF NOT EXISTS `parties` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `firm_id` INT NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'customer',
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NULL,
  `email` VARCHAR(255) NULL,
  `gstin` VARCHAR(50) NULL,
  `pan` VARCHAR(50) NULL,
  `billing_address` TEXT NULL,
  `shipping_address` TEXT NULL,
  `city` VARCHAR(100) NULL,
  `state` VARCHAR(100) NULL,
  `state_code` VARCHAR(10) NULL,
  `pincode` VARCHAR(20) NULL,
  `opening_balance` DECIMAL(15,2) DEFAULT 0.00,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Items / Inventory
CREATE TABLE IF NOT EXISTS `items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `firm_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `item_code` VARCHAR(100) NULL,
  `hsn_code` VARCHAR(50) NULL,
  `unit` VARCHAR(50) DEFAULT 'PCS',
  `sale_price` DECIMAL(15,2) DEFAULT 0.00,
  `purchase_price` DECIMAL(15,2) DEFAULT 0.00,
  `tax_rate` DECIMAL(5,2) DEFAULT 0.00,
  `tax_inclusive` TINYINT(1) DEFAULT 0,
  `opening_stock` DECIMAL(15,2) DEFAULT 0.00,
  `current_stock` DECIMAL(15,2) DEFAULT 0.00,
  `low_stock_threshold` DECIMAL(15,2) DEFAULT 5.00,
  `description` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Invoices (Sales & Purchase Bills)
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `firm_id` INT NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'sale',
  `invoice_number` VARCHAR(100) NOT NULL,
  `invoice_date` DATE NOT NULL,
  `due_date` DATE NULL,
  `party_id` INT NULL,
  `party_name` VARCHAR(255) NOT NULL,
  `party_phone` VARCHAR(50) NULL,
  `party_gstin` VARCHAR(50) NULL,
  `party_address` TEXT NULL,
  `party_state` VARCHAR(100) NULL,
  `party_state_code` VARCHAR(10) NULL,
  `is_gst_bill` TINYINT(1) DEFAULT 1,
  `is_interstate` TINYINT(1) DEFAULT 0,
  `subtotal` DECIMAL(15,2) DEFAULT 0.00,
  `discount_type` VARCHAR(50) DEFAULT 'percentage',
  `discount_value` DECIMAL(15,2) DEFAULT 0.00,
  `discount_amount` DECIMAL(15,2) DEFAULT 0.00,
  `taxable_amount` DECIMAL(15,2) DEFAULT 0.00,
  `cgst_amount` DECIMAL(15,2) DEFAULT 0.00,
  `sgst_amount` DECIMAL(15,2) DEFAULT 0.00,
  `igst_amount` DECIMAL(15,2) DEFAULT 0.00,
  `tax_amount` DECIMAL(15,2) DEFAULT 0.00,
  `round_off` DECIMAL(15,2) DEFAULT 0.00,
  `grand_total` DECIMAL(15,2) DEFAULT 0.00,
  `paid_amount` DECIMAL(15,2) DEFAULT 0.00,
  `balance_due` DECIMAL(15,2) DEFAULT 0.00,
  `payment_status` VARCHAR(50) DEFAULT 'unpaid',
  `payment_mode` VARCHAR(50) DEFAULT 'cash',
  `notes` TEXT NULL,
  `terms` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `idx_invoices_firm_type_number` (`firm_id`, `type`, `invoice_number`),
  FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`party_id`) REFERENCES `parties`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Invoice Line Items
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` INT NOT NULL,
  `item_id` INT NULL,
  `item_name` VARCHAR(255) NOT NULL,
  `hsn_code` VARCHAR(50) NULL,
  `unit` VARCHAR(50) DEFAULT 'PCS',
  `quantity` DECIMAL(15,2) NOT NULL DEFAULT 1.00,
  `rate` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
  `discount_amount` DECIMAL(15,2) DEFAULT 0.00,
  `taxable_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `tax_rate` DECIMAL(5,2) DEFAULT 0.00,
  `cgst_rate` DECIMAL(5,2) DEFAULT 0.00,
  `cgst_amount` DECIMAL(15,2) DEFAULT 0.00,
  `sgst_rate` DECIMAL(5,2) DEFAULT 0.00,
  `sgst_amount` DECIMAL(15,2) DEFAULT 0.00,
  `igst_rate` DECIMAL(5,2) DEFAULT 0.00,
  `igst_amount` DECIMAL(15,2) DEFAULT 0.00,
  `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Payments (In & Out)
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `firm_id` INT NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `payment_number` VARCHAR(100) NOT NULL,
  `payment_date` DATE NOT NULL,
  `party_id` INT NOT NULL,
  `invoice_id` INT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `payment_mode` VARCHAR(50) DEFAULT 'cash',
  `reference_no` VARCHAR(100) NULL,
  `notes` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `idx_payments_firm_type_number` (`firm_id`, `type`, `payment_number`),
  FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`party_id`) REFERENCES `parties`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Google Drive OAuth Tokens
CREATE TABLE IF NOT EXISTS `google_tokens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNIQUE NOT NULL,
  `access_token` TEXT NULL,
  `refresh_token` TEXT NULL,
  `scope` TEXT NULL,
  `token_type` VARCHAR(50) NULL,
  `expiry_date` BIGINT NULL,
  `email` VARCHAR(255) NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Extensible Firm Settings
CREATE TABLE IF NOT EXISTS `firm_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `firm_id` INT NOT NULL UNIQUE,
  `settings_json` LONGTEXT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Platform Global Settings
CREATE TABLE IF NOT EXISTS `platform_settings` (
  `key` VARCHAR(100) PRIMARY KEY,
  `value` TEXT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Admin Audit Logs
CREATE TABLE IF NOT EXISTS `admin_audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NULL,
  `admin_name` VARCHAR(255) NULL,
  `action` VARCHAR(100) NOT NULL,
  `target_type` VARCHAR(100) NULL,
  `target_id` VARCHAR(100) NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(50) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Document Sequences (Concurrency Safe Number Allocation)
CREATE TABLE IF NOT EXISTS `document_sequences` (
  `firm_id` INT NOT NULL,
  `doc_type` VARCHAR(50) NOT NULL,
  `last_number` INT NOT NULL DEFAULT 0,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`firm_id`, `doc_type`),
  FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Performance Indexes
CREATE INDEX `idx_firms_user` ON `firms`(`user_id`);
CREATE INDEX `idx_parties_firm` ON `parties`(`firm_id`);
CREATE INDEX `idx_items_firm` ON `items`(`firm_id`);
CREATE INDEX `idx_invoices_firm` ON `invoices`(`firm_id`);
CREATE INDEX `idx_invoices_party` ON `invoices`(`party_id`);
CREATE INDEX `idx_invoice_items_invoice` ON `invoice_items`(`invoice_id`);
CREATE INDEX `idx_payments_firm` ON `payments`(`firm_id`);
CREATE INDEX `idx_payments_party` ON `payments`(`party_id`);

-- Default Platform Settings Seed
INSERT INTO `platform_settings` (`key`, `value`) VALUES
('max_firms_limit', '2'),
('max_upload_size_mb', '2'),
('platform_announcement', ''),
('platform_announcement_type', 'info'),
('enable_announcement', '0'),
('maintenance_mode', '0')
ON DUPLICATE KEY UPDATE `value` = `value`;

-- Default Administrator Seed (Phone: 9414223562, Password: admin123)
-- Hash generated using standard bcrypt (cost 10)
INSERT INTO `users` (`name`, `phone`, `password`, `role`, `status`)
VALUES ('Admin', '9414223562', '$2y$10$gC/NoyXjJCQzQk1UiHjLqe7t8BpTuWg.IxW4iFxoEpgqYLrIYvabi', 'admin', 'active')
ON DUPLICATE KEY UPDATE `password` = VALUES(`password`), `role` = 'admin', `status` = 'active';

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
