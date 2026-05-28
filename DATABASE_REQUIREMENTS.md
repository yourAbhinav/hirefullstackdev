# Login System - Database Requirements & Setup

## Overview
The updated login system for DevHire now supports multiple authentication methods:
- **Users**: Email/password or Google OAuth
- **Admins**: Email/password or Google OAuth (with approval verification)
- **Companies**: Email/password or Google OAuth

## Critical Fixes Applied

### ✅ 1. Function Redeclaration Bug Fixed
**Issue**: `completeSessionLogin()` was defined twice
- Removed duplicate from `auth/login_handler.php` (line 229)
- Now only defined in `includes/helpers.php` (line 466)
- **Impact**: Prevented "Cannot redeclare" fatal error that broke all logins

### ✅ 2. Admin Password Login Added
**Issue**: Admin accounts could only use Google login
- Added email/password form to `admin/login.php`
- Implemented `admin_password` mode in `auth/login_handler.php`
- **Impact**: Admins can now use password authentication on the dedicated admin login page

### ✅ 3. User/Admin Login Separation
**Issue**: User login could potentially access admin accounts
- User login (`pages/login.php`) checks only `users` table
- Admin login (`admin/login.php`) checks only `admin_accounts` table
- Password login modes are role-specific (`admin_password` vs `password`)
- **Impact**: Clear separation prevents privilege escalation

## Required Database Tables & Columns

### 1. `users` (Existing - Updated)
```sql
id INT PRIMARY KEY AUTO_INCREMENT
fullName VARCHAR(255) NOT NULL
email VARCHAR(255) NOT NULL UNIQUE
password VARCHAR(255) NOT NULL
firebase_uid VARCHAR(255) DEFAULT NULL UNIQUE (ADDED)
provider VARCHAR(100) DEFAULT 'password'
role ENUM('developer', 'company') NOT NULL
phone VARCHAR(20)
experience VARCHAR(50)
techStack LONGTEXT
portfolio_url VARCHAR(255)
company_name VARCHAR(255)
company_description LONGTEXT
profile_image VARCHAR(255)
bio LONGTEXT
verified BOOLEAN DEFAULT FALSE
last_login_at TIMESTAMP NULL (ADDED)
created_at TIMESTAMP
updated_at TIMESTAMP
```

**New columns added by migration**:
- `firebase_uid` - Store Firebase UID for Google login
- `last_login_at` - Track user login activity

### 2. `admin_accounts` (New)
```sql
id INT PRIMARY KEY AUTO_INCREMENT
name VARCHAR(255) NOT NULL
email VARCHAR(255) NOT NULL UNIQUE
password VARCHAR(255) NOT NULL
role ENUM('super_admin', 'manager', 'reviewer') DEFAULT 'reviewer'
status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
last_login_at TIMESTAMP NULL DEFAULT NULL
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE
```

**Purpose**: Separate admin account management from user accounts

**Important**: 
- Migrated from old `admin_users` table (Firebase-only)
- Placeholder passwords set (admins must reset or use Google login)
- Only `active` admins can log in

### 3. `login_attempts` (New)
```sql
id INT PRIMARY KEY AUTO_INCREMENT
throttle_key VARCHAR(255) NOT NULL
email VARCHAR(255)
ip_address VARCHAR(45)
user_agent VARCHAR(255)
success BOOLEAN DEFAULT FALSE
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**Purpose**: Rate limiting and brute-force protection

**Usage**: Stored for failed login attempts, cleared on successful login

### 4. `remember_me_tokens` (New)
```sql
id INT PRIMARY KEY AUTO_INCREMENT
user_id INT NOT NULL
selector VARCHAR(64) NOT NULL UNIQUE
token_hash CHAR(64) NOT NULL
ip_address VARCHAR(45)
user_agent VARCHAR(255)
expires_at TIMESTAMP NOT NULL
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE
```

**Purpose**: Persistent login sessions ("Remember me" feature)

**Security**: Uses selector + token_hash pattern (tokens not stored in plain text)

### 5. `admin_permissions` (New)
```sql
admin_id INT NOT NULL
permission VARCHAR(150) NOT NULL
PRIMARY KEY (admin_id, permission)
CONSTRAINT fk_admin_permissions_admin FOREIGN KEY (admin_id) REFERENCES admin_accounts(id)
```

**Purpose**: Fine-grained permission control for admins

## Database Setup Instructions

### Option 1: Run Migration Script (Recommended)
```bash
cd /path/to/DevHire
php scripts/migrate_schema.php
```

This will:
- ✓ Create missing tables (`login_attempts`, `remember_me_tokens`, `admin_accounts`, `admin_permissions`)
- ✓ Add missing columns to `users` table
- ✓ Migrate data from `admin_users` to `admin_accounts` if present
- ○ Skip if tables/columns already exist

### Option 2: Manual SQL
If you prefer to run SQL manually, execute the relevant sections from `database.sql`:

```sql
-- Create login_attempts table
CREATE TABLE login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    throttle_key VARCHAR(255) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    success BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_throttle_key (throttle_key),
    INDEX idx_login_created_at (created_at),
    INDEX idx_login_email (email),
    INDEX idx_login_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create remember_me_tokens table
CREATE TABLE remember_me_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    selector VARCHAR(64) NOT NULL UNIQUE,
    token_hash CHAR(64) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_remember_me_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_remember_user (user_id),
    INDEX idx_remember_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_accounts table (replaces admin_users)
CREATE TABLE admin_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'manager', 'reviewer') NOT NULL DEFAULT 'reviewer',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admin_email (email),
    INDEX idx_admin_role (role),
    INDEX idx_admin_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_permissions table
CREATE TABLE admin_permissions (
    admin_id INT NOT NULL,
    permission VARCHAR(150) NOT NULL,
    PRIMARY KEY (admin_id, permission),
    CONSTRAINT fk_admin_permissions_admin FOREIGN KEY (admin_id) REFERENCES admin_accounts(id) ON DELETE CASCADE,
    INDEX idx_admin_permissions_permission (permission)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add missing columns to users table
ALTER TABLE users ADD COLUMN firebase_uid VARCHAR(255) DEFAULT NULL UNIQUE;
ALTER TABLE users ADD INDEX idx_firebase_uid (firebase_uid);
ALTER TABLE users ADD COLUMN last_login_at TIMESTAMP NULL DEFAULT NULL;
```

## Creating Admin Accounts

### Method 1: Direct Database Insert
```sql
INSERT INTO admin_accounts (name, email, password, role, status)
VALUES (
    'John Admin',
    'admin@devhire.com',
    -- Password hash (use bcrypt, e.g., password: 'SecurePass123!')
    '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890ABCDEFG',
    'super_admin',
    'active'
);
```

To generate a bcrypt password hash, use:
```bash
php -r "echo password_hash('YourPassword123!', PASSWORD_BCRYPT);"
```

### Method 2: Create Admin Web UI (Future)
A web-based admin creation interface is recommended for production.

## Environment Variables (Production)

Ensure these are set in `.env` for production:
```env
APP_ENV=production
DB_HOST=your-db-server.com
DB_USER=devhire_user
DB_PASS=SecurePassword123!
DB_NAME=devhire
DB_PORT=3306
```

See `config/db.php` for details on how production mode enforces these variables.

## Authentication Flow

### User Login (pages/login.php)
1. User enters email + password OR clicks "Sign in with Google"
2. If password: checked against `users` table
3. If Google: verified against Firebase, then synced to `users` table
4. Success: Session created with `user_id`, `user_role`, etc.

### Admin Login (admin/login.php)
1. Admin enters email + password OR clicks "Continue with Google"
2. If password: checked against `admin_accounts` table
3. If Google: verified against Firebase, must match `admin_accounts` email
4. Admin must have `status = 'active'` to proceed
5. Success: Session created with `admin_id`, `admin_role`, etc.

### Session Keys

**User Session**:
- `user_id` - User ID from `users` table
- `user_name` - User display name
- `user_email` - User email
- `user_role` - 'developer' or 'company'
- `user_provider` - 'password', 'google', etc.
- `user_photo` - Profile image URL
- `user_firebase_uid` - Firebase UID if Google login

**Admin Session**:
- `admin_id` - Admin ID from `admin_accounts` table
- `admin_name` - Admin display name
- `admin_email` - Admin email
- `admin_role` - 'super_admin', 'manager', or 'reviewer'
- `admin_provider` - 'password', 'google', etc.
- `admin_photo` - Profile image URL
- `admin_firebase_uid` - Firebase UID if Google login

## Troubleshooting

### "Table doesn't exist" Error
**Fix**: Run the migration script
```bash
php scripts/migrate_schema.php
```

### Admin Login Fails with "Not Approved"
**Cause**: Admin account status is not 'active' or email doesn't match
**Fix**: Check `admin_accounts` table:
```sql
SELECT * FROM admin_accounts WHERE email = 'admin@example.com';
```
Ensure `status = 'active'`.

### Google Login Fails
**Cause**: Email in Firebase doesn't match database email
**Fix**: Ensure the email used for Google login matches exactly in:
- `users` table (for user login)
- `admin_accounts` table (for admin login)

### "Cannot redeclare completeSessionLogin()" Error
**Cause**: Duplicate function definition
**Fix**: Already fixed in this update. Ensure you're running the latest code.

## Security Checklist

- ✅ Passwords hashed with bcrypt (PASSWORD_BCRYPT)
- ✅ CSRF tokens verified on all forms
- ✅ Login attempts tracked and throttled
- ✅ Sessions regenerated on login
- ✅ User/Admin role separation enforced
- ✅ Firebase API key restrictions configured (see assets/js/firebase-config.js)
- ✅ Secure database connection with proper charset
- ✅ Production mode requires all env vars

## Database File Location
- Schema: `database.sql`
- Migration script: `scripts/migrate_schema.php`
