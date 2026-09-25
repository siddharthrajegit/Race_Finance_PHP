# RACE FINANCE — Billing & Inventory Management System (PHP Edition)

A high-performance, lightweight, and production-ready small business billing, GST invoicing, and inventory accounting platform built with pure PHP and MySQL/SQLite.

---

## 🚀 Key Features

- **GST & Non-GST Invoicing**: Professional Tax Invoices, Quotations, Sales & Purchase Bills with A4 print/PDF download.
- **Inventory & Stock Management**: Real-time stock tracking, automated deduction upon sales, low-stock alerts, and manual adjustments.
- **Customer & Supplier Ledgers**: Complete party transaction history, running debit/credit balances, and payment tracking.
- **Multi-Firm Support**: Manage up to 2 distinct business entities under a single subscriber account.
- **Platform Command Center (Admin)**: Full subscriber management, plan renewal, storage quota enforcement, database optimization, and audit logging.
- **Zero-Dependency Architecture**: Pure vanilla PHP (no heavyweight framework bloat) designed for 100% compatibility with standard cPanel/Apache hosting.
- **Dual Database Support**: Seamlessly runs on MySQL/MariaDB (default on cPanel) with automatic fallback to SQLite (`data/biller.db`).

---

## 📋 System Requirements

- **PHP**: 8.0 or higher
- **Extensions**: `pdo`, `pdo_mysql` (or `pdo_sqlite`), `mbstring`, `openssl`, `fileinfo`, `gd` (optional, for image processing)
- **Web Server**: Apache with `mod_rewrite` enabled (supported natively by `.htaccess`) or Nginx

---

## 🛠️ Quick Installation Guide

### 1. Clone the Repository
```bash
git clone https://github.com/siddharthrajegit/Race_Finance_PHP.git
cd Race_Finance_PHP
```

### 2. Configure Environment Variables
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Edit `.env` with your settings:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
SESSION_SECRET=your_random_64_character_secret_here

# cPanel MySQL Database Credentials
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpaneluser_biller
DB_USERNAME=cpaneluser_dbuser
DB_PASSWORD=your_strong_mysql_password
```
*(Note: If `DB_CONNECTION=sqlite` or MySQL credentials are left blank, the app will automatically use SQLite in `data/biller.db`).*

### 3. Import MySQL Schema (cPanel / phpMyAdmin)
1. Open **phpMyAdmin** in your hosting control panel.
2. Select your database.
3. Import `database/schema_mysql.sql`.

### 4. Default Seed Administrator
- **Phone (Login ID):** `9414223562`
- **Password:** `admin123`

*(You can immediately change this password or add new admins via the Admin Center at `/admin/users` or via CLI using `php create_user.php`).*

---

## 🔄 Automated Deployment to cPanel

This repository includes a pre-configured GitHub Actions workflow in `.github/workflows/deploy.yml` that automatically syncs code to your cPanel hosting on every `git push`.

To enable automated deployment:
1. Go to your GitHub repository **Settings** > **Secrets and variables** > **Actions**.
2. Add the following repository secrets:
   - `FTP_SERVER`: Your domain or FTP host (e.g. `ftp.yourdomain.com`).
   - `FTP_USERNAME`: Your cPanel FTP username.
   - `FTP_PASSWORD`: Your cPanel FTP password.

---

## 📁 Project Structure

```
├── .github/workflows/deploy.yml  # Automated CI/CD deployment to cPanel
├── .cpanel.yml                   # Native cPanel Git deployment configuration
├── .htaccess                     # Apache routing, security headers & HTTPS enforcement
├── config/                       # Application configuration & PDO connection manager
├── controllers/                  # MVC Controllers (Auth, Admin, Invoices, Firms, etc.)
├── core/                         # Core router, authentication guard, security, flash messages
├── database/                     # MySQL schema & database migration scripts
├── models/                       # Data models (User, Firm, Invoice, Item, Party, etc.)
├── public/                       # Static public assets (CSS, JS, logos, user uploads)
├── views/                        # PHP template views organized by feature
├── create_user.php               # CLI tool to provision users and admins from terminal
└── index.php                     # Front controller & request router
```

---

## 📄 License
Proprietary — All Rights Reserved.
