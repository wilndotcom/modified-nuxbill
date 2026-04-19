<?php

/**
 * Simple Widget Fix - Check and Add Fiber Stats Widget
 */

include '../init.php';

echo "<h2>Simple Widget Fix</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";

try {
    // Get actual column names using MySQL
    $rawColumns = ORM::for_table('tbl_widgets')->raw_query('SHOW COLUMNS FROM tbl_widgets')->find_array();
    
    echo "<strong>Table Columns Found:</strong>\n";
    $columns = [];
    foreach ($rawColumns as $col) {
        $colName = isset($col['name']) ? $col['name'] : (isset($col['Field']) ? $col['Field'] : 'unknown');
        $columns[] = $colName;
        echo "  - {$colName}\n";
    }
    
    // Check required columns
    $required = ['widget', 'enabled', 'user', 'orders', 'position'];
    $missing = array_diff($required, $columns);
    
    if (!empty($missing)) {
        echo "\n❌ Missing required columns: " . implode(', ', $missing) . "\n";
        echo "Cannot proceed without these columns.\n";
        exit;
    }
    
    // Check if description column exists
    $hasDescription = in_array('description', $columns);
    
    // Check if fiber_stats widget exists
    $existing = ORM::for_table('tbl_widgets')->where('widget', 'fiber_stats')->find_one();
    
    if ($existing) {
        echo "\n⚠ Widget already exists. Updating...\n";
        $existing->enabled = 1;
        if ($hasDescription) {
            $existing->description = 'Fiber Network Statistics';
        }
        $existing->save();
        echo "✓ Widget updated and enabled\n";
    } else {
        echo "\n<strong>Adding fiber_stats widget...</strong>\n";
        
        // Get max order
        $maxOrder = ORM::for_table('tbl_widgets')->max('orders') ?: 0;
        
        // Build insert data dynamically based on available columns
        $baseData = [
            'widget' => 'fiber_stats',
            'enabled' => 1,
            'user' => 'Admin',
            'orders' => $maxOrder + 1,
            'position' => 1
        ];
        
        if ($hasDescription) {
            $baseData['description'] = 'Fiber Network Statistics';
        }
        
        // Insert for Admin
        $widget1 = ORM::for_table('tbl_widgets')->create();
        foreach ($baseData as $key => $value) {
            $widget1->$key = $value;
        }
        $widget1->save();
        echo "✓ Added for Admin\n";
        
        // Insert for SuperAdmin
        $baseData['user'] = 'SuperAdmin';
        $baseData['orders'] = $maxOrder + 2;
        
        $widget2 = ORM::for_table('tbl_widgets')->create();
        foreach ($baseData as $key => $value) {
            $widget2->$key = $value;
        }
        $widget2->save();
        echo "✓ Added for SuperAdmin\n";
    }
    
    // Verify
    $count = ORM::for_table('tbl_widgets')
        ->where('widget', 'fiber_stats')
        ->where('enabled', 1)
        ->count();
    
    echo "\n<strong>✅ SUCCESS!</strong>\n";
    echo "Active fiber_stats widgets: {$count}\n";
    
    // Show all widgets
    echo "\n<strong>All Active Widgets:</strong>\n";
    $allWidgets = ORM::for_table('tbl_widgets')->where('enabled', 1)->order_by_asc('orders')->find_many();
    foreach ($allWidgets as $w) {
        echo "  - {$w->widget} ({$w->user}) - Order: {$w->orders}\n";
    }
    
    echo "\n<strong>Next:</strong>\n";
    echo "1. <a href='../?_route=dashboard' style='color: blue;'>Go to Dashboard</a>\n";
    echo "2. Press Ctrl+F5 to clear browser cache\n";
    
} catch (Throwable $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}

echo "</pre>";
