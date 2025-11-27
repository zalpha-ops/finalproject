# 🚀 Eagle Flight School - Setup Guide

## Quick Start for GitHub Upload

### Step 1: Prepare Your Local Files

1. **Keep your current database connection working**
   - Your `db_connect_local.php` is already configured
   - This file will NOT be uploaded to GitHub (it's in .gitignore)

2. **Files created for you:**
   - ✅ `database_setup.sql` - Complete database structure
   - ✅ `.gitignore` - Protects sensitive files
   - ✅ `README.md` - Project documentation
   - ✅ `db_connect_local.php.example` - Template for others

### Step 2: Initialize Git Repository

Open your terminal/command prompt in your project folder:

```bash
# Initialize git repository
git init

# Add all files (gitignore will protect sensitive ones)
git add .

# Create first commit
git commit -m "Initial commit: Eagle Flight School Management System"
```

### Step 3: Create GitHub Repository

1. Go to https://github.com/new
2. Create a new repository named `eagle-flight-school`
3. **DO NOT** initialize with README (we already have one)
4. Click "Create repository"

### Step 4: Push to GitHub

Copy the commands from GitHub (they'll look like this):

```bash
# Add GitHub as remote
git remote add origin https://github.com/YOUR-USERNAME/eagle-flight-school.git

# Push to GitHub
git branch -M main
git push -u origin main
```

### Step 5: For Others to Use Your Project

When someone clones your repository, they should:

1. **Clone the repository**
   ```bash
   git clone https://github.com/YOUR-USERNAME/eagle-flight-school.git
   ```

2. **Import the database**
   - Open phpMyAdmin
   - Create new database or use existing
   - Import `database_setup.sql`

3. **Configure database connection**
   ```bash
   # Copy the example file
   cp db_connect_local.php.example db_connect_local.php
   ```
   - Edit `db_connect_local.php` with their credentials

4. **Start using the system**
   - Navigate to `http://localhost/eagle-flight-school/`
   - Login with admin/admin123

## 🔒 Security Checklist

Before uploading to GitHub, verify:

- ✅ `.gitignore` file exists
- ✅ `db_connect_local.php` is in .gitignore
- ✅ No passwords in committed files
- ✅ `uploads/` folder is ignored
- ✅ Test/debug files are ignored

## 📋 What Gets Uploaded to GitHub

**Included:**
- All PHP application files
- `database_setup.sql` (structure only, no sensitive data)
- `.gitignore`
- `README.md`
- `db_connect_local.php.example` (template)

**Excluded (Protected by .gitignore):**
- `db_connect_local.php` (your actual credentials)
- `uploads/` (user files)
- Test and debug files
- Backup files
- Vendor dependencies

## 🛠️ Troubleshooting

### "Permission denied" error
```bash
# On Mac/Linux, you may need to set permissions
chmod 755 uploads/
```

### Database connection fails after clone
- Make sure you copied and edited `db_connect_local.php`
- Verify MySQL is running
- Check database name matches

### Git push fails
```bash
# If you need to authenticate
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

## 📞 Need Help?

- Check the main README.md for detailed documentation
- Review database_setup.sql for database structure
- Ensure all prerequisites are installed

---

**Ready to upload?** Follow Step 2 above to get started! 🚀
