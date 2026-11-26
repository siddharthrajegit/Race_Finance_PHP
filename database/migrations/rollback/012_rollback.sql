-- Rollback note: SQLite <3.35 does not support DROP COLUMN easily; harmless nullable column if left intact.
ALTER TABLE `parties` DROP COLUMN `loyalty_points`;
