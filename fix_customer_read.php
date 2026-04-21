<?php
/**
 * Fix script to add customer_read_at column for ticket notifications
 */

// Read database credentials from config
$config_file = file_get_contents('config.php');
preg_match("/db_user\s*=\s*['\"](.+?)['\"]/", $config_file, $db_user_match);
preg_match("/db_pass\s*=\s*['\"](.+?)['\"]/", $config_file, $db_pass_match);
preg_match("/db_name\s*=\s*['\"](.+?)['\"]/", $config_file, $db_name_match);

$db_user = $db_user_match[1] ?? 'root';
$db_pass = $db_pass_match[1] ?? '';
$db_name = $db_name_match[1] ?? 'phpnuxbill';

// Connect to MySQL
try {
    $pdo = new PDO("mysql:host=localhost;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Adding customer_read_at Column</h2>";
    echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_tickets LIKE 'customer_read_at'");
    $column_exists = $stmt->fetch();
    
    if (!$column_exists) {
        $pdo->exec("ALTER TABLE tbl_tickets ADD COLUMN customer_read_at DATETIME DEFAULT NULL");
        echo "✅ Added 'customer_read_at' column\n";
        
        try {
            $pdo->exec("ALTER TABLE tbl_tickets ADD INDEX idx_customer_read_at (customer_read_at)");
            echo "✅ Added index for customer_read_at\n";
        } catch (PDOException $e) {
            echo "⚠️ Index may already exist\n";
        }
    } else {
        echo "⚠️ Column 'customer_read_at' already exists\n";
    }
    
    echo "\n✅ Done! Customer ticket notifications are now enabled.\n";
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Error</h2>";
    echo "<pre style='background: #ffebee; padding: 15px;'>";
    echo "Database connection failed: " . $e->getMessage() . "\n";
    echo "\nPlease check your config.php settings.\n";
    echo "</pre>";
}
?>
