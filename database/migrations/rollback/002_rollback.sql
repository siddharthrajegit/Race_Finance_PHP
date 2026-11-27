-- Rollback 002: Expense Management
-- [SQLITE]
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS expense_categories;

-- [MYSQL]
DROP TABLE IF EXISTS `expenses`;
DROP TABLE IF EXISTS `expense_categories`;
