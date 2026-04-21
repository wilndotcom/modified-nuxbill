<?php
/**
 * Add Wallet Widget to Customer Dashboard
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
    
    echo "<h2>🔧 Adding Wallet Widget</h2>";
    echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";
    
    // Check if wallet widget exists
    $stmt = $pdo->query("SELECT * FROM tbl_widgets WHERE widget = 'wallet' AND user = 'Customer'");
    $widget = $stmt->fetch();
    
    if (!$widget) {
        // Add the wallet widget at position 1 (top)
        $pdo->exec("INSERT INTO tbl_widgets (widget, title, user, enabled, orders, created_at) 
                    VALUES ('wallet', 'My Wallet', 'Customer', 1, 1, NOW())");
        echo "✅ Added 'wallet' widget to customer dashboard\n";
    } else {
        // Enable it if disabled
        if (!$widget['enabled']) {
            $pdo->exec("UPDATE tbl_widgets SET enabled = 1 WHERE id = {$widget['id']}");
            echo "✅ Enabled existing wallet widget\n";
        } else {
            echo "⚠️ Wallet widget already exists and is enabled\n";
        }
    }
    
    // Show all customer widgets
    echo "\n📋 Current Customer Widgets:\n";
    $stmt = $pdo->query("SELECT * FROM tbl_widgets WHERE user = 'Customer' ORDER BY orders");
    while ($row = $stmt->fetch()) {
        echo "  - {$row['title']} ({$row['widget']}) - " . ($row['enabled'] ? '✅ Enabled' : '❌ Disabled') . "\n";
    }
    
    echo "\n✅ Done! Wallet widget added to customer dashboard.\n";
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Error</h2>";
    echo "<pre style='background: #ffebee; padding: 15px;'>";
    echo "Database connection failed: " . $e->getMessage() . "\n";
    echo "\nPlease check your config.php settings.\n";
    echo "</pre>";
}
?>
