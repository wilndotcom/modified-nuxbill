<?php
/**
 * Emergency Fix for admin_read_at column
 * Access this file via browser: http://your-site/fix_column.php
 */

// Database connection from config
try {
    $config_file = file_get_contents('config.php');
    
    // Extract database credentials using regex
    preg_match("/db_user\s*=\s*['\"](.+?)['\"]/", $config_file, $db_user_match);
    preg_match("/db_pass\s*=\s*['\"](.+?)['\"]/", $config_file, $db_pass_match);
    preg_match("/db_name\s*=\s*['\"](.+?)['\"]/", $config_file, $db_name_match);
    
    $db_user = $db_user_match[1] ?? 'root';
    $db_pass = $db_pass_match[1] ?? '';
    $db_name = $db_name_match[1] ?? 'phpnuxbill';
    
    // Connect to MySQL
    $pdo = new PDO("mysql:host=localhost;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Fixing admin_read_at Column</h2>";
    echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_tickets LIKE 'admin_read_at'");
    $column_exists = $stmt->fetch();
    
    if (!$column_exists) {
        // Add the column
        $pdo->exec("ALTER TABLE tbl_tickets ADD COLUMN admin_read_at DATETIME DEFAULT NULL");
        echo "✅ Added 'admin_read_at' column\n";
        
        // Add index
        try {
            $pdo->exec("ALTER TABLE tbl_tickets ADD INDEX idx_admin_read_at (admin_read_at)");
            echo "✅ Added index for admin_read_at\n";
        } catch (PDOException $e) {
            echo "⚠️ Index may already exist\n";
        }
    } else {
        echo "⚠️ Column 'admin_read_at' already exists\n";
    }
    
    // Also check and add other potential missing columns
    $columns_to_check = [
        'priority' => "ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium' AFTER status",
        'category' => "VARCHAR(50) DEFAULT 'general' AFTER priority",
        'assigned_to' => "INT(11) UNSIGNED DEFAULT NULL AFTER category"
    ];
    
    foreach ($columns_to_check as $col_name => $col_def) {
        $stmt = $pdo->query("SHOW COLUMNS FROM tbl_tickets LIKE '$col_name'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE tbl_tickets ADD COLUMN $col_name $col_def");
            echo "✅ Added '$col_name' column\n";
        }
    }
    
    echo "\n✅ Fix complete! You can now delete this file.\n";
    echo "</pre>";
    
    echo "<p><a href='dashboard' style='padding: 10px 20px; background: #337ab7; color: white; text-decoration: none; border-radius: 4px;'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<pre style='background: #f5f5f5; padding: 15px;'>";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nTry running this SQL directly in phpMyAdmin:\n\n";
    echo "ALTER TABLE tbl_tickets ADD COLUMN admin_read_at DATETIME DEFAULT NULL;\n";
    echo "ALTER TABLE tbl_tickets ADD INDEX idx_admin_read_at (admin_read_at);\n";
    echo "</pre>";
}
