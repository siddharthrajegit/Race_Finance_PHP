# RACE FINANCE — Phase 0 Baseline & Environment Audit Report

- **Audit Date:** 2026-11-26
- **Git Branch:** `feature/zero-cost-extensions`
- **Rollback Anchor (`main`):** `6f8b925` (`Fix text contrast and hover state for sidebar firm switcher`)
- **Pre-Implementation Binary DB Backup:** `backups/pre_implementation_biller.db` (221,184 bytes)
- **Pre-Implementation JSON Export:** `backups/pre_implementation_full.json` (19,707 bytes)

## 1. Runtime & Hosting Environment Audit

| Item | Observed Value |
|------|----------------|
| **Local CLI PHP** | PHP 8.2.12 (`C:\xampp\php\php.exe`) |
| **Loaded Extensions** | `bcmath`, `curl`, `dom`, `exif`, `fileinfo`, `filter`, `hash`, `iconv`, `json`, `libxml`, `mbstring`, `mysqli`, `mysqlnd`, `openssl`, `pcre`, `PDO`, `pdo_mysql`, `pdo_sqlite`, `Phar`, `random`, `session`, `SimpleXML`, `xml`, `xmlreader`, `xmlwriter`, `zlib` |
| **Missing Extensions Locally** | `gd` is **not** enabled in local XAMPP PHP (`phpqrcode` PNG generation cannot be assumed; pure-PHP SVG/client-side QR generation is used instead so zero extension dependency exists). |
| **Primary DB (Configured in `.env`)** | SQLite (`data/biller.db`, WAL mode, foreign keys enabled). App also supports MySQL/MariaDB via PDO when `DB_CONNECTION=mysql`. |
| **Web Server / cPanel** | Apache (`mod_rewrite`, security headers, gzip, browser caching in `.htaccess`), `.cpanel.yml` rsync deploy, GitHub Actions FTP deploy on `main`. |
| **Frontend Framework** | Bootstrap 5.3.3, Bootstrap Icons 1.11.3, Flatpickr, html2pdf.bundle.min.js (all self-hosted in `public/vendor/`). |

## 2. Database Baseline Row Counts (`data/biller.db`)

| Table | Rows |
|-------|------|
| `users` | 5 (2 admins, 3 business owners) |
| `firms` | 18 |
| `parties` | 44 |
| `items` | 39 |
| `invoices` | 62 |
| `invoice_items` | 77 |
| `payments` | 35 |
| `document_sequences` | 19 |
| `firm_settings` | 2 |
| `admin_audit_logs` | 19 |
| `estimates` / `estimate_items` | 0 / 0 |
| `custom_units` | 0 |
| `google_tokens` | 0 |
| `platform_settings` | 0 |

## 3. Existing Features Preserved & Audited

| Capability | Status | Files / Notes |
|-----------|--------|---------------|
| GST & Non-GST invoicing + A4/PDF | Present | `Invoice.php`, `InvoiceController.php`, `views/invoices/{form,view,print_a4,download,bulk_print}.php` |
| Quotations / Estimates + HSN + discounts | Present | `Estimate.php`, `EstimateController.php`, `views/estimates/*` |
| Party ledgers (Dr/Cr/running balance/due/adv) | Present | `Party::getLedger()`, `views/parties/ledger.php` |
| Chronological FIFO settlement | Present | `Party::syncFIFOSettlement()` |
| Sales/purchase stock + multi-unit (`PCS/KG/BOX` + `sub_unit` + `custom_units`) | Present | `Item.php`, `CustomUnit.php` |
| Low-stock alerts | Present | `Item::getLowStock()`, dashboard widget, `/items` badge |
| Up to 2 firms per subscriber | Present | `Firm::create()` enforces limit 2 |
| JSON backup/restore + Google Drive | Present | `Backup.php`, `BackupController.php` |
| Basic PWA manifest & minimal SW | Partially Present | `public/manifest.json` & `public/sw.js` exist (static shell only; no IndexedDB offline billing sync yet) |
| WhatsApp share modal on invoice download | Partially Present | `views/invoices/download.php` has `wa.me` share modal; missing on invoice view & party ledger reminders |

## 4. Rollback Procedures

1. **Instant Feature Kill-Switch:** Flip any `ENABLE_*` constant in `config/config.php` or `.env` to `false`.
2. **Phase Rollback:** `git reset --hard phase-<N>-complete` and run matching `database/migrations/rollback/NNN_rollback.sql`.
3. **Full Restore to Pre-Implementation State:**
   ```bash
   git checkout main
   git branch -D feature/zero-cost-extensions
   cp backups/pre_implementation_biller.db data/biller.db
   ```
