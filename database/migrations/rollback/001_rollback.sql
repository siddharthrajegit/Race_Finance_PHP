-- Rollback 001: Multi-Warehouse & Stock Transfers
-- [SQLITE]
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS stock_transfer_items;
DROP TABLE IF EXISTS stock_transfers;
DROP TABLE IF EXISTS warehouse_stock;
DROP TABLE IF EXISTS warehouses;

-- [MYSQL]
DROP TABLE IF EXISTS `stock_movements`;
DROP TABLE IF EXISTS `stock_transfer_items`;
DROP TABLE IF EXISTS `stock_transfers`;
DROP TABLE IF EXISTS `warehouse_stock`;
DROP TABLE IF EXISTS `warehouses`;
