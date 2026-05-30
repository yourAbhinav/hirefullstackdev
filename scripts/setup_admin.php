<?php
/**
 * Admin Panel Setup Script
 * Run this script to create the admin dashboard tables and setup initial data
 */

require_once __DIR__ . '/../config/db.php';

echo "Starting Admin Panel Setup...\n\n";

try {
    // Read and execute the SQL file
    $sqlFile = __DIR__ . '/create_admin_tables.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);
    
    // Use multi-query for better SQL execution
    if ($conn->multi_query($sql)) {
        $executed = 0;
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
            $executed++;
        } while ($conn->more_results() && $conn->next_result());
        
        echo "✓ Admin tables created successfully\n";
    } else {
        throw new Exception("Multi-query failed: " . $conn->error);
    }
    
    echo "\n========================================\n";
    echo "Setup Complete!\n";
    echo "✓ All admin tables created successfully\n";
    echo "========================================\n";
    
    echo "\nDefault Admin Account:\n";
    echo "Email: admin@devhire.com\n";
    echo "Password: Admin@123\n";
    echo "Role: super_admin\n";
    echo "\nIMPORTANT: Change this password immediately after first login!\n";
    
} catch (Exception $e) {
    echo "Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
