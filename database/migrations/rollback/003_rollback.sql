-- Rollback 003: Staff Accounts, RBAC & Audit Logs
-- [SQLITE]
DROP TABLE IF EXISTS user_audit_logs;
DROP TABLE IF EXISTS staff_accounts;

-- [MYSQL]
DROP TABLE IF EXISTS `user_audit_logs`;
DROP TABLE IF EXISTS `staff_accounts`;
