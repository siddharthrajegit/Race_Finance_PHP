-- Rollback note: SQLite <3.35 preserves nullable columns cleanly if left unused.
ALTER TABLE `items` DROP COLUMN `tracking_type`;
