<?php
/**
 * Emergency Column Fix - Direct PDO
 */

include '../init.php';

echo "<h2>Emergency Column Fix</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";

try {
    // Get raw PDO connection
    $pdo = ORM::get_db();
    
    // Columns to add
    $columns = [
        'tbl_onus' => [
            'last_seen' => 'DATETIME NULL',
            'signal_level' => 'VARCHAR(20) NULL',
            'distance' => 'VARCHAR(20) NULL',
            'uptime' => 'VARCHAR(50) NULL',
        ],
        'tbl_olt_devices' => [
            'last_seen' => 'DATETIME NULL',
        ],
        'tbl_cpe_routers' => [
            'last_seen' => 'DATETIME NULL',
        ],
    ];
    
    foreach ($columns as $table => $cols) {
        echo "\n<strong>Table: {$table}</strong>\n";
        
        // Get existing columns
        $stmt = $pdo->query("SHOW COLUMNS FROM {$table}");
        $existing = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existing[] = $row['Field'];
        }
        
        foreach ($cols as $col => $type) {
            if (in_array($col, $existing)) {
                echo "  ✓ {$col} already exists\n";
            } else {
                echo "  ⚠ Adding {$col}... ";
                try {
                    $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$col} {$type}");
                    echo "✓ DONE\n";
                } catch (PDOException $e) {
                    echo "✗ ERROR: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\n<strong>✅ All columns processed!</strong>\n";
    echo "\n<a href='../?_route=dashboard' style='padding: 10px 20px; background: #337ab7; color: white; text-decoration: none; border-radius: 4px;'>Go to Dashboard</a>\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
