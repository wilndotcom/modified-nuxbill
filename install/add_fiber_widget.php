<?php

/**
 * Fiber Stats Widget Installer
 * 
 * Run this script to add the fiber_stats widget to the dashboard.
 * 
 * Usage: http://localhost/modified-nuxbill/install/add_fiber_widget.php
 * Or: php install/add_fiber_widget.php
 */

// Include initialization
if (file_exists('../init.php')) {
    include '../init.php';
} elseif (file_exists('init.php')) {
    include 'init.php';
} else {
    die("Error: Cannot find init.php file\n");
}

echo "<h2>Fiber Stats Widget Installer</h2>\n";
echo "<pre>\n";

// Check if we're in web or CLI mode
$isCli = (php_sapi_name() === 'cli');

// Function to output message
function msg($text, $type = 'info') {
    global $isCli;
    if ($isCli) {
        echo $text . "\n";
    } else {
        $color = ($type == 'success') ? 'green' : (($type == 'error') ? 'red' : 'black');
        echo "<span style='color: {$color};'>{$text}</span>\n";
    }
}

try {
    // Check if table exists
    $tableExists = ORM::for_table('tbl_widgets')->raw_query("SHOW TABLES LIKE 'tbl_widgets'")->find_one();
    
    if (!$tableExists) {
        msg("ERROR: tbl_widgets table not found!", 'error');
        echo "</pre>";
        exit;
    }
    
    msg("✓ tbl_widgets table found");
    
    // Check if widget already exists
    $existingWidget = ORM::for_table('tbl_widgets')
        ->where('widget', 'fiber_stats')
        ->find_one();
    
    if ($existingWidget) {
        msg("⚠ Widget 'fiber_stats' already exists in database", 'info');
        msg("  Current status: " . ($existingWidget->enabled ? 'ENABLED' : 'DISABLED'));
        msg("  User type: " . $existingWidget->user);
        
        // Enable it if disabled
        if (!$existingWidget->enabled) {
            $existingWidget->enabled = 1;
            $existingWidget->save();
            msg("✓ Widget enabled successfully!", 'success');
        }
    } else {
        // Add widget for Admin
        $widgetAdmin = ORM::for_table('tbl_widgets')->create();
        $widgetAdmin->widget = 'fiber_stats';
        $widgetAdmin->description = 'Fiber Network Statistics';
        $widgetAdmin->enabled = 1;
        $widgetAdmin->user = 'Admin';
        $widgetAdmin->orders = 5;
        $widgetAdmin->position = 1;
        $widgetAdmin->save();
        
        msg("✓ Added fiber_stats widget for Admin users", 'success');
        
        // Add widget for SuperAdmin
        $widgetSuper = ORM::for_table('tbl_widgets')->create();
        $widgetSuper->widget = 'fiber_stats';
        $widgetSuper->description = 'Fiber Network Statistics';
        $widgetSuper->enabled = 1;
        $widgetSuper->user = 'SuperAdmin';
        $widgetSuper->orders = 5;
        $widgetSuper->position = 1;
        $widgetSuper->save();
        
        msg("✓ Added fiber_stats widget for SuperAdmin users", 'success');
    }
    
    // Verify installation
    $count = ORM::for_table('tbl_widgets')
        ->where('widget', 'fiber_stats')
        ->where('enabled', 1)
        ->count();
    
    msg("\n----------------------------------------");
    msg("✓ Installation Complete!", 'success');
    msg("Active fiber_stats widgets: {$count}");
    msg("\nNext steps:");
    msg("1. Clear browser cache (Ctrl+F5)");
    msg("2. Go to Dashboard to see the widget");
    msg("3. Add OLT devices at: Network > Fiber > OLT Devices");
    
    if (!$isCli) {
        echo "\n<a href='../?_route=dashboard' style='font-size: 18px; padding: 10px 20px; background: #337ab7; color: white; text-decoration: none; border-radius: 4px;'>Go to Dashboard</a>\n";
    }
    
} catch (Exception $e) {
    msg("ERROR: " . $e->getMessage(), 'error');
}

echo "</pre>\n";

// Auto-redirect after 3 seconds if web
if (!$isCli) {
    echo "<script>setTimeout(function() { window.location.href = '../?_route=dashboard'; }, 5000);</script>";
    echo "<p>Redirecting to dashboard in 5 seconds...</p>";
}
