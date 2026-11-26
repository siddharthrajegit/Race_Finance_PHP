-- Migration 013: Offline Sync Idempotency UUID on Invoices

-- [SQLITE]
ALTER TABLE invoices ADD COLUMN client_uuid TEXT NULL;
CREATE UNIQUE INDEX IF NOT EXISTS idx_invoices_firm_client_uuid ON invoices(firm_id, client_uuid);

-- [MYSQL]
ALTER TABLE `invoices` ADD COLUMN `client_uuid` VARCHAR(64) NULL;
CREATE UNIQUE INDEX `uq_inv_firm_client_uuid` ON `invoices` (`firm_id`, `client_uuid`);
