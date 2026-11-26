-- Migration 004: Chart of Accounts (Double-Entry Accounting Foundation)

-- [SQLITE]
CREATE TABLE IF NOT EXISTS chart_of_accounts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    code TEXT NOT NULL,
    name TEXT NOT NULL,
    account_type TEXT NOT NULL,
    parent_code TEXT NULL,
    opening_balance REAL DEFAULT 0.00,
    opening_side TEXT DEFAULT 'debit',
    is_system INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(firm_id, code),
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_coa_firm_type ON chart_of_accounts(firm_id, account_type);

-- [MYSQL]
CREATE TABLE IF NOT EXISTS `chart_of_accounts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `code` VARCHAR(30) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `account_type` ENUM('asset','liability','equity','income','expense') NOT NULL,
    `parent_code` VARCHAR(30) NULL,
    `opening_balance` DECIMAL(15,2) DEFAULT 0.00,
    `opening_side` ENUM('debit','credit') DEFAULT 'debit',
    `is_system` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_coa_firm_code` (`firm_id`, `code`),
    INDEX `idx_coa_firm_type` (`firm_id`, `account_type`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
