<?php
/**
 * Add admin_read_at column to tbl_tickets for tracking admin read status
 */

include '../init.php';

echo "<h2>🎫 Updating Ticket Table</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";

try {
    // Check if column exists
    $result = ORM::for_table('tbl_tickets')->raw_query("SHOW COLUMNS FROM tbl_tickets LIKE 'admin_read_at'")->find_one();
    
    if (!$result) {
        // Add the column
        ORM::for_table('tbl_tickets')->raw_query("ALTER TABLE tbl_tickets ADD COLUMN admin_read_at DATETIME DEFAULT NULL");
        echo "✅ Added 'admin_read_at' column to tbl_tickets\n";
        
        // Add index for performance
        ORM::for_table('tbl_tickets')->raw_query("ALTER TABLE tbl_tickets ADD INDEX idx_admin_read_at (admin_read_at)");
        echo "✅ Added index for admin_read_at\n";
    } else {
        echo "⚠️ Column 'admin_read_at' already exists\n";
    }
    
    echo "\n✅ Ticket table update complete!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<p><a href='../dashboard' class='btn btn-primary'>Go to Dashboard</a></p>";
