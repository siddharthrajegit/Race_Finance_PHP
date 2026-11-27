-- Migration 007: Batch / Expiry Tracking & Serial / IMEI Tracking

-- [SQLITE]
CREATE TABLE IF NOT EXISTS item_batches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    item_id INTEGER NOT NULL,
    warehouse_id INTEGER NULL,
    batch_number TEXT NOT NULL,
    mfg_date DATE NULL,
    expiry_date DATE NULL,
    purchase_rate REAL DEFAULT 0.00,
    initial_qty REAL NOT NULL DEFAULT 0.00,
    remaining_qty REAL NOT NULL DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(firm_id, item_id, batch_number),
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_batches_firm_item_exp ON item_batches(firm_id, item_id, expiry_date);

CREATE TABLE IF NOT EXISTS item_serials (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    item_id INTEGER NOT NULL,
    warehouse_id INTEGER NULL,
    serial_number TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'in_stock',
    purchase_invoice_id INTEGER NULL,
    sale_invoice_id INTEGER NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(firm_id, serial_number),
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_serials_firm_item_status ON item_serials(firm_id, item_id, status);

-- [MYSQL]
CREATE TABLE IF NOT EXISTS `item_batches` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `item_id` INT NOT NULL,
    `warehouse_id` INT NULL,
    `batch_number` VARCHAR(100) NOT NULL,
    `mfg_date` DATE NULL,
    `expiry_date` DATE NULL,
    `purchase_rate` DECIMAL(15,2) DEFAULT 0.00,
    `initial_qty` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `remaining_qty` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_batch_firm_item` (`firm_id`, `item_id`, `batch_number`),
    INDEX `idx_batches_firm_item_exp` (`firm_id`, `item_id`, `expiry_date`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `item_serials` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `item_id` INT NOT NULL,
    `warehouse_id` INT NULL,
    `serial_number` VARCHAR(150) NOT NULL,
    `status` ENUM('in_stock','sold','returned','damaged') NOT NULL DEFAULT 'in_stock',
    `purchase_invoice_id` INT NULL,
    `sale_invoice_id` INT NULL,
    `notes` VARCHAR(255) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_serial_firm_num` (`firm_id`, `serial_number`),
    INDEX `idx_serials_firm_item_status` (`firm_id`, `item_id`, `status`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
