-- Migration 003: Staff Accounts, Role-Based Access Control (RBAC), CA Access & Audit Logs

-- [SQLITE]
CREATE TABLE IF NOT EXISTS staff_accounts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    owner_user_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    firm_id INTEGER NOT NULL,
    role TEXT NOT NULL DEFAULT 'cashier',
    permissions_json TEXT NULL,
    is_active INTEGER DEFAULT 1,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, firm_id),
    FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_staff_user ON staff_accounts(user_id);
CREATE INDEX IF NOT EXISTS idx_staff_firm ON staff_accounts(firm_id);
CREATE INDEX IF NOT EXISTS idx_staff_owner ON staff_accounts(owner_user_id);

CREATE TABLE IF NOT EXISTS user_audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NULL,
    user_id INTEGER NULL,
    user_name TEXT NULL,
    staff_role TEXT NULL,
    action TEXT NOT NULL,
    target_type TEXT NULL,
    target_id TEXT NULL,
    details TEXT NULL,
    ip_address TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_user_audit_firm_date ON user_audit_logs(firm_id, created_at);
CREATE INDEX IF NOT EXISTS idx_user_audit_user ON user_audit_logs(user_id);

-- [MYSQL]
CREATE TABLE IF NOT EXISTS `staff_accounts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `owner_user_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `firm_id` INT NOT NULL,
    `role` VARCHAR(50) NOT NULL DEFAULT 'cashier',
    `permissions_json` TEXT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `notes` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_staff_user_firm` (`user_id`, `firm_id`),
    INDEX `idx_staff_user` (`user_id`),
    INDEX `idx_staff_firm` (`firm_id`),
    INDEX `idx_staff_owner` (`owner_user_id`),
    FOREIGN KEY (`owner_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NULL,
    `user_id` INT NULL,
    `user_name` VARCHAR(255) NULL,
    `staff_role` VARCHAR(50) NULL,
    `action` VARCHAR(100) NOT NULL,
    `target_type` VARCHAR(100) NULL,
    `target_id` VARCHAR(100) NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(50) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_audit_firm_date` (`firm_id`, `created_at`),
    INDEX `idx_user_audit_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
