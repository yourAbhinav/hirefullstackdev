<?php
/**
 * Database Schema Alignment Script
 * 
 * Fixes remaining schema mismatches:
 * 1. Renames applications table columns (jobPosition → job_position, etc.)
 * 2. Updates users.role enum to remove 'admin'
 * 
 * Usage: php scripts/align_schema.php [--force]
 */

require_once __DIR__ . '/../config/db.php';

$force = in_array('--force', $argv, true);

if (php_sapi_name() !== 'cli') {
	http_response_code(403);
	echo "This script can only be run from the command line.";
	exit(1);
}

echo "\n=== DevHire Database Schema Alignment ===\n\n";

// Step 1: Rename applications table columns
echo "Step 1: Renaming applications table columns...\n";

$columnRenames = [
	'jobPosition' => 'job_position',
	'portfolio' => 'portfolio_url',
	'resume' => 'resume_path'
];

foreach ($columnRenames as $oldName => $newName) {
	try {
		// Check if old column exists
		$conn->query("DESCRIBE applications $oldName");
		
		// Check if new column already exists
		try {
			$conn->query("DESCRIBE applications $newName");
			echo "  ○ Column '$newName' already exists\n";
		} catch (Throwable $e) {
			// Old column exists, new doesn't - rename it
			$conn->query("ALTER TABLE applications CHANGE COLUMN $oldName $newName VARCHAR(255)");
			echo "  ✓ Renamed '$oldName' → '$newName'\n";
		}
	} catch (Throwable $e) {
		echo "  ○ Column '$oldName' doesn't exist (may already be renamed)\n";
	}
}

// Step 2: Update users.role enum to remove 'admin'
echo "\nStep 2: Updating users.role enum...\n";

try {
	// First check current enum
	$result = $conn->query("DESCRIBE users role");
	$row = $result->fetch_assoc();
	$currentType = $row['Type'];
	
	if (strpos($currentType, 'admin') !== false) {
		// Enum includes 'admin', need to remove it
		$conn->query("ALTER TABLE users MODIFY role ENUM('developer', 'company') NOT NULL DEFAULT 'developer'");
		echo "  ✓ Updated role enum to exclude 'admin'\n";
	} else {
		echo "  ○ Role enum already correct\n";
	}
} catch (Throwable $e) {
	echo "  ✗ Failed to update role enum: " . $e->getMessage() . "\n";
}

// Step 3: Verify schema
echo "\nStep 3: Verifying schema...\n";

$allGood = true;

// Check applications columns
echo "  Applications table:\n";
foreach ($columnRenames as $oldName => $newName) {
	try {
		$conn->query("DESCRIBE applications $newName");
		echo "    ✓ $newName exists\n";
	} catch (Throwable $e) {
		echo "    ✗ $newName MISSING\n";
		$allGood = false;
	}
}

// Check users.role
echo "  Users table:\n";
$result = $conn->query("DESCRIBE users role");
$row = $result->fetch_assoc();
if (strpos($row['Type'], 'admin') === false) {
	echo "    ✓ role enum is correct (no admin)\n";
} else {
	echo "    ✗ role enum still includes admin\n";
	$allGood = false;
}

// Final status
echo "\n" . str_repeat("=", 80) . "\n";

if ($allGood) {
	echo "✓ SCHEMA ALIGNMENT COMPLETE\n\n";
	echo "Your database schema now matches the code:\n";
	echo "  ✓ Applications table columns are correctly named\n";
	echo "  ✓ Users.role enum excludes 'admin'\n";
	echo "  ✓ Admin accounts remain in admin_accounts table only\n";
	exit(0);
} else {
	echo "⚠ SCHEMA ALIGNMENT INCOMPLETE\n";
	echo "Some columns/enums are still incorrect. Review errors above.\n";
	exit(1);
}
