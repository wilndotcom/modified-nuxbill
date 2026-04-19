<?php
/**
 * Check database tables
 */

include 'init.php';

echo "<h2>Database Table Check</h2>";
echo "<hr>";

$required_tables = [
    'tbl_customers',
    'tbl_tickets',
    'tbl_ticket_replies',
    'tbl_ticket_categories',
    'tbl_plans',
    'tbl_routers',
    'tbl_transactions',
    'tbl_user_recharges'
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Table</th><th>Status</th></tr>";

foreach ($required_tables as $table) {
    try {
        $exists = ORM::for_table($table)->raw_query("SHOW TABLES LIKE '$table'")->find_one();
        $status = $exists ? "<span style='color:green'>EXISTS</span>" : "<span style='color:red'>MISSING</span>";
    } catch (Exception $e) {
        $status = "<span style='color:red'>ERROR: " . $e->getMessage() . "</span>";
    }
    echo "<tr><td>$table</td><td>$status</td></tr>";
}

echo "</table>";
echo "<hr>";

// Check for specific errors
echo "<h3>Error Log</h3>";
$error_log = 'system/logs/error.log';
if (file_exists($error_log)) {
    $lines = file($error_log);
    $recent = array_slice($lines, -10);
    echo "<pre>";
    foreach ($recent as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "No error log found.<br>";
}
