Secure CRM for Small Businesses

A lightweight, production-ready CRM built with Pure PHP (MVC architecture), MySQL, HTML5, Vanilla CSS, and JavaScript.
Designed with strong security practices and optimized for live deployment.

✨ Features
Core Functionality

Authentication – Secure login/logout with session management and optional 2FA

Dashboard – Business overview with statistics and recent activity

Clients Management – Full CRUD with search and filtering

Deals / Orders – Sales pipeline tracking with status workflows

Follow-Ups – Schedule and manage client interactions

Invoices – PDF invoices with secure public payment links

Public Portal – Guest access to view and pay invoices

Payments – Integrated Stripe and PayPal support

Branding – Custom logo, colors, and company details

🔐 Production-Grade Security

Two-Factor Authentication (2FA) – TOTP-based protection

Brute-Force Protection – Rate limiting and temporary account lockout

Environment Variables – Secure secrets via .env

Session Hardening – ID rotation, inactivity timeout, SameSite=Strict cookies

Error Handling – No stack traces exposed; secure file logging

Security Logs – Centralized audit logs for suspicious activity

HTTP Security – HSTS, CSP, X-Frame-Options, X-Content-Type-Options

Upload Protection – Hardened upload directory (no script execution)

SQLi / XSS / CSRF Protection – Prepared statements and token validation

📋 Requirements

PHP 8.0+ (curl, gd, mbstring, openssl)

MySQL 5.7+ or MariaDB 10.4+

Apache with mod_rewrite and .htaccess

SSL Certificate (required for secure cookies and HSTS)

🚀 Installation
1. Clone the Repository
git clone https://github.com/Mounirhsinou/crm-platform.git

2. Database Setup
mysql -u root -p -e "CREATE DATABASE crm_db"
mysql -u root -p crm_db < database/schema.sql
mysql -u root -p crm_db < database/security_migration.sql
mysql -u root -p crm_db < database/seed.sql

3. Environment Configuration
cp .env.example .env


Update database credentials and API keys.
Set:

APP_DEBUG=false
ENFORCE_HTTPS=true

4. Permissions

Ensure these directories are writable:

logs/
public/uploads/

5. Access
https://your-domain.com/public

📁 Project Structure
CRM/
├── app/
│   ├── core/        # Router, Controller, Model, ErrorHandler
│   ├── controllers/
│   ├── helpers/
│   ├── models/
│   └── views/
├── config/
├── database/
├── logs/
├── public/
└── .env (git ignored)

🛡️ Security Configuration

Session Timeout: 30 minutes (configurable via .env)

Rate Limiting: 5 failed attempts → 15-minute lockout

2FA: Enforced once enabled (backup codes supported)

📝 License

MIT License — free to use, modify, and distribute.

👨‍💻 Developer
performance for small business environments @Mounirhsinou
