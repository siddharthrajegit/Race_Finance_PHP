-- Migration 008: Basic Manufacturing / Bill of Materials (BOM) & Production Orders

-- [SQLITE]
CREATE TABLE IF NOT EXISTS boms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    finished_item_id INTEGER NOT NULL,
    bom_name TEXT NOT NULL,
    output_qty REAL NOT NULL DEFAULT 1.00,
    notes TEXT NULL,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(firm_id, finished_item_id),
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE,
    FOREIGN KEY (finished_item_id) REFERENCES items(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bom_components (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    bom_id INTEGER NOT NULL,
    component_item_id INTEGER NOT NULL,
    quantity_per_unit REAL NOT NULL DEFAULT 1.00,
    unit TEXT DEFAULT 'PCS',
    FOREIGN KEY (bom_id) REFERENCES boms(id) ON DELETE CASCADE,
    FOREIGN KEY (component_item_id) REFERENCES items(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS production_orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL,
    order_number TEXT NOT NULL,
    bom_id INTEGER NOT NULL,
    finished_item_id INTEGER NOT NULL,
    warehouse_id INTEGER NULL,
    production_date DATE NOT NULL,
    produced_qty REAL NOT NULL DEFAULT 1.00,
    total_component_cost REAL DEFAULT 0.00,
    unit_cost REAL DEFAULT 0.00,
    status TEXT DEFAULT 'completed',
    notes TEXT NULL,
    created_by INTEGER NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(firm_id, order_number),
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE,
    FOREIGN KEY (bom_id) REFERENCES boms(id),
    FOREIGN KEY (finished_item_id) REFERENCES items(id)
);

-- [MYSQL]
CREATE TABLE IF NOT EXISTS `boms` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `finished_item_id` INT NOT NULL,
    `bom_name` VARCHAR(150) NOT NULL,
    `output_qty` DECIMAL(12,2) NOT NULL DEFAULT 1.00,
    `notes` TEXT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_bom_firm_finished` (`firm_id`, `finished_item_id`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`finished_item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bom_components` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `bom_id` INT NOT NULL,
    `component_item_id` INT NOT NULL,
    `quantity_per_unit` DECIMAL(12,4) NOT NULL DEFAULT 1.0000,
    `unit` VARCHAR(50) DEFAULT 'PCS',
    FOREIGN KEY (`bom_id`) REFERENCES `boms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`component_item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `production_orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL,
    `order_number` VARCHAR(100) NOT NULL,
    `bom_id` INT NOT NULL,
    `finished_item_id` INT NOT NULL,
    `warehouse_id` INT NULL,
    `production_date` DATE NOT NULL,
    `produced_qty` DECIMAL(12,2) NOT NULL DEFAULT 1.00,
    `total_component_cost` DECIMAL(15,2) DEFAULT 0.00,
    `unit_cost` DECIMAL(15,2) DEFAULT 0.00,
    `status` VARCHAR(30) DEFAULT 'completed',
    `notes` TEXT NULL,
    `created_by` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_prod_firm_num` (`firm_id`, `order_number`),
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`bom_id`) REFERENCES `boms`(`id`),
    FOREIGN KEY (`finished_item_id`) REFERENCES `items`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
