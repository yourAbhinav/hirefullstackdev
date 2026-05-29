-- Admin Dashboard Database Schema for DevHire
-- This script adds all necessary tables for the advanced admin panel

USE devhire;

-- Admin Accounts Table (Enhanced)
CREATE TABLE IF NOT EXISTS admin_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'recruiter', 'editor', 'viewer') NOT NULL DEFAULT 'viewer',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    firebase_uid VARCHAR(255) DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    last_login_ip VARCHAR(45) DEFAULT NULL,
    failed_login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_firebase_uid (firebase_uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Permissions Table
CREATE TABLE IF NOT EXISTS admin_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    permission VARCHAR(100) NOT NULL,
    granted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    granted_by INT DEFAULT NULL,
    FOREIGN KEY (admin_id) REFERENCES admin_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES admin_accounts(id) ON DELETE SET NULL,
    UNIQUE KEY unique_admin_permission (admin_id, permission),
    INDEX idx_permission (permission)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Audit Logs Table
CREATE TABLE IF NOT EXISTS admin_audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT DEFAULT NULL,
    old_values JSON DEFAULT NULL,
    new_values JSON DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_accounts(id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Notifications Table
CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT DEFAULT NULL,
    type ENUM('info', 'success', 'warning', 'error', 'security') NOT NULL DEFAULT 'info',
    title VARCHAR(255) NOT NULL,
    message TEXT,
    action_url VARCHAR(255) DEFAULT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    read_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_accounts(id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_is_read (is_read),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Platform Settings Table
CREATE TABLE IF NOT EXISTS platform_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') NOT NULL DEFAULT 'string',
    category VARCHAR(50) NOT NULL DEFAULT 'general',
    description TEXT,
    is_public BOOLEAN NOT NULL DEFAULT FALSE,
    updated_by INT DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES admin_accounts(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics Data Table
CREATE TABLE IF NOT EXISTS analytics_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(15,2) NOT NULL,
    dimensions JSON DEFAULT NULL,
    recorded_date DATE NOT NULL,
    recorded_hour TINYINT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_metric_time (metric_name, recorded_date, recorded_hour, dimensions(255)),
    INDEX idx_metric_name (metric_name),
    INDEX idx_recorded_date (recorded_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default permissions
INSERT INTO admin_permissions (admin_id, permission) VALUES
(1, 'view_users'), (1, 'edit_users'), (1, 'delete_users'),
(1, 'view_applications'), (1, 'edit_applications'), (1, 'delete_applications'),
(1, 'view_resumes'), (1, 'download_resumes'),
(1, 'view_jobs'), (1, 'create_jobs'), (1, 'edit_jobs'), (1, 'delete_jobs'),
(1, 'view_settings'), (1, 'edit_settings'),
(1, 'view_analytics'), (1, 'view_logs'),
(1, 'manage_roles'), (1, 'manage_admins'),
(1, 'export_data'), (1, 'import_data'),
(1, 'enable_maintenance');

-- Insert default platform settings
INSERT INTO platform_settings (setting_key, setting_value, setting_type, category, description, is_public) VALUES
('site_title', 'DevHire', 'string', 'general', 'Website title', TRUE),
('site_description', 'Connect with talented full stack developers', 'string', 'general', 'Website description', TRUE),
('admin_email', 'admin@devhire.com', 'string', 'general', 'Admin contact email', FALSE),
('maintenance_mode', 'false', 'boolean', 'general', 'Enable maintenance mode', FALSE),
('allow_registrations', 'true', 'boolean', 'general', 'Allow new user registrations', FALSE),
('max_resume_size', '5242880', 'integer', 'uploads', 'Maximum resume file size in bytes', FALSE),
('allowed_resume_types', '["pdf","doc","docx"]', 'json', 'uploads', 'Allowed resume file types', FALSE),
('primary_color', '#4F46E5', 'string', 'theme', 'Primary theme color', TRUE),
('secondary_color', '#10B981', 'string', 'theme', 'Secondary theme color', TRUE),
('enable_notifications', 'true', 'boolean', 'notifications', 'Enable admin notifications', FALSE),
('notification_email', 'true', 'boolean', 'notifications', 'Send email notifications', FALSE),
('session_timeout', '3600', 'integer', 'security', 'Session timeout in seconds', FALSE),
('max_login_attempts', '5', 'integer', 'security', 'Maximum login attempts before lockout', FALSE),
('password_min_length', '8', 'integer', 'security', 'Minimum password length', FALSE),
('require_password_special', 'true', 'boolean', 'security', 'Require special characters in passwords', FALSE);

-- Create a default super admin account (password: Admin@123)
INSERT INTO admin_accounts (name, email, password, role, status) VALUES
('Super Admin', 'admin@devhire.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'active');
