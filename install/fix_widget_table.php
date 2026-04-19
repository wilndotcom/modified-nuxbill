<?php

/**
 * Fix Widget Table and Add Fiber Stats Widget
 */

include '../init.php';

echo "<h2>Fixing Widget Table...</h2>";
echo "<pre>\n";

try {
    // Check table structure
    echo "Checking tbl_widgets structure...\n";
    $columns = ORM::for_table('tbl_widgets')->raw_query('SHOW COLUMNS FROM tbl_widgets')->find_array();
    
    echo "Found columns:\n";
    $columnNames = [];
    foreach ($columns as $col) {
        echo "  - {$col['Field']}\n";
        $columnNames[] = $col['Field'];
    }
    
    // Check if description column exists
    $hasDescription = in_array('description', $columnNames);
    
    if (!$hasDescription) {
        echo "\n⚠ Column 'description' not found. Adding it...\n";
        ORM::for_table('tbl_widgets')->raw_query("ALTER TABLE tbl_widgets ADD COLUMN description VARCHAR(255) NULL AFTER widget");
        echo "✓ Added 'description' column\n";
    }
    
    // Now check if widget exists
    $existing = ORM::for_table('tbl_widgets')->where('widget', 'fiber_stats')->find_one();
    
    if ($existing) {
        echo "\n⚠ Widget 'fiber_stats' already exists\n";
        
        // Update to enable it
        $existing->enabled = 1;
        $existing->save();
        echo "✓ Enabled existing widget\n";
    } else {
        echo "\nAdding fiber_stats widget...\n";
        
        // Get max order
        $maxOrder = ORM::for_table('tbl_widgets')->max('orders') ?: 0;
        
        // Add for Admin
        $widget = ORM::for_table('tbl_widgets')->create();
        $widget->widget = 'fiber_stats';
        $widget->description = 'Fiber Network Statistics';
        $widget->enabled = 1;
        $widget->user = 'Admin';
        $widget->orders = $maxOrder + 1;
        $widget->position = 1;
        $widget->save();
        
        echo "✓ Added widget for Admin\n";
        
        // Add for SuperAdmin
        $widget2 = ORM::for_table('tbl_widgets')->create();
        $widget2->widget = 'fiber_stats';
        $widget2->description = 'Fiber Network Statistics';
        $widget2->enabled = 1;
        $widget2->user = 'SuperAdmin';
        $widget2->orders = $maxOrder + 2;
        $widget2->position = 1;
        $widget2->save();
        
        echo "✓ Added widget for SuperAdmin\n";
    }
    
    // Verify
    $count = ORM::for_table('tbl_widgets')->where('widget', 'fiber_stats')->where('enabled', 1)->count();
    echo "\n✅ SUCCESS! {$count} fiber_stats widget(s) active\n";
    
    echo "\n<strong>Next Steps:</strong>\n";
    echo "1. Go to Dashboard: <a href='../?_route=dashboard' style='color: blue;'>Click Here</a>\n";
    echo "2. Press Ctrl+F5 to clear cache\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
