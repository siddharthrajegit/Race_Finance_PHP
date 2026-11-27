-- Migration 002: Expense Management

-- [SQLITE]
CREATE TABLE IF NOT EXISTS expense_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    account_code TEXT NULL,
    is_default INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(firm_id, name),
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    expense_number TEXT NOT NULL,
    expense_date DATE NOT NULL,
    category TEXT NOT NULL,
    payee TEXT NULL,
    amount REAL NOT NULL DEFAULT 0.00,
    payment_mode TEXT DEFAULT 'cash',
    reference_no TEXT NULL,
    attachment_path TEXT NULL,
    notes TEXT NULL,
    created_by INTEGER NULL,
    updated_by INTEGER NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(firm_id, expense_number),
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_expenses_firm_date ON expenses(firm_id, expense_date);

-- [MYSQL]
CREATE TABLE IF NOT EXISTS `expense_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `account_code` VARCHAR(20) NULL,
    `is_default` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_expense_cat_firm` (`firm_id`, `name`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `expenses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `expense_number` VARCHAR(100) NOT NULL,
    `expense_date` DATE NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `payee` VARCHAR(255) NULL,
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `payment_mode` VARCHAR(50) DEFAULT 'cash',
    `reference_no` VARCHAR(100) NULL,
    `attachment_path` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `created_by` INT NULL,
    `updated_by` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_expense_firm_number` (`firm_id`, `expense_number`),
    INDEX `idx_expenses_firm_date` (`firm_id`, `expense_date`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
