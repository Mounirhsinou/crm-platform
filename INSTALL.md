# CRM - Quick Installation Guide

## Prerequisites Check
Before starting, ensure:
- ✅ XAMPP is installed
- ✅ Apache and MySQL are running in XAMPP Control Panel

## Step-by-Step Installation

### 1. Start XAMPP Services
Open XAMPP Control Panel and start:
- **Apache** (should show green)
- **MySQL** (should show green)

### 2. Create Database
Open your browser and go to: `http://localhost/phpmyadmin`

Click "New" and create a database named: `crm_db`

### 3. Import Database Schema
In phpMyAdmin:
- Select the `crm_db` database
- Click "Import" tab
- Click "Choose File"
- Navigate to: `C:\xampp\htdocs\CRM\database\schema.sql`
- Click "Go" at the bottom

### 4. Import Demo Data (Optional)
- Still in the Import tab
- Choose file: `C:\xampp\htdocs\CRM\database\seed.sql`
- Click "Go"

### 5. Access the Application
Open your browser and navigate to:
```
http://localhost/CRM/public
```

### 6. Login with Demo Account
If you imported the demo data:
- **Email:** demo@crm.com
- **Password:** demo123

---

## Troubleshooting

### "Database connection failed"
**Solution:**
1. Make sure MySQL is running in XAMPP
2. Verify database `crm_db` exists in phpMyAdmin
3. Check that you imported `schema.sql`

### "Page not found" or 404 errors
**Solution:**
1. Make sure you're accessing: `http://localhost/CRM/public` (not just `/CRM`)
2. Verify Apache is running in XAMPP
3. Check that `mod_rewrite` is enabled in Apache

### Session warnings
**Solution:** These have been fixed in the latest version. Refresh the page.

---

## Alternative: Command Line Installation

If you prefer using command line:

```bash
# Navigate to XAMPP MySQL bin directory
cd C:\xampp\mysql\bin

# Create database and import schema
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS crm_db"
mysql -u root -p crm_db < C:\xampp\htdocs\CRM\database\schema.sql

# Import demo data (optional)
mysql -u root -p crm_db < C:\xampp\htdocs\CRM\database\seed.sql
```

When prompted for password, just press Enter (default XAMPP has no password).

---

## Next Steps

After successful installation:
1. **Explore the Dashboard** - View statistics and recent activity
2. **Add Your First Client** - Click "Clients" → "Add Client"
3. **Create a Deal** - Link deals to clients
4. **Schedule Follow-ups** - Never miss a client interaction
5. **Generate Invoices** - Create professional invoices

---

## Need Help?

Check the main README.md file for:
- Full feature documentation
- Security best practices
- Customization guide
- Future enhancement roadmap
