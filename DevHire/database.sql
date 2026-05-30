-- DevHire Database Setup
CREATE DATABASE IF NOT EXISTS devhire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE devhire;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS saved_jobs;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS admin_audit_logs;
DROP TABLE IF EXISTS admin_permissions;
DROP TABLE IF EXISTS admin_accounts;
DROP TABLE IF EXISTS technologies;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS remember_me_tokens;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fullName VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    firebase_uid VARCHAR(255) DEFAULT NULL UNIQUE,
    provider VARCHAR(100) DEFAULT 'password',
    phone VARCHAR(20) DEFAULT NULL,
    role ENUM('developer', 'company') NOT NULL DEFAULT 'developer',
    experience VARCHAR(50) DEFAULT NULL,
    techStack LONGTEXT DEFAULT NULL,
    portfolio_url VARCHAR(255) DEFAULT NULL,
    company_name VARCHAR(255) DEFAULT NULL,
    company_description LONGTEXT DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    bio LONGTEXT DEFAULT NULL,
    verified BOOLEAN NOT NULL DEFAULT FALSE,
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_firebase_uid (firebase_uid),
    INDEX idx_provider (provider)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE admin_permissions (
    admin_id INT NOT NULL,
    permission VARCHAR(150) NOT NULL,
    PRIMARY KEY (admin_id, permission),
    CONSTRAINT fk_admin_permissions_admin FOREIGN KEY (admin_id) REFERENCES admin_accounts(id) ON DELETE CASCADE,
    INDEX idx_admin_permissions_permission (permission)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    entity VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_admin_audit_logs_admin FOREIGN KEY (admin_id) REFERENCES admin_accounts(id) ON DELETE CASCADE,
    INDEX idx_admin_audit_created_at (created_at),
    INDEX idx_admin_audit_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    company_id INT NOT NULL,
    description LONGTEXT NOT NULL,
    requirements LONGTEXT NOT NULL,
    salary_min INT DEFAULT NULL,
    salary_max INT DEFAULT NULL,
    experience_level VARCHAR(50) DEFAULT NULL,
    job_type ENUM('full-time', 'part-time', 'contract', 'freelance') NOT NULL DEFAULT 'full-time',
    work_mode ENUM('remote', 'hybrid', 'on-site') NOT NULL DEFAULT 'remote',
    location VARCHAR(255) DEFAULT NULL,
    tech_stack LONGTEXT DEFAULT NULL,
    applications_count INT NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive', 'closed') NOT NULL DEFAULT 'active',
    featured BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_jobs_company FOREIGN KEY (company_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_company (company_id),
    INDEX idx_featured (featured),
    FULLTEXT INDEX ft_title_description (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    experience VARCHAR(50) DEFAULT NULL,
    tech_stack VARCHAR(255) DEFAULT NULL,
    job_position VARCHAR(255) NOT NULL,
    portfolio_url VARCHAR(255) DEFAULT NULL,
    message LONGTEXT DEFAULT NULL,
    resume_path VARCHAR(255) DEFAULT NULL,
    job_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected', 'interview', 'reviewed', 'shortlisted') NOT NULL DEFAULT 'pending',
    admin_notes LONGTEXT DEFAULT NULL,
    interview_date DATETIME DEFAULT NULL,
    rating INT DEFAULT NULL,
    feedback LONGTEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_applications_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL,
    CONSTRAINT fk_applications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_user (user_id),
    INDEX idx_job (job_id),
    INDEX idx_email (email),
    INDEX idx_job_position (job_position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message LONGTEXT NOT NULL,
    status ENUM('new', 'read', 'archived') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_contact_messages_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_contact_status (status),
    INDEX idx_contact_email (email),
    INDEX idx_contact_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    text LONGTEXT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    user_type ENUM('developer', 'company') DEFAULT 'developer',
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_testimonials_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_featured (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE technologies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(255) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE saved_jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_saved_jobs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_saved_jobs_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_save (user_id, job_id),
    INDEX idx_saved_jobs_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message LONGTEXT NOT NULL,
    read_status BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_receiver (receiver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO technologies (name, icon, category) VALUES
('React', 'fab fa-react', 'Frontend'),
('Vue.js', 'fab fa-vuejs', 'Frontend'),
('Angular', 'fab fa-angular', 'Frontend'),
('Node.js', 'fab fa-node-js', 'Backend'),
('Python', 'fab fa-python', 'Backend'),
('PHP', 'fab fa-php', 'Backend'),
('Java', 'fab fa-java', 'Backend'),
('MongoDB', 'fab fa-database', 'Database'),
('PostgreSQL', 'fab fa-database', 'Database'),
('MySQL', 'fab fa-database', 'Database'),
('AWS', 'fab fa-aws', 'Cloud'),
('Docker', 'fab fa-docker', 'DevOps'),
('Kubernetes', 'fab fa-kubernetes', 'DevOps'),
('Git', 'fab fa-git-alt', 'Tools'),
('TypeScript', 'fas fa-code', 'Language'),
('JavaScript', 'fab fa-js-square', 'Language'),
('Tailwind CSS', 'fas fa-palette', 'Frontend'),
('Next.js', 'fas fa-arrow-right', 'Frontend');

INSERT INTO users (fullName, email, password, role, provider, verified, company_name, profile_image) VALUES
('Tech Startup Inc.', 'company@devhire.com', '$2y$10$PWYF1rNB5tal.PWDxQAEGObkRCNMiN/bYsp0STJ2bUDWia6bBKZEi', 'company', 'password', TRUE, 'Tech Startup Inc.', NULL);

SET @company_user_id := LAST_INSERT_ID();

INSERT INTO admin_accounts (name, email, password, role, status, last_login_at) VALUES
('Admin User', 'admin@devhire.com', '$2y$10$PWYF1rNB5tal.PWDxQAEGObkRCNMiN/bYsp0STJ2bUDWia6bBKZEi', 'super_admin', 'active', NULL);

INSERT INTO jobs (title, company_id, description, requirements, salary_min, salary_max, experience_level, job_type, work_mode, location, tech_stack, applications_count, status, featured) VALUES
('Full Stack Developer', @company_user_id, 'Build modern web applications with a product-focused engineering team.', 'React, Node.js, databases, and CI/CD basics.', 150000, 250000, '3-5 Years', 'full-time', 'remote', 'Remote', 'React, Node.js, MongoDB', 0, 'active', TRUE),
('Frontend Developer', @company_user_id, 'Create polished user interfaces for a fast-moving SaaS product.', 'Vue or React, TypeScript, responsive design.', 120000, 180000, '2-4 Years', 'full-time', 'hybrid', 'San Francisco, CA', 'Vue.js, TypeScript, Tailwind', 0, 'active', TRUE),
('Backend Developer', @company_user_id, 'Design scalable APIs and data flows for enterprise applications.', 'Python or PHP, PostgreSQL, Docker.', 130000, 210000, '3-6 Years', 'full-time', 'remote', 'Remote', 'Python, PostgreSQL, Docker', 0, 'active', TRUE),
('DevOps Engineer', @company_user_id, 'Own deployment pipelines and infrastructure automation.', 'Kubernetes, AWS, CI/CD, observability.', 140000, 220000, '4-7 Years', 'full-time', 'remote', 'Remote', 'Kubernetes, AWS, CI/CD', 0, 'active', TRUE);