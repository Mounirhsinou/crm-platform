# Secure CRM for Small Businesses

A lightweight, production-grade CRM system built with **Pure PHP (MVC architecture)**, MySQL, HTML5, Vanilla CSS, and JavaScript. Hardened for security and ready for live deployment.

![Version](https://img.shields.io/badge/version-1.1.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4.svg)
![Security](https://img.shields.io/badge/Security-Hardened-success.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## ✨ Features

### Core Functionality
- **Authentication** - Secure login/logout with session management and 2FA.
- **Dashboard** - Overview with statistics, charts, and recent activity.
- **Clients Management** - Full CRUD operations with search and filtering.
- **Deals/Orders** - Track sales pipeline with status workflows.
- **Follow-Ups** - Schedule, manage, and track client interactions.
- **Invoices** - Generate professional invoices with PDF support and payment links.
- **Public Portal** - Secure guest-facing portal for viewing and paying invoices.
- **Payment Integration** - Ready-to-use Stripe and PayPal integrations.
- **Branding** - Custom logo, colors, and business details per company.

### 🔐 Production-Grade Security
- **Two-Factor Authentication (2FA)** - TOTP support for admin accounts.
- **Brute-Force Protection** - Intelligent rate limiting and account lockout (5 attempts).
- **Environment Variables** - Secure secret management via `.env` files.
- **Session Hardening** - Periodic ID rotation, inactivity timeouts (30 min), and SameSite=Strict cookies.
- **Global Error Handling** - Technical stack traces are hidden from users and logged securely to files.
- **Security Audit Logs** - Centralized logging of all suspicious events (`logs/security.log`).
- **HTTP Hardening** - HSTS, CSP, X-Frame-Options, and X-Content-Type-Options enforced.
- **File Upload Security** - `.htaccess` hardened upload directory to prevent script execution.
- **SQLi/XSS/CSRF** - Zero-trust input validation, prepared statements, and token-based protection.

## 📋 Requirements

- **PHP** 8.0 or higher (ext-curl, ext-gd, ext-mbstring, ext-openssl required)
- **MySQL** 5.7+ or **MariaDB** 10.4+
- **Apache** with `mod_rewrite` and `.htaccess` support
- **SSL Certificate** (Required for secure cookies and HSTS)

## 🚀 Installation

### Step 1: Clone and Extract
```bash
git clone https://github.com/Mounirhsinou/crm-platform.git
# Move to your web server root 
```

### Step 2: Database Setup
1. Create a database named `crm_db`.
2. Import the schema:
```bash
mysql -u root -p crm_db < database/schema.sql
# Then import security migration and demo data if needed
mysql -u root -p crm_db < database/security_migration.sql
mysql -u root -p crm_db < database/seed.sql
```

### Step 3: Environment Configuration
1. Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
2. Open `.env` and fill in your database credentials and API keys.
3. Set `APP_DEBUG=false` for production use.
4. Set `ENFORCE_HTTPS=true` if your server has SSL.

### Step 4: Permissions
Ensure the `logs/` and `public/uploads/` directories are writable by the web server.

### Step 5: Access
Point your browser to `http://localhost/CRM/public` (or your domain).

## 📁 Project Structure

```
CRM/
├── app/
│   ├── core/            # Router, Base Controller, Model, ErrorHandler
│   ├── controllers/     # Application logic (Auth, Clients, Invoices, etc.)
│   ├── helpers/         # Security, RateLimiter, Validator, Session
│   ├── models/          # Database interaction classes
│   └── views/           # UI Templates (MVC Views)
├── config/              # Central configuration loader
├── database/            # SQL schemas and migrations
├── logs/                # Application and Security logs
├── public/              # Entry point, Assets, and Secure Uploads
└── .env                 # Environment secrets (Git ignored)
```

## 🛡️ Security Configuration

### Session Timeout
Configurable via `.env` (`SESSION_IDLE_TIMEOUT` in seconds). Default is 1800 (30 minutes).

### Rate Limiting
Failed login attempts are tracked by both Email and IP Address. After 5 failures, the account is locked for 15 minutes.

### 2FA Verification
Once enabled in user settings, 2FA is strictly enforced. Backup codes should be generated and stored securely.

## 📝 License
This project is open-source and available under the [MIT License](LICENSE).

## 👨‍💻 Developer
Built with ❤️ for secure small business management.
