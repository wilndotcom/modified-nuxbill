<?php
/**
 * Create OLT Tables - Quick Migration
 * Run this file once via browser: http://localhost/phpnuxbill/create_olt_tables.php
 */

session_start();
require_once 'init.php';

// Check admin login
_admin();

// Simple check - just try to create tables
try {
    // Read updates.json
    $updates_file = __DIR__ . '/system/updates.json';
    $updates = json_decode(file_get_contents($updates_file), true);
    
    if (!isset($updates['2025.12.1'])) {
        die('Error: Migration 2025.12.1 not found in updates.json');
    }
    
    echo "<h2>Creating OLT Tables...</h2>";
    echo "<pre>";
    
    $success = 0;
    $failed = 0;
    
    foreach ($updates['2025.12.1'] as $query) {
        try {
            ORM::raw_execute($query);
            $success++;
            echo "✓ Table created successfully\n";
        } catch (Exception $e) {
            $msg = $e->getMessage();
            // Check if table already exists (not an error)
            if (strpos($msg, 'already exists') !== false || 
                strpos($msg, 'Duplicate column') !== false ||
                strpos($msg, 'Duplicate key') !== false) {
                echo "⚠ Already exists (skipped)\n";
            } else {
                $failed++;
                echo "✗ Error: $msg\n";
            }
        }
    }
    
    echo "\n=== Summary ===\n";
    echo "Success: $success\n";
    echo "Failed: $failed\n";
    
    if ($failed == 0) {
        echo "\n✅ All tables created successfully!\n";
        echo "\n<a href='fiber/olt-devices'>Go to OLT Devices</a> | ";
        echo "<a href='dashboard'>Go to Dashboard</a>\n";
    } else {
        echo "\n⚠ Some errors occurred. Please check above.\n";
    }
    
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<pre>Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "</pre>";
}
