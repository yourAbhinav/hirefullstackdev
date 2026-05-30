<?php
/**
 * Database Migration Script
 * 
 * This script migrates the database schema from the old format to the new authentication system.
 * It safely creates missing tables and adds missing columns without dropping existing data.
 * 
 * Usage: php scripts/migrate_schema.php [--force]
 */

require_once __DIR__ . '/../config/db.php';

$force = in_array('--force', $argv, true);

if (php_sapi_name() !== 'cli') {
	http_response_code(403);
	echo "This script can only be run from the command line.";
	exit(1);
}

// Track migration status
$migrations = [];

echo "\n=== DevHire Database Schema Migration ===\n\n";

// Migration 1: Create login_attempts table
try {
	$conn->query('DESCRIBE login_attempts');
	$migrations[] = ['login_attempts', 'skip', 'Table already exists'];
} catch (Throwable $e) {
	try {
		$conn->query(<<<SQL
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
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
		SQL);
		$migrations[] = ['login_attempts', 'created', 'Table created successfully'];
	} catch (Throwable $ex) {
		$migrations[] = ['login_attempts', 'error', $ex->getMessage()];
	}
}

// Migration 2: Create remember_me_tokens table
try {
	$conn->query('DESCRIBE remember_me_tokens');
	$migrations[] = ['remember_me_tokens', 'skip', 'Table already exists'];
} catch (Throwable $e) {
	try {
		$conn->query(<<<SQL
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
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
		SQL);
		$migrations[] = ['remember_me_tokens', 'created', 'Table created successfully'];
	} catch (Throwable $ex) {
		$migrations[] = ['remember_me_tokens', 'error', $ex->getMessage()];
	}
}

// Migration 3: Add firebase_uid column to users if missing
try {
	$result = $conn->query("DESCRIBE users firebase_uid");
	$migrations[] = ['users.firebase_uid', 'skip', 'Column already exists'];
} catch (Throwable $e) {
	try {
		$conn->query('ALTER TABLE users ADD COLUMN firebase_uid VARCHAR(255) DEFAULT NULL UNIQUE, ADD INDEX idx_firebase_uid (firebase_uid)');
		$migrations[] = ['users.firebase_uid', 'added', 'Column added successfully'];
	} catch (Throwable $ex) {
		$migrations[] = ['users.firebase_uid', 'error', $ex->getMessage()];
	}
}

// Migration 4: Add last_login_at column to users if missing
try {
	$result = $conn->query("DESCRIBE users last_login_at");
	$migrations[] = ['users.last_login_at', 'skip', 'Column already exists'];
} catch (Throwable $e) {
	try {
		$conn->query('ALTER TABLE users ADD COLUMN last_login_at TIMESTAMP NULL DEFAULT NULL');
		$migrations[] = ['users.last_login_at', 'added', 'Column added successfully'];
	} catch (Throwable $ex) {
		$migrations[] = ['users.last_login_at', 'error', $ex->getMessage()];
	}
}

// Migration 5: Create or migrate admin_accounts table
$adminAccountsExists = false;
try {
	$conn->query('DESCRIBE admin_accounts');
	$adminAccountsExists = true;
	$migrations[] = ['admin_accounts', 'skip', 'Table already exists'];
} catch (Throwable $e) {
	// Table doesn't exist, we'll create it
	
	// Check if old admin_users table exists
	$adminUsersExists = false;
	try {
		$conn->query('DESCRIBE admin_users');
		$adminUsersExists = true;
	} catch (Throwable $ex) {
		// Old table doesn't exist either
	}

	try {
		// Create new admin_accounts table
		$conn->query(<<<SQL
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
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
		SQL);

		// If old admin_users table exists, migrate data (admin_users has Firebase-only auth, no passwords)
		if ($adminUsersExists) {
			// Generate a placeholder password hash for migrated admins (they'll need to reset)
			$placeholderPassword = password_hash('NEEDS_PASSWORD_RESET_' . time(), PASSWORD_BCRYPT);
			
			$conn->query(<<<SQL
				INSERT INTO admin_accounts (id, name, email, password, role, status, created_at, updated_at)
				SELECT 
					id, 
					name, 
					email, 
					'$placeholderPassword' as password,
					'reviewer' as role,
					'active' as status,
					created_at, 
					updated_at 
				FROM admin_users
				ON DUPLICATE KEY UPDATE email=VALUES(email)
			SQL);
			$migrations[] = ['admin_accounts', 'created+migrated', 'Table created and data migrated from admin_users (passwords set to reset required)'];
		} else {
			$migrations[] = ['admin_accounts', 'created', 'Table created (no legacy data to migrate)'];
		}
	} catch (Throwable $ex) {
		$migrations[] = ['admin_accounts', 'error', $ex->getMessage()];
	}
}

// Migration 6: Create admin_permissions table if missing
try {
	$conn->query('DESCRIBE admin_permissions');
	$migrations[] = ['admin_permissions', 'skip', 'Table already exists'];
} catch (Throwable $e) {
	try {
		$conn->query(<<<SQL
			CREATE TABLE admin_permissions (
				admin_id INT NOT NULL,
				permission VARCHAR(150) NOT NULL,
				PRIMARY KEY (admin_id, permission),
				CONSTRAINT fk_admin_permissions_admin FOREIGN KEY (admin_id) REFERENCES admin_accounts(id) ON DELETE CASCADE,
				INDEX idx_admin_permissions_permission (permission)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
		SQL);
		$migrations[] = ['admin_permissions', 'created', 'Table created successfully'];
	} catch (Throwable $ex) {
		$migrations[] = ['admin_permissions', 'error', $ex->getMessage()];
	}
}

// Print results
echo "Migration Results:\n";
echo str_repeat("-", 80) . "\n";

$hasErrors = false;
foreach ($migrations as [$name, $status, $message]) {
	$symbol = match ($status) {
		'created', 'created+migrated', 'added' => '✓',
		'skip' => '○',
		'error' => '✗',
		default => '?'
	};
	
	if ($status === 'error') {
		$hasErrors = true;
		echo "$symbol [$status] $name - $message\n";
	} else {
		echo "$symbol [$status] $name - $message\n";
	}
}

echo str_repeat("-", 80) . "\n";

if ($hasErrors) {
	echo "\n⚠  Some migrations failed. Please review the errors above.\n";
	exit(1);
} else {
	echo "\n✓ All migrations completed successfully!\n";
	echo "\nYour database is now ready for the new authentication system.\n";
	exit(0);
}
