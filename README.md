# DevHire - Developer Hiring Platform

A modern, responsive, dark-themed developer hiring platform built with PHP 8+, MySQL, HTML5, CSS3, and JavaScript.

## 🎨 Features

✨ **Modern Design**
- Dark theme with purple/blue gradients
- Glassmorphism cards with smooth animations
- Fully responsive (mobile, tablet, desktop)
- Professional SaaS-style UI

🔐 **Authentication**
- Secure login/logout system
- Role-based access (Developer, Company, Admin)
- Session management
- Protected routes

💼 **Job Management**
- Browse and filter job listings
- Apply to positions
- Save favorite jobs
- Advanced search functionality

📊 **Admin Dashboard**
- Manage job listings
- Review applications
- User management
- Statistics and analytics

📱 **Responsive Design**
- Mobile-first approach
- Hamburger menu on mobile
- Adaptive layouts
- Touch-friendly interface

🗄️ **Database**
- MySQL with proper indexes
- Prepared statements for security
- Normalized schema
- Sample data included

## 📋 Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- 100MB disk space
- Modern web browser (Chrome, Firefox, Safari, Edge)

## 🚀 Installation & Setup

### 1. **Copy Project Files**
```
Copy the entire DevHire folder to your XAMPP/WAMP/LAMP htdocs directory:
C:\xampp\htdocs\  (Windows)
/Applications/XAMPP/htdocs/  (Mac)
/var/www/html/  (Linux)
```

### 2. **Create Database**

**Option A: Using phpMyAdmin**
1. Open phpMyAdmin (usually http://localhost/phpmyadmin)
2. Click on "Import" tab
3. Select the `database.sql` file from the DevHire folder
4. Click "Import"

**Option B: Using Command Line**
```bash
mysql -u root -p < /path/to/{PROJECT_DIR}/database.sql
```

**Option C: Manually**
1. Create a new database named `devhire`
2. Import the SQL file through phpMyAdmin or command line

### 3. **Configure Database Connection**

Edit `config/db.php` and update credentials if needed:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');  // Leave empty if no password
define('DB_NAME', 'devhire');
```

### 4. **Set File Permissions**

Create directories for uploads (if they don't exist):

```bash
mkdir -p uploads/resumes
mkdir -p logs
chmod 755 uploads/
chmod 755 logs/
```

### 4.5. **Configure Base URL (Optional)**

If deploying in a subfolder (e.g., http://localhost{APP_BASE_URL}/), set the APP_BASE_URL environment variable:

**Root domain deployment:** APP_BASE_URL = (empty or not set)
**Subfolder deployment:** APP_BASE_URL = /your-folder-name

Example for .env file or system environment:
```
APP_BASE_URL=/DevHire
```

### 5. **Start Your Server**

**XAMPP:**
1. Open XAMPP Control Panel
2. Start Apache and MySQL
3. Open browser: http://localhost{APP_BASE_URL}/

**WAMP:**
1. Start WAMP Server
2. Open browser: http://localhost{APP_BASE_URL}/

**LAMP (Linux):**
```bash
sudo systemctl start apache2
sudo systemctl start mysql
```

Then visit: http://localhost{APP_BASE_URL}/

### 6. **Access the Application**

- **Homepage:** http://localhost{APP_BASE_URL}/
- **Login:** http://localhost{APP_BASE_URL}/pages/login.php
- **Admin Dashboard:** http://localhost{APP_BASE_URL}/admin/dashboard.php

## 👤 Default Credentials

**Admin Login:**
- Email: `admin@devhire.com`
- Password: `admin123`

⚠️ **Important:** Change these credentials in production!

## 📁 Project Structure

```
DevHire/
├── index.php                 # Homepage
├── config/
│   └── db.php              # Database configuration
├── includes/
│   ├── header.php          # HTML head & Meta tags
│   ├── navbar.php          # Navigation bar
│   └── footer.php          # Footer section
├── pages/
│   ├── jobs.php            # Job listings
│   ├── developers.php      # Developer profiles
│   ├── apply.php           # Application form
│   ├── login.php           # Login page
│   ├── contact.php         # Contact form
│   ├── pricing.php         # Pricing plans
│   ├── how-it-works.php    # How it works page
│   └── testimonials.php    # Testimonials page
├── admin/
│   └── dashboard.php       # Admin dashboard
├── auth/
│   ├── login_handler.php   # Login processing
│   └── logout.php          # Logout handler
├── handlers/
│   └── contact_handler.php # Contact form processing
├── assets/
│   ├── css/
│   │   └── style.css       # Main stylesheet
│   ├── js/
│   │   └── main.js         # JavaScript
│   └── images/             # Image files
├── uploads/
│   └── resumes/            # Resume uploads
├── logs/
│   └── error.log           # Error logging
└── database.sql            # Database schema
```

## 🛠️ Features Overview

### 🏠 Homepage
- Hero section with CTA
- Quick apply form
- Featured job listings
- Why choose us section
- Hiring process timeline
- Statistics section
- Testimonials
- Technology stack
- Final CTA

### 💼 Jobs Page
- Search and filter jobs
- Job cards with details
- Save jobs functionality
- Pagination

### 👥 Developers Page
- Browse developer profiles
- Filter by specialization
- View developer details

### 📝 Application System
- Multi-field application form
- Resume upload
- Form validation
- Success/error messages
- Database storage

### 🔐 Admin Dashboard
- Applications management
- Job listings management
- User management
- Statistics dashboard
- Application status tracking
- Approve/reject functionality

## 🔒 Security Features

✓ SQL Injection Prevention - Prepared statements
✓ XSS Prevention - Input sanitization
✓ Session Management - Secure sessions
✓ Password Hashing - Use password_hash in production
✓ File Upload Validation - Type and size checking
✓ CSRF Protection - Token validation
✓ Error Logging - Secure error handling

## 🎨 Customization

### Change Color Scheme
Edit `assets/css/style.css` and modify the `:root` variables:

```css
:root {
    --primary: #7c3aed;        /* Purple */
    --secondary: #06b6d4;      /* Cyan */
    --accent: #f43f5e;         /* Red */
    --bg-primary: #0f172a;     /* Dark Blue */
}
```

### Add New Pages
1. Create new file in `pages/` folder
2. Include header and navbar at top
3. Include footer at bottom
4. Add navigation link in navbar

### Modify Database Schema
Edit `database.sql` and reimport into MySQL

## 🚀 Deployment

### For Production:

1. **Update Database Credentials**
   ```php
   // Use environment variables
   define('DB_HOST', getenv('DB_HOST'));
   define('DB_USER', getenv('DB_USER'));
   define('DB_PASSWORD', getenv('DB_PASSWORD'));
   define('DB_NAME', getenv('DB_NAME'));
   ```

2. **Enable HTTPS**
   - Get SSL certificate from Let's Encrypt
   - Configure in Apache/Nginx

3. **Security Headers**
   Add to .htaccess:
   ```
   Header set X-Content-Type-Options "nosniff"
   Header set X-Frame-Options "SAMEORIGIN"
   Header set X-XSS-Protection "1; mode=block"
   ```

4. **Environment Setup**
   Create `.env` file with:
   ```
   DB_HOST=your_db_host
   DB_USER=your_db_user
   DB_PASSWORD=your_db_password
   DB_NAME=devhire
   APP_ENV=production
   ```

5. **Change Default Credentials**
   - Update admin password in database
   - Remove sample data if needed

## 📧 Email Integration (Optional)

To enable email notifications:

1. Configure SMTP in `config/db.php`
2. Use PHPMailer library for emails
3. Send confirmation emails on application

## 🔍 Troubleshooting

**White screen or 500 error:**
- Check PHP error logs
- Verify database connection
- Check file permissions

**Database connection failed:**
- Verify MySQL is running
- Check credentials in `config/db.php`
- Ensure database exists

**Images not loading:**
- Check file paths
- Verify assets folder permissions

**Login not working:**
- Check browser cookies enabled
- Verify database has users table
- Check password hashing

## 📞 Support

For issues or questions:
1. Check error logs in `logs/error.log`
2. Review browser console for JavaScript errors
3. Check database connectivity

## 📄 License

This project is open source and available for personal and commercial use.

## 🎯 Future Enhancements

- Email notification system
- Payment gateway integration
- Video call interviews
- AI-powered resume parsing
- Advanced analytics
- Mobile app
- Social login
- Real-time notifications
- Two-factor authentication
- API endpoints

## 📝 Version History

**v1.0** - Initial Release
- Core features implemented
- Responsive design
- Admin dashboard
- Database setup

---

**Built with ❤️ using PHP 8+, MySQL, and modern web technologies**

Happy Hiring! 🚀
