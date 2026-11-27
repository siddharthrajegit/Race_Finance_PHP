-- Migration 009: Customer Loyalty Program & Point Transactions

-- [SQLITE]
CREATE TABLE IF NOT EXISTS loyalty_programs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL UNIQUE,
    is_enabled INTEGER DEFAULT 0,
    program_name TEXT DEFAULT 'Rewards Club',
    points_per_currency REAL DEFAULT 1.00,
    currency_unit REAL DEFAULT 100.00,
    point_value REAL DEFAULT 1.00,
    min_redeem_points REAL DEFAULT 10.00,
    max_redeem_pct REAL DEFAULT 50.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS loyalty_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    party_id INTEGER NOT NULL,
    invoice_id INTEGER NULL,
    txn_type TEXT NOT NULL,
    points_delta REAL NOT NULL,
    balance_after REAL NOT NULL DEFAULT 0.00,
    notes TEXT NULL,
    created_by INTEGER NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE,
    FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_loyalty_txns_firm_party ON loyalty_transactions(firm_id, party_id, created_at);
CREATE INDEX IF NOT EXISTS idx_loyalty_txns_invoice ON loyalty_transactions(firm_id, invoice_id);

-- [MYSQL]
CREATE TABLE IF NOT EXISTS `loyalty_programs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL UNIQUE,
    `is_enabled` TINYINT(1) DEFAULT 0,
    `program_name` VARCHAR(150) DEFAULT 'Rewards Club',
    `points_per_currency` DECIMAL(10,2) DEFAULT 1.00,
    `currency_unit` DECIMAL(10,2) DEFAULT 100.00,
    `point_value` DECIMAL(10,2) DEFAULT 1.00,
    `min_redeem_points` DECIMAL(10,2) DEFAULT 10.00,
    `max_redeem_pct` DECIMAL(5,2) DEFAULT 50.00,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `loyalty_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `party_id` INT NOT NULL,
    `invoice_id` INT NULL,
    `txn_type` VARCHAR(30) NOT NULL,
    `points_delta` DECIMAL(12,2) NOT NULL,
    `balance_after` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `notes` VARCHAR(255) NULL,
    `created_by` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_loyalty_txns_firm_party` (`firm_id`, `party_id`, `created_at`),
    INDEX `idx_loyalty_txns_invoice` (`firm_id`, `invoice_id`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`party_id`) REFERENCES `parties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
