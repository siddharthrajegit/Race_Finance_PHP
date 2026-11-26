-- Migration 005: Journal Entries & Journal Lines

-- [SQLITE]
CREATE TABLE IF NOT EXISTS journal_entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    entry_number TEXT NOT NULL,
    entry_date DATE NOT NULL,
    source_type TEXT NOT NULL DEFAULT 'manual',
    source_id INTEGER NULL,
    reference_no TEXT NULL,
    narration TEXT NULL,
    total_debit REAL NOT NULL DEFAULT 0.00,
    total_credit REAL NOT NULL DEFAULT 0.00,
    is_reversed INTEGER DEFAULT 0,
    reversed_by_entry_id INTEGER NULL,
    created_by INTEGER NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(firm_id, entry_number),
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_journal_firm_date ON journal_entries(firm_id, entry_date);
CREATE INDEX IF NOT EXISTS idx_journal_source ON journal_entries(firm_id, source_type, source_id);

CREATE TABLE IF NOT EXISTS journal_lines (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    journal_entry_id INTEGER NOT NULL,
    firm_id INTEGER NOT NULL,
    account_id INTEGER NOT NULL,
    description TEXT NULL,
    debit REAL NOT NULL DEFAULT 0.00,
    credit REAL NOT NULL DEFAULT 0.00,
    party_id INTEGER NULL,
    FOREIGN KEY (journal_entry_id) REFERENCES journal_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (account_id) REFERENCES chart_of_accounts(id),
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_jlines_firm_acc ON journal_lines(firm_id, account_id);
CREATE INDEX IF NOT EXISTS idx_jlines_entry ON journal_lines(journal_entry_id);

-- [MYSQL]
CREATE TABLE IF NOT EXISTS `journal_entries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `entry_number` VARCHAR(100) NOT NULL,
    `entry_date` DATE NOT NULL,
    `source_type` VARCHAR(50) NOT NULL DEFAULT 'manual',
    `source_id` INT NULL,
    `reference_no` VARCHAR(100) NULL,
    `narration` TEXT NULL,
    `total_debit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `total_credit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `is_reversed` TINYINT(1) DEFAULT 0,
    `reversed_by_entry_id` INT NULL,
    `created_by` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_journal_firm_num` (`firm_id`, `entry_number`),
    INDEX `idx_journal_firm_date` (`firm_id`, `entry_date`),
    INDEX `idx_journal_source` (`firm_id`, `source_type`, `source_id`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `journal_lines` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `journal_entry_id` INT NOT NULL,
    `firm_id` INT NOT NULL,
    `account_id` INT NOT NULL,
    `description` VARCHAR(255) NULL,
    `debit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `credit` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `party_id` INT NULL,
    INDEX `idx_jlines_firm_acc` (`firm_id`, `account_id`),
    INDEX `idx_jlines_entry` (`journal_entry_id`),
    FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts`(`id`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
