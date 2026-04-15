<?php

/**
 *  PHP Mikrotik Billing - CPE Router Tables Migration
 *  Quick migration script to create CPE router management tables
 * 
 *  Access this file via browser: http://your-domain/phpnuxbill/create_cpe_tables.php
 **/

session_start();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'init.php';

// Check admin login
_admin();

// Read updates.json
$updates_file = __DIR__ . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'updates.json';
if (!file_exists($updates_file)) {
    die('Error: updates.json not found');
}

$updates = json_decode(file_get_contents($updates_file), true);
if (!$updates || !isset($updates['2025.12.2'])) {
    die('Error: CPE tables migration not found in updates.json');
}

$queries = $updates['2025.12.2'];

echo "<h2>Creating CPE Router Tables...</h2>";
echo "<pre>";

$success = 0;
$failed = 0;

foreach ($queries as $index => $query) {
    try {
        ORM::raw_execute($query);
        $success++;
        echo "✓ Query " . ($index + 1) . " executed successfully\n";
    } catch (Exception $e) {
        $msg = $e->getMessage();
        // Check if table already exists (not an error)
        if (strpos($msg, 'already exists') !== false || 
            strpos($msg, 'Duplicate column') !== false ||
            strpos($msg, 'Duplicate key') !== false ||
            strpos($msg, 'Duplicate entry') !== false) {
            echo "⚠ Query " . ($index + 1) . ": Already exists (skipped)\n";
        } else {
            $failed++;
            echo "✗ Query " . ($index + 1) . " Error: $msg\n";
        }
    }
}

echo "\n=== Summary ===\n";
echo "Success: $success\n";
echo "Failed: $failed\n";

if ($failed == 0) {
    echo "\n✅ All CPE router tables created successfully!\n";
    echo "\n<a href='fiber/cpe-routers'>Go to CPE Routers</a> | ";
    echo "<a href='dashboard'>Go to Dashboard</a>\n";
} else {
    echo "\n⚠ Some errors occurred. Please check above.\n";
}

echo "</pre>";
