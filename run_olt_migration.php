<?php
/**
 * Quick OLT Migration Script
 * Run this once to create OLT tables
 */

require_once 'init.php';

// Check if admin is logged in
_admin();

$admin = Admin::_info();
if ($admin['user_type'] != 'SuperAdmin') {
    die('Access denied. SuperAdmin required.');
}

echo "<h2>OLT Database Migration</h2>";
echo "<pre>";

// Read SQL file
$sql_file = __DIR__ . '/install/olt_tables.sql';
if (!file_exists($sql_file)) {
    die("SQL file not found: $sql_file");
}

$sql = file_get_contents($sql_file);

// Split by semicolon and execute each statement
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && !preg_match('/^--/', $stmt);
    }
);

$success = 0;
$failed = 0;
$errors = [];

foreach ($statements as $statement) {
    if (empty(trim($statement))) {
        continue;
    }
    
    try {
        ORM::raw_execute($statement);
        $success++;
        echo "✓ Executed successfully\n";
    } catch (Exception $e) {
        $failed++;
        $error_msg = $e->getMessage();
        $errors[] = $error_msg;
        
        // Check if table already exists (not a real error)
        if (strpos($error_msg, 'already exists') !== false) {
            echo "⚠ Table already exists (skipped)\n";
        } else {
            echo "✗ Error: $error_msg\n";
        }
    } catch (Throwable $e) {
        $failed++;
        $error_msg = $e->getMessage();
        $errors[] = $error_msg;
        echo "✗ Error: $error_msg\n";
    }
}

// Also run updates.json migrations
echo "\n=== Running updates.json migrations ===\n";
$updates_file = __DIR__ . '/system/updates.json';
$updates = json_decode(file_get_contents($updates_file), true);

if (isset($updates['2025.12.1'])) {
    foreach ($updates['2025.12.1'] as $query) {
        try {
            ORM::raw_execute($query);
            $success++;
            echo "✓ Executed: " . substr($query, 0, 50) . "...\n";
        } catch (Exception $e) {
            $error_msg = $e->getMessage();
            if (strpos($error_msg, 'already exists') !== false || 
                strpos($error_msg, 'Duplicate column') !== false) {
                echo "⚠ Already applied (skipped)\n";
            } else {
                $failed++;
                $errors[] = $error_msg;
                echo "✗ Error: $error_msg\n";
            }
        }
    }
}

echo "\n=== Summary ===\n";
echo "Success: $success\n";
echo "Failed: $failed\n";

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}

if ($failed == 0) {
    echo "\n✅ Migration completed successfully!\n";
    echo "<a href='fiber/olt-devices'>Go to OLT Devices</a>";
} else {
    echo "\n⚠ Some errors occurred. Please review above.\n";
}

echo "</pre>";
