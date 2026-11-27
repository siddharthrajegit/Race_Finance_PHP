# Third-Party Zero-Cost Dependency Register

All external libraries used by RACE FINANCE extensions are permissive open-source (MIT) assets bundled locally under `public/js/vendor/` with zero runtime licensing cost, zero telemetry, and full compatibility with cPanel shared hosting and offline PWA execution.

| Asset / Library | Version | License | Source / Upstream | Purpose | Local Path |
|---|---|---|---|---|---|
| `qrcode-generator` (`qrcode.min.js`) | 1.4.4 | MIT | `npm: qrcode-generator@1.4.4` (Kazuhiko Arase) | Dynamic UPI QR (`upi://pay?...`) SVG rendering on A4 invoices, thermal receipts, PDFs, and invoice view | `public/js/vendor/qrcode.min.js` |
| `JsBarcode` (`JsBarcode.all.min.js`) | 3.11.6 | MIT | `npm: jsbarcode@3.11.6` (Johan Lindell) | Browser-side barcode sticker preview & printing (`CODE128`, `EAN13`, `CODE39`) | `public/js/vendor/JsBarcode.all.min.js` |
| `SvgBarcode` (Internal Pure-PHP) | 1.0 | Project Proprietary | `core/SvgBarcode.php` | Deterministic pure-PHP Code128B `<svg>` generator requiring zero `gd` PHP extension | `core/SvgBarcode.php` |
| Existing App Vendor Libraries | Pre-installed | MIT / Permissive | Bootstrap 5.3, Bootstrap Icons, `html2pdf.js` | Base UI & PDF export preserved unchanged | `public/` |
