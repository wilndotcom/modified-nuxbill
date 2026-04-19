<?php

/**
 * Dashboard Widget Debugger
 * 
 * Debugs why the Fiber Stats widget is not showing on dashboard.
 * 
 * Usage: http://localhost/modified-nuxbill/install/debug_widget.php
 */

include '../init.php';

echo "<h2>🔧 Dashboard Widget Debugger</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px; font-size: 14px;'>\n";

// 1. Check if user is logged in
echo "<strong>1. User Authentication</strong>\n";
if (isset($_SESSION['uid']) && isset($admin)) {
    echo "  ✓ User logged in\n";
    echo "  User ID: {$_SESSION['uid']}\n";
    echo "  User Type: {$admin['user_type']}\n";
    echo "  Username: {$admin['username']}\n";
} else {
    echo "  ✗ No user logged in!\n";
    echo "  <a href='../?_route=login' style='color: red;'>Please login first</a>\n";
    echo "</pre>";
    exit;
}

// 2. Check widgets table
echo "\n<strong>2. Database Check - tbl_widgets</strong>\n";
try {
    $widgets = ORM::for_table('tbl_widgets')
        ->where('enabled', 1)
        ->order_by_asc('orders')
        ->find_many();
    
    echo "  Total enabled widgets: " . count($widgets) . "\n\n";
    
    $foundFiber = false;
    foreach ($widgets as $w) {
        $isFiber = ($w->widget == 'fiber_stats');
        $marker = $isFiber ? '👉' : '  ';
        $style = $isFiber ? " style='color: green; font-weight: bold;'" : '';
        
        echo "{$marker} Widget: <span{$style}>{$w->widget}</span>\n";
        echo "     User: {$w->user}, Enabled: {$w->enabled}, Order: {$w->orders}, Pos: {$w->position}\n";
        
        if ($isFiber) {
            $foundFiber = true;
        }
    }
    
    if (!$foundFiber) {
        echo "\n  ✗ fiber_stats widget NOT FOUND in database!\n";
        echo "  <a href='add_fiber_widget.php' style='color: blue; font-weight: bold;'>→ Click here to add it</a>\n";
    } else {
        echo "\n  ✓ fiber_stats widget found in database\n";
    }
    
} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

// 3. Check if widget file exists
echo "\n<strong>3. Widget File Check</strong>\n";
$widgetFile = __DIR__ . '/../system/widgets/fiber_stats.php';
$widgetTpl = __DIR__ . '/../ui/ui/admin/widget/fiber_stats.tpl';

if (file_exists($widgetFile)) {
    echo "  ✓ Controller file exists: system/widgets/fiber_stats.php\n";
    
    // Try to load it
    try {
        require_once $widgetFile;
        if (class_exists('fiber_stats')) {
            echo "  ✓ fiber_stats class loaded successfully\n";
            
            // Try to instantiate
            $widget = new fiber_stats();
            echo "  ✓ fiber_stats class instantiated\n";
            
        } else {
            echo "  ✗ Class 'fiber_stats' not found in file!\n";
        }
    } catch (Throwable $e) {
        echo "  ✗ Error loading class: " . $e->getMessage() . "\n";
    }
} else {
    echo "  ✗ Controller file MISSING: system/widgets/fiber_stats.php\n";
}

if (file_exists($widgetTpl)) {
    echo "  ✓ Template file exists: ui/ui/admin/widget/fiber_stats.tpl\n";
} else {
    echo "  ✗ Template file MISSING: ui/ui/admin/widget/fiber_stats.tpl\n";
}

// 4. Test widget rendering
echo "\n<strong>4. Widget Rendering Test</strong>\n";
if (isset($widget) && $widget instanceof fiber_stats) {
    try {
        echo "  Trying to render widget...\n";
        $output = $widget->getWidget();
        
        if (!empty($output)) {
            echo "  ✓ Widget rendered successfully!\n";
            echo "  Output length: " . strlen($output) . " characters\n";
        } else {
            echo "  ⚠ Widget returned empty output\n";
            echo "    This could mean no OLT devices exist yet\n";
        }
        
    } catch (Throwable $e) {
        echo "  ✗ Error rendering widget: " . $e->getMessage() . "\n";
        echo "  Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
} else {
    echo "  ⚠ Skipping render test (widget not loaded)\n";
}

// 5. Check database tables
echo "\n<strong>5. Database Tables Check</strong>\n";
$tables = [
    'tbl_olt_devices' => 'OLT Devices',
    'tbl_onus' => 'ONUs',
    'tbl_cpe_routers' => 'CPE Routers'
];

foreach ($tables as $table => $name) {
    try {
        $count = ORM::for_table($table)->count();
        echo "  ✓ {$name}: {$count} records\n";
    } catch (Exception $e) {
        echo "  ✗ {$name}: Table error - " . $e->getMessage() . "\n";
    }
}

// 6. Check for OLT devices
echo "\n<strong>6. OLT Devices Status</strong>\n";
try {
    $olts = ORM::for_table('tbl_olt_devices')->find_many();
    if (count($olts) > 0) {
        echo "  Found " . count($olts) . " OLT device(s):\n";
        foreach ($olts as $olt) {
            echo "    - {$olt->name} ({$olt->ip_address}) - Status: {$olt->status}\n";
        }
    } else {
        echo "  ⚠ No OLT devices found in database\n";
        echo "    <a href='../?_route=fiber/olt-add' style='color: blue;'>Add an OLT device</a>\n";
    }
} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

// 7. Check permissions
echo "\n<strong>7. Permission Check</strong>\n";
$allowedTypes = ['SuperAdmin', 'Admin'];
if (in_array($admin['user_type'], $allowedTypes)) {
    echo "  ✓ User type '{$admin['user_type']}' has permission to view widgets\n";
} else {
    echo "  ✗ User type '{$admin['user_type']}' may not have widget permissions\n";
    echo "    Widget is configured for: Admin, SuperAdmin\n";
}

// Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "<strong>SUMMARY & NEXT STEPS</strong>\n";
echo str_repeat("=", 60) . "\n";

if (!$foundFiber) {
    echo "❌ <strong style='color: red;'>Widget not in database!</strong>\n";
    echo "   <a href='add_fiber_widget.php' style='padding: 10px 20px; background: #5cb85c; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px;'>Click to Install Widget</a>\n";
} else {
    echo "✓ <strong style='color: green;'>Widget is installed!</strong>\n";
    echo "\nIf still not visible, try:\n";
    echo "1. Clear browser cache: Ctrl+F5\n";
    echo "2. Log out and log back in\n";
    echo "3. Check if there are any JavaScript errors in browser console (F12)\n";
    echo "4. Check PHP error logs in XAMPP\n";
}

echo "\n<strong>Quick Links:</strong>\n";
echo "  • <a href='../?_route=dashboard' style='color: #337ab7;'>Go to Dashboard</a>\n";
echo "  • <a href='../?_route=fiber/olt-devices' style='color: #337ab7;'>Manage OLTs</a>\n";
echo "  • <a href='verify_features.php' style='color: #337ab7;'>Full Verification</a>\n";

echo "</pre>";
