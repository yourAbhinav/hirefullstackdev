-- DevHire Database Setup
-- Create database
CREATE DATABASE IF NOT EXISTS devhire CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE devhire;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fullName VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('developer', 'company', 'admin') DEFAULT 'developer',
    experience VARCHAR(50),
    techStack LONGTEXT,
    portfolio_url VARCHAR(255),
    company_name VARCHAR(255),
    company_description LONGTEXT,
    profile_image VARCHAR(255),
    bio LONGTEXT,
    verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firebase_uid VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    photo VARCHAR(255),
    provider VARCHAR(100) DEFAULT 'google',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_firebase_uid (firebase_uid),
    INDEX idx_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jobs Table
CREATE TABLE IF NOT EXISTS jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    company_id INT NOT NULL,
    description LONGTEXT NOT NULL,
    requirements LONGTEXT NOT NULL,
    salary_min INT,
    salary_max INT,
    experience_level VARCHAR(50),
    job_type ENUM('full-time', 'part-time', 'contract', 'freelance') DEFAULT 'full-time',
    work_mode ENUM('remote', 'hybrid', 'on-site') DEFAULT 'remote',
    location VARCHAR(255),
    tech_stack LONGTEXT,
    applications_count INT DEFAULT 0,
    status ENUM('active', 'inactive', 'closed') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_company (company_id),
    FULLTEXT INDEX ft_title_description (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Applications Table
CREATE TABLE IF NOT EXISTS applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fullName VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    experience VARCHAR(50),
    techStack VARCHAR(255),
    jobPosition VARCHAR(255),
    portfolio VARCHAR(255),
    message LONGTEXT,
    resume VARCHAR(255),
    job_id INT,
    user_id INT,
    status ENUM('pending', 'reviewing', 'shortlisted', 'rejected', 'hired') DEFAULT 'pending',
    rating INT,
    feedback LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_user (user_id),
    INDEX idx_job (job_id),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Testimonials Table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    text LONGTEXT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    user_type ENUM('developer', 'company') DEFAULT 'developer',
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_featured (featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Technologies Table
CREATE TABLE IF NOT EXISTS technologies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(255),
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Saved Jobs Table
CREATE TABLE IF NOT EXISTS saved_jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_save (user_id, job_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages Table (for communication)
CREATE TABLE IF NOT EXISTS messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255),
    message LONGTEXT NOT NULL,
    read_status BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_receiver (receiver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Logs Table
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(255),
    entity_type VARCHAR(100),
    entity_id INT,
    description LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Sample Technologies
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

-- Insert Sample Admin User (Password: admin123 - use password_hash in production)
INSERT INTO users (fullName, email, password, role, verified) VALUES
('Admin User', 'admin@devhire.com', '$2y$10$YourHashedPasswordHere', 'admin', TRUE);
