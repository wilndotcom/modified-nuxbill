<?php
/**
 * Check for Missing Features
 * Quick audit of what's implemented vs documented
 */

include '../init.php';

echo "<h2>🔍 Missing Features Audit</h2>";
echo "<div style='font-family: monospace;'>";

$categories = [
    'Debt System' => [
        'files' => [
            'system/cron_debt_notification.php' => 'Debt Notification Cron',
            'system/cron_debt_disconnect.php' => 'Debt Disconnect Cron',
        ],
        'tables' => ['tbl_customer_debt', 'tbl_debt_notifications'],
        'config' => ['debt_notification_enabled', 'debt_grace_period_days'],
    ],
    'Ticket Siren' => [
        'files' => [
            'system/widgets/ticket_siren.php' => 'Ticket Siren Widget',
        ],
        'widgets' => ['ticket_siren'],
    ],
    'OLT Drivers' => [
        'files' => [
            'system/devices/olt/Huawei.php' => 'Huawei OLT Driver',
            'system/devices/olt/ZTE.php' => 'ZTE OLT Driver',
            'system/devices/olt/BDCOM.php' => 'BDCOM OLT Driver',
            'system/devices/olt/VSOL.php' => 'VSOL OLT Driver',
        ],
    ],
    'Customer Enhancements' => [
        'columns' => [
            'tbl_customers' => ['router_id', 'service_type_filtering'],
        ],
    ],
];

foreach ($categories as $category => $checks) {
    echo "<h3>{$category}</h3><ul>";
    
    // Check files
    if (isset($checks['files'])) {
        foreach ($checks['files'] as $file => $desc) {
            $exists = file_exists(__DIR__ . '/../' . $file);
            $icon = $exists ? '✅' : '❌';
            echo "<li>{$icon} {$desc}: " . ($exists ? 'EXISTS' : 'MISSING') . "</li>";
        }
    }
    
    // Check tables
    if (isset($checks['tables'])) {
        foreach ($checks['tables'] as $table) {
            try {
                $exists = ORM::for_table($table)->raw_query("SHOW TABLES LIKE '{$table}'")->find_one();
                $icon = $exists ? '✅' : '❌';
                echo "<li>{$icon} Table {$table}: " . ($exists ? 'EXISTS' : 'MISSING') . "</li>";
            } catch (Exception $e) {
                echo "<li>❌ Table {$table}: ERROR - " . $e->getMessage() . "</li>";
            }
        }
    }
    
    // Check columns
    if (isset($checks['columns'])) {
        foreach ($checks['columns'] as $table => $columns) {
            foreach ($columns as $column) {
                try {
                    $cols = ORM::for_table($table)->raw_query("SHOW COLUMNS FROM {$table}")->find_array();
                    $exists = false;
                    foreach ($cols as $col) {
                        if ($col['Field'] === $column) {
                            $exists = true;
                            break;
                        }
                    }
                    $icon = $exists ? '✅' : '❌';
                    echo "<li>{$icon} {$table}.{$column}: " . ($exists ? 'EXISTS' : 'MISSING') . "</li>";
                } catch (Exception $e) {
                    echo "<li>❌ {$table}.{$column}: ERROR</li>";
                }
            }
        }
    }
    
    // Check widgets
    if (isset($checks['widgets'])) {
        foreach ($checks['widgets'] as $widget) {
            $exists = ORM::for_table('tbl_widgets')->where('widget', $widget)->find_one();
            $icon = $exists ? '✅' : '❌';
            echo "<li>{$icon} Widget '{$widget}': " . ($exists ? 'IN DATABASE' : 'NOT IN DATABASE') . "</li>";
        }
    }
    
    // Check config
    if (isset($checks['config'])) {
        foreach ($checks['config'] as $config) {
            global $_c;
            $exists = isset($_c[$config]);
            $icon = $exists ? '✅' : '❌';
            echo "<li>{$icon} Config '{$config}': " . ($exists ? 'SET' : 'NOT SET') . "</li>";
        }
    }
    
    echo "</ul>";
}

// Summary
echo "<h3>Summary</h3>";
echo "<p>Review the checkmarks above. Features with ❌ need implementation.</p>";
echo "<p><a href='../?_route=dashboard' style='padding: 10px 20px; background: #337ab7; color: white; text-decoration: none; border-radius: 4px;'>Go to Dashboard</a></p>";

echo "</div>";
