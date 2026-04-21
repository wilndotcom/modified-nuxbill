<?php
/**
 * Fix Wallet Widget Position
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
    
    echo "<h2>🔧 Fixing Wallet Widget Position</h2>";
    echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";
    
    // Check if position column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_widgets LIKE 'position'");
    $position_col = $stmt->fetch();
    
    if (!$position_col) {
        echo "⚠️ Adding 'position' column to tbl_widgets...\n";
        $pdo->exec("ALTER TABLE tbl_widgets ADD COLUMN position INT DEFAULT 1");
    }
    
    // Update wallet widget position to 1 (first position)
    $stmt = $pdo->prepare("UPDATE tbl_widgets SET position = 1, orders = 1 WHERE widget = 'wallet' AND user = 'Customer'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Updated wallet widget position to 1\n";
    } else {
        echo "⚠️ Wallet widget not found or already has position 1\n";
    }
    
    // Show all customer widgets
    echo "\n📋 Current Customer Widgets:\n";
    $stmt = $pdo->query("SELECT widget, title, position, orders, enabled FROM tbl_widgets WHERE user = 'Customer' ORDER BY position, orders");
    while ($row = $stmt->fetch()) {
        echo "  - {$row['title']} (pos:{$row['position']}, order:{$row['orders']}) - " . ($row['enabled'] ? '✅' : '❌') . "\n";
    }
    
    echo "\n✅ Done!\n";
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Error</h2>";
    echo "<pre style='background: #ffebee; padding: 15px;'>";
    echo "Database error: " . $e->getMessage() . "\n";
    echo "</pre>";
}
?>
