-- Migration 006: Bank Statement CSV Import & Manual Reconciliation

-- [SQLITE]
CREATE TABLE IF NOT EXISTS bank_statement_lines (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    bank_account_code TEXT DEFAULT '1100',
    txn_date DATE NOT NULL,
    description TEXT NOT NULL,
    reference_no TEXT NULL,
    debit_amount REAL DEFAULT 0.00,
    credit_amount REAL DEFAULT 0.00,
    balance_after REAL NULL,
    match_status TEXT DEFAULT 'unmatched',
    matched_source_type TEXT NULL,
    matched_source_id INTEGER NULL,
    imported_batch TEXT NULL,
    matched_at DATETIME NULL,
    created_by INTEGER NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_bank_stmt_firm_status ON bank_statement_lines(firm_id, match_status, txn_date);

-- [MYSQL]
CREATE TABLE IF NOT EXISTS `bank_statement_lines` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `bank_account_code` VARCHAR(30) DEFAULT '1100',
    `txn_date` DATE NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `reference_no` VARCHAR(100) NULL,
    `debit_amount` DECIMAL(15,2) DEFAULT 0.00,
    `credit_amount` DECIMAL(15,2) DEFAULT 0.00,
    `balance_after` DECIMAL(15,2) NULL,
    `match_status` VARCHAR(30) DEFAULT 'unmatched',
    `matched_source_type` VARCHAR(50) NULL,
    `matched_source_id` INT NULL,
    `imported_batch` VARCHAR(80) NULL,
    `matched_at` DATETIME NULL,
    `created_by` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_bank_stmt_firm_status` (`firm_id`, `match_status`, `txn_date`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
