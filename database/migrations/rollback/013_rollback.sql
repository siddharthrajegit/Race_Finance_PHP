DROP INDEX IF EXISTS idx_invoices_firm_client_uuid;
-- Note: SQLite <3.35 preserves nullable client_uuid column safely; MySQL drops index & column cleanly.
-- [MYSQL]
DROP INDEX `uq_inv_firm_client_uuid` ON `invoices`;
ALTER TABLE `invoices` DROP COLUMN `client_uuid`;
