-- Migration 010: Public Shareable Online Product Catalogue

-- [SQLITE]
CREATE TABLE IF NOT EXISTS catalogue_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER NOT NULL UNIQUE,
    is_published INTEGER DEFAULT 0,
    slug TEXT NOT NULL UNIQUE,
    headline TEXT NULL,
    description TEXT NULL,
    whatsapp_number TEXT NULL,
    show_prices INTEGER DEFAULT 1,
    show_stock_badge INTEGER DEFAULT 1,
    hide_out_of_stock INTEGER DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (firm_id) REFERENCES firms(id) ON DELETE CASCADE
);

-- [MYSQL]
CREATE TABLE IF NOT EXISTS `catalogue_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firm_id` INT NOT NULL UNIQUE,
    `is_published` TINYINT(1) DEFAULT 0,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `headline` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `whatsapp_number` VARCHAR(30) NULL,
    `show_prices` TINYINT(1) DEFAULT 1,
    `show_stock_badge` TINYINT(1) DEFAULT 1,
    `hide_out_of_stock` TINYINT(1) DEFAULT 0,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`firm_id`) REFERENCES `firms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
