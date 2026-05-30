<?php

/**
 * Add FULLTEXT indexes for improved search performance
 * Run this script to enhance search capabilities in admin dashboard
 */

require_once 'config/db.php';

echo "<h2>Adding FULLTEXT Search Indexes</h2>";

try {
    // Add FULLTEXT index on applications table for search fields
    $sql1 = "ALTER TABLE applications ADD FULLTEXT INDEX ft_search (full_name, job_position, tech_stack)";
    
    if ($conn->query($sql1)) {
        echo "<p>✅ FULLTEXT index added to applications table (full_name, job_position, tech_stack)</p>";
    } else {
        echo "<p>ℹ️ FULLTEXT index may already exist on applications table: " . $conn->error . "</p>";
    }
    
    // Note: FULLTEXT indexes require InnoDB or MyISAM storage engines
    // and minimum word length configuration in MySQL (default is 4 characters)
    
    echo "<p><strong>Next steps for production:</strong></p>";
    echo "<ul>";
    echo "<li>Consider using Elasticsearch for large-scale search</li>";
    echo "<li>Implement search result caching</li>";
    echo "<li>Add search analytics for query optimization</li>";
    echo "<li>Configure MySQL ft_min_word_len for shorter words if needed</li>";
    echo "</ul>";
    
    echo "<p><strong>Current search optimizations implemented:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Index-friendly search patterns (trailing wildcards)</li>";
    echo "<li>✅ Smart pattern selection based on search length</li>";
    echo "<li>✅ Exact email matching for index usage</li>";
    echo "<li>✅ Fuzzy matching only where needed (phone, account name)</li>";
    echo "<li>✅ Existing B-tree indexes on key fields</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

$conn->close();

echo "<p><a href='admin/dashboard.php'>Back to Admin Dashboard</a></p>";
?>