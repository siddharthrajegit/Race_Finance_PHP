-- Migration 001: Multi-Warehouse, Stock Transfers & Auditable Stock Movements (Valuation)

-- [SQLITE]
CREATE TABLE IF NOT EXISTS warehouses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    code TEXT NULL,
    address TEXT NULL,
    is_default INTEGER DEFAULT 0,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_warehouses_firm ON warehouses(firm_id);

CREATE TABLE IF NOT EXISTS warehouse_stock (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    warehouse_id INTEGER NOT NULL,
    item_id INTEGER NOT NULL,
    quantity REAL DEFAULT 0.00,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(warehouse_id, item_id),
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_warehouse_stock_firm_item ON warehouse_stock(firm_id, item_id);

CREATE TABLE IF NOT EXISTS stock_transfers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    transfer_number TEXT NOT NULL,
    transfer_date DATE NOT NULL,
    source_warehouse_id INTEGER NOT NULL,
    dest_warehouse_id INTEGER NOT NULL,
    status TEXT DEFAULT 'completed',
    notes TEXT NULL,
    created_by INTEGER NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(firm_id, transfer_number),
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE,
    FOREIGN KEY (source_warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (dest_warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_stock_transfers_firm ON stock_transfers(firm_id, transfer_date);

CREATE TABLE IF NOT EXISTS stock_transfer_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transfer_id INTEGER NOT NULL,
    item_id INTEGER NOT NULL,
    item_name TEXT NOT NULL,
    unit TEXT DEFAULT 'PCS',
    quantity REAL NOT NULL,
    FOREIGN KEY (transfer_id) REFERENCES stock_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id)
);

CREATE TABLE IF NOT EXISTS stock_movements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    warehouse_id INTEGER NULL,
    item_id INTEGER NOT NULL,
    movement_type TEXT NOT NULL,
    quantity_delta REAL NOT NULL,
    unit_cost REAL DEFAULT 0.00,
    total_cost REAL DEFAULT 0.00,
    remaining_qty REAL DEFAULT 0.00,
    source_type TEXT NULL,
    source_id INTEGER NULL,
    batch_id INTEGER NULL,
    notes TEXT NULL,
    created_by INTEGER NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_stock_movements_firm_item ON stock_movements(firm_id, item_id, created_at);
CREATE INDEX IF NOT EXISTS idx_stock_movements_source ON stock_movements(source_type, source_id);

ALTER TABLE invoices ADD COLUMN warehouse_id INTEGER NULL;

-- [MYSQL]
CREATE TABLE IF NOT EXISTS `warehouses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(50) NULL,
    `address` TEXT NULL,
    `is_default` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_warehouses_firm` (`firm_id`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `warehouse_stock` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `warehouse_id` INT NOT NULL,
    `item_id` INT NOT NULL,
    `quantity` DECIMAL(15,2) DEFAULT 0.00,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_warehouse_item` (`warehouse_id`, `item_id`),
    INDEX `idx_warehouse_stock_firm_item` (`firm_id`, `item_id`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stock_transfers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `transfer_number` VARCHAR(100) NOT NULL,
    `transfer_date` DATE NOT NULL,
    `source_warehouse_id` INT NOT NULL,
    `dest_warehouse_id` INT NOT NULL,
    `status` VARCHAR(50) DEFAULT 'completed',
    `notes` TEXT NULL,
    `created_by` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_transfer_firm_number` (`firm_id`, `transfer_number`),
    INDEX `idx_stock_transfers_firm` (`firm_id`, `transfer_date`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`source_warehouse_id`) REFERENCES `warehouses`(`id`),
    FOREIGN KEY (`dest_warehouse_id`) REFERENCES `warehouses`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stock_transfer_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `transfer_id` INT NOT NULL,
    `item_id` INT NOT NULL,
    `item_name` VARCHAR(255) NOT NULL,
    `unit` VARCHAR(50) DEFAULT 'PCS',
    `quantity` DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (`transfer_id`) REFERENCES `stock_transfers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stock_movements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `warehouse_id` INT NULL,
    `item_id` INT NOT NULL,
    `movement_type` VARCHAR(50) NOT NULL,
    `quantity_delta` DECIMAL(15,2) NOT NULL,
    `unit_cost` DECIMAL(15,2) DEFAULT 0.00,
    `total_cost` DECIMAL(15,2) DEFAULT 0.00,
    `remaining_qty` DECIMAL(15,2) DEFAULT 0.00,
    `source_type` VARCHAR(50) NULL,
    `source_id` INT NULL,
    `batch_id` INT NULL,
    `notes` TEXT NULL,
    `created_by` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_stock_movements_firm_item` (`firm_id`, `item_id`, `created_at`),
    INDEX `idx_stock_movements_source` (`source_type`, `source_id`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `invoices` ADD COLUMN `warehouse_id` INT NULL;
