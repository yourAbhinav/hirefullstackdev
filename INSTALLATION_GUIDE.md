# DevHire - Installation Guide

## Complete Step-by-Step Setup Instructions

### System Requirements
- PHP 8.0 or higher
- MySQL 5.7 or higher
- 100MB free disk space
- Apache with mod_rewrite
- Modern web browser

### Installation Steps

#### Step 1: Extract and Copy Files
1. Download/clone the DevHire project
2. Copy the entire `DevHire` folder to:
   - **Windows:** `C:\xampp\htdocs\`
   - **Mac:** `/Applications/XAMPP/htdocs/`
   - **Linux:** `/var/www/html/`

#### Step 2: Create Database

**Method A: Using phpMyAdmin (Easiest)**
1. Open your browser and go to `http://localhost/phpmyadmin`
2. Click the "Import" tab
3. Click "Choose File" and select `database.sql` from the DevHire folder
4. Click the "Import" button
5. The database and tables will be created automatically

**Method B: Using MySQL Command Line**
```bash
# Open terminal/command prompt
mysql -u root -p

# At MySQL prompt, paste this:
CREATE DATABASE IF NOT EXISTS devhire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE devhire;

# Then import the SQL file:
SOURCE /path/to/{PROJECT_DIR}/database.sql;
```

**Method C: Manual Setup in phpMyAdmin**
1. Create new database named `devhire`
2. Go to "Import" tab
3. Select `database.sql` file
4. Click Import

#### Step 3: Configure Database Connection

1. Open `{PROJECT_DIR}/config/db.php` in a text editor
2. Update database credentials if needed:

```php
define('DB_HOST', 'localhost');      // Usually localhost
define('DB_USER', 'root');           // MySQL username
define('DB_PASSWORD', '');           // MySQL password (blank if none)
define('DB_NAME', 'devhire');        // Database name
```

**Common credentials:**
- **XAMPP:** User=root, Password=(blank)
- **WAMP:** User=root, Password=(blank)
- **cPanel/Hosting:** Check your hosting control panel

#### Step 4: Create Required Directories

If they don't exist, create:
```bash
mkdir uploads/
mkdir uploads/resumes/
mkdir logs/
```

**Windows:** Right-click → New Folder

#### Step 5: Set File Permissions (Linux/Mac)

```bash
chmod 755 /path/to/{PROJECT_DIR}/uploads/
chmod 755 /path/to/{PROJECT_DIR}/logs/
chmod 755 /path/to/{PROJECT_DIR}/uploads/resumes/
```

#### Step 5.5: Configure Base URL (Optional)

If deploying in a subfolder (e.g., http://localhost{APP_BASE_URL}/), set the APP_BASE_URL environment variable:

**Root domain deployment:** APP_BASE_URL = (empty or not set)
**Subfolder deployment:** APP_BASE_URL = /your-folder-name

Example for .env file or system environment:
```
APP_BASE_URL=/DevHire
```

#### Step 6: Start Your Server

**XAMPP (Windows/Mac):**
1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Click "Start" next to MySQL
4. Open browser: `http://localhost{APP_BASE_URL}/`

**WAMP (Windows):**
1. Click system tray icon → Start WampServer
2. Wait for it to turn green
3. Open browser: `http://localhost{APP_BASE_URL}/`

**LAMP (Linux):**
```bash
sudo systemctl start apache2
sudo systemctl start mysql
# Open browser: http://localhost{APP_BASE_URL}/
```

**MAMP (Mac):**
1. Open Applications → MAMP
2. Click "Start Servers"
3. Open browser: `http://localhost:8888{APP_BASE_URL}/`

#### Step 7: Access the Application

After server is running, visit:
- **Homepage:** `http://localhost{APP_BASE_URL}/`
- **Jobs:** `http://localhost{APP_BASE_URL}/pages/jobs.php`
- **Apply:** `http://localhost{APP_BASE_URL}/pages/apply.php`
- **Login:** `http://localhost{APP_BASE_URL}/pages/login.php`
- **Admin:** `http://localhost{APP_BASE_URL}/admin/dashboard.php`

## Testing the Installation

### Test Database Connection
1. Go to `http://localhost{APP_BASE_URL}/`
2. If you see the homepage design properly, database is connected
3. Try clicking on "Apply Now" - should open the form

### Test Admin Panel
1. Go to `http://localhost{APP_BASE_URL}/pages/login.php`
2. Use credentials:
   - Email: `admin@devhire.com`
   - Password: `admin123`
3. Should see admin dashboard with statistics

### Test Form Submission
1. Go to `http://localhost{APP_BASE_URL}/pages/apply.php`
2. Fill in the form and submit
3. Should see success message
4. In admin panel, should see the application

## Troubleshooting

### "Cannot connect to database"
- **Solution:** Check MySQL is running and credentials are correct
- **Windows:** Check XAMPP/WAMP control panel shows green for MySQL
- **Verify:** Open phpMyAdmin - if it works, database is fine

### "Page not found (404)"
- **Solution:** Check .htaccess file exists in {PROJECT_DIR} folder
- **For WAMP/XAMPP:** Make sure mod_rewrite is enabled
- **Test:** Go directly to `http://localhost{APP_BASE_URL}/index.php`

### "White screen or errors"
- **Solution:** Check PHP error log
- **File:** Check `logs/error.log` in {PROJECT_DIR} folder
- **Browser:** Press F12, check Console tab for errors

### "Uploads not working"
- **Solution:** Check permissions on uploads folder
- **Windows:** Right-click folder → Properties → Security → Edit
- **Linux/Mac:** `chmod 755 uploads/`

### "Can't login with admin credentials"
- **Solution:** Reimport database.sql
- **Verify:** Check users table has admin user: `SELECT * FROM users;`

### "CSS/JS not loading"
- **Solution:** Clear browser cache
- **Chrome:** Ctrl+Shift+Delete
- **Firefox:** Ctrl+Shift+Delete
- **Safari:** Cmd+Shift+Delete

## Next Steps After Installation

1. **Change Admin Password**
   - Login as admin
   - Go to admin panel
   - Update password in database

2. **Customize Design**
   - Edit `assets/css/style.css` to change colors
   - Update content in pages

3. **Add Real Data**
   - Create job listings
   - Add testimonials
   - Customize company information

4. **Enable Email**
   - Install PHPMailer
   - Configure SMTP settings
   - Update contact form handler

5. **Setup SSL Certificate** (for production)
   - Get from Let's Encrypt
   - Configure Apache/Nginx
   - Update to HTTPS

## File Structure

```
DevHire/
├── index.php                # Main homepage
├── config/
│   └── db.php              # Database config (EDIT THIS)
├── pages/                  # All page files
├── admin/
│   └── dashboard.php       # Admin panel
├── assets/
│   ├── css/style.css      # Styling
│   └── js/main.js         # JavaScript
├── database.sql           # Import this (STEP 2)
├── uploads/               # Resume uploads
├── logs/                  # Error logs
└── README.md             # Full documentation
```

## Support & Resources

- **PHP Official:** https://www.php.net/
- **MySQL Official:** https://www.mysql.com/
- **Apache Docs:** https://httpd.apache.org/docs/
- **XAMPP:** https://www.apachefriends.org/
- **Let's Encrypt:** https://letsencrypt.org/

## Production Deployment

### Before Going Live:

1. **Update Credentials**
   - Change admin password
   - Use environment variables for DB credentials

2. **Enable HTTPS**
   - Get SSL certificate
   - Redirect HTTP to HTTPS

3. **Security**
   - Update .htaccess with security headers
   - Enable firewall rules
   - Regular backups

4. **Optimization**
   - Enable caching
   - Minify CSS/JS
   - Optimize images

5. **Monitoring**
   - Set up error tracking
   - Monitor uptime
   - Regular logs review

---

**Congratulations! DevHire is now installed and ready to use! 🎉**

If you encounter any issues, check the troubleshooting section or review error logs in the `logs/` directory.
