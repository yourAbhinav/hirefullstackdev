# Quick Start Guide - DevHire

## ⚡ Quick Setup (5 Minutes)

### Step 1: Import Database
```sql
1. Open phpMyAdmin
2. Click "Import"
3. Select: DevHire/database.sql
4. Click "Import"
```

### Step 2: Update Database Config (Optional)
If your database credentials are different, edit `config/db.php`:
```php
define('DB_USER', 'your_username');
define('DB_PASSWORD', 'your_password');
```

### Step 3: Start Server
- XAMPP: Start Apache & MySQL
- WAMP: Start WampServer
- LAMP: `sudo systemctl start apache2 mysql`

### Step 4: Open in Browser
```
http://localhost{APP_BASE_URL}/
```

## Base URL / Deployment Path

If deploying in a subfolder (e.g., http://localhost{APP_BASE_URL}/), set the APP_BASE_URL environment variable:

**Root domain deployment:** APP_BASE_URL = (empty or not set)
**Subfolder deployment:** APP_BASE_URL = /your-folder-name

Example for .env file or system environment:
```
APP_BASE_URL=/DevHire
```

## 🔐 Login Credentials

**Admin Account:**
- Email: admin@devhire.com
- Password: admin123

## 📂 Folder Structure

```
DevHire/
├── index.php              ← Start here
├── pages/                 ← All pages
├── config/db.php         ← Database config
├── assets/               ← CSS, JS, images
├── admin/dashboard.php   ← Admin panel
└── database.sql          ← Import this
```

## 🎯 Pages to Visit

1. Homepage: `/index.php`
2. Jobs: `/pages/jobs.php`
3. Apply: `/pages/apply.php`
4. Login: `/pages/login.php`
5. Admin: `/admin/dashboard.php` (use admin credentials)
6. Contact: `/pages/contact.php`
7. Pricing: `/pages/pricing.php`

## ✅ Features Included

✓ Modern dark theme design
✓ Responsive (mobile/tablet/desktop)
✓ 8+ complete pages
✓ Admin dashboard
✓ Job listings & filtering
✓ Application system
✓ Database setup
✓ Contact form
✓ Authentication system
✓ Testimonials section

## 🛠️ Troubleshooting

**Cannot connect to database?**
- Check MySQL is running
- Verify credentials in config/db.php
- Ensure database exists

**Pages look broken?**
- Clear browser cache (Ctrl+Shift+Delete)
- Check console for errors (F12)

**Login doesn't work?**
- Use admin@devhire.com / admin123
- Check browser cookies are enabled

## 📧 Contact Form

Contact form submissions are handled but not emailed by default. To enable email:

1. Install PHPMailer: `composer require phpmailer/phpmailer`
2. Update `handlers/contact_handler.php` with mail code

## 🚀 Next Steps

1. **Customize Colors:** Edit `assets/css/style.css` :root variables
2. **Add Users:** Use admin dashboard
3. **Create Jobs:** In admin panel (not visible yet - ready for phase 2)
4. **Configure Email:** Set up SMTP for notifications
5. **Deploy:** Upload to production server

## 💡 Pro Tips

- Use modern browser (Chrome, Firefox, Safari, Edge)
- Test on mobile too (responsive design included)
- Check console (F12) for any JavaScript errors
- Database schema is production-ready
- All forms use security best practices

---

**Ready to go! Happy hiring! 🎉**
