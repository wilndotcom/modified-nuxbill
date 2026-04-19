<?php

/**
 * Emergency Widget Fix - Comprehensive Diagnostics and Repair
 */

// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../init.php';

echo "<h2>🚨 Emergency Widget Fix</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; font-family: monospace;'>";

$errors = [];
$fixes = [];

// 1. CHECK FILES
echo "<h3>1. Checking Widget Files</h3><pre>";

$requiredFiles = [
    '../system/widgets/fiber_stats.php' => 'Widget Controller',
    '../ui/ui/admin/widget/fiber_stats.tpl' => 'Widget Template',
];

foreach ($requiredFiles as $file => $desc) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        echo "✓ {$desc}: {$file} ({$size} bytes)\n";
        
        // Check if readable
        if (!is_readable($fullPath)) {
            $errors[] = "{$desc} not readable";
            echo "  ✗ ERROR: File not readable!\n";
        }
    } else {
        $errors[] = "{$desc} missing";
        echo "✗ {$desc}: {$file} MISSING!\n";
    }
}
echo "</pre>";

// 2. CHECK WIDGET CLASS
echo "<h3>2. Testing Widget Class</h3><pre>";
try {
    require_once __DIR__ . '/../system/widgets/fiber_stats.php';
    
    if (class_exists('fiber_stats')) {
        echo "✓ fiber_stats class exists\n";
        
        $widget = new fiber_stats();
        echo "✓ fiber_stats instantiated\n";
        
        // Try to render
        echo "Testing render...\n";
        $output = $widget->getWidget();
        
        if (!empty($output)) {
            echo "✓ Widget rendered (" . strlen($output) . " chars)\n";
        } else {
            echo "⚠ Widget rendered but empty (this is OK if no OLTs exist)\n";
        }
    } else {
        $errors[] = "fiber_stats class not found";
        echo "✗ fiber_stats class NOT FOUND\n";
    }
} catch (Throwable $e) {
    $errors[] = "Widget error: " . $e->getMessage();
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
echo "</pre>";

// 3. FIX DATABASE
echo "<h3>3. Fixing Database Entries</h3><pre>";
try {
    // Remove broken entries (empty user field)
    $broken = ORM::for_table('tbl_widgets')
        ->where('widget', 'fiber_stats')
        ->where_raw("user = '' OR user IS NULL")
        ->find_many();
    
    foreach ($broken as $b) {
        echo "Removing broken entry (ID: {$b->id}, empty user)\n";
        $b->delete();
        $fixes[] = "Removed broken widget entry";
    }
    
    // Check for valid entries
    $valid = ORM::for_table('tbl_widgets')
        ->where('widget', 'fiber_stats')
        ->where('enabled', 1)
        ->where_in('user', ['Admin', 'SuperAdmin'])
        ->find_many();
    
    if (count($valid) == 0) {
        echo "No valid entries found. Creating new ones...\n";
        
        // Get max order
        $maxOrder = ORM::for_table('tbl_widgets')->max('orders') ?: 0;
        
        // Add for Admin
        $w1 = ORM::for_table('tbl_widgets')->create();
        $w1->widget = 'fiber_stats';
        $w1->enabled = 1;
        $w1->user = 'Admin';
        $w1->orders = $maxOrder + 1;
        $w1->position = 1;
        $w1->save();
        echo "✓ Created widget for Admin\n";
        $fixes[] = "Created Admin widget";
        
        // Add for SuperAdmin
        $w2 = ORM::for_table('tbl_widgets')->create();
        $w2->widget = 'fiber_stats';
        $w2->enabled = 1;
        $w2->user = 'SuperAdmin';
        $w2->orders = $maxOrder + 2;
        $w2->position = 1;
        $w2->save();
        echo "✓ Created widget for SuperAdmin\n";
        $fixes[] = "Created SuperAdmin widget";
    } else {
        echo "✓ Found " . count($valid) . " valid widget entries\n";
        
        // Enable any disabled ones
        $disabled = ORM::for_table('tbl_widgets')
            ->where('widget', 'fiber_stats')
            ->where('enabled', 0)
            ->find_many();
        
        foreach ($disabled as $d) {
            $d->enabled = 1;
            $d->save();
            echo "✓ Enabled disabled widget (ID: {$d->id})\n";
            $fixes[] = "Enabled widget ID {$d->id}";
        }
    }
    
    // Show current state
    $all = ORM::for_table('tbl_widgets')
        ->where('widget', 'fiber_stats')
        ->find_many();
    
    echo "\nCurrent fiber_stats widgets:\n";
    foreach ($all as $w) {
        echo "  ID: {$w->id}, User: '{$w->user}', Enabled: {$w->enabled}, Order: {$w->orders}\n";
    }
    
} catch (Throwable $e) {
    $errors[] = "Database error: " . $e->getMessage();
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
echo "</pre>";

// 4. CHECK DASHBOARD LAYOUT
echo "<h3>4. Checking Dashboard Layout</h3><pre>";
$layoutConfig = $_c['dashboard_Admin'] ?? $_c['dashboard_SuperAdmin'] ?? '';
echo "Dashboard layout config: " . ($layoutConfig ?: "NOT SET - using default") . "\n";

// Check if dashboard template exists
$dashTpl = __DIR__ . '/../ui/ui/admin/dashboard.tpl';
if (file_exists($dashTpl)) {
    echo "✓ Dashboard template exists\n";
    
    // Check if it uses widgets
    $content = file_get_contents($dashTpl);
    if (strpos($content, '$widgets') !== false) {
        echo "✓ Dashboard template uses widgets\n";
    } else {
        $errors[] = "Dashboard template doesn't use widgets";
        echo "✗ Dashboard template doesn't reference widgets!\n";
    }
} else {
    $errors[] = "Dashboard template missing";
    echo "✗ Dashboard template MISSING!\n";
}
echo "</pre>";

// 5. TEST WIDGET ON DASHBOARD
echo "<h3>5. Testing Widget Render</h3><pre>";
try {
    global $ui, $admin;
    
    // Simulate dashboard widget loading
    $tipeUser = $admin['user_type'] ?? 'Admin';
    if (in_array($tipeUser, ['SuperAdmin', 'Admin'])) {
        $tipeUser = 'Admin';
    }
    
    $widgets = ORM::for_table('tbl_widgets')
        ->where("enabled", 1)
        ->where('user', $tipeUser)
        ->order_by_asc("orders")
        ->findArray();
    
    echo "Found " . count($widgets) . " widgets for user type '{$tipeUser}'\n";
    
    $foundFiber = false;
    foreach ($widgets as $i => $w) {
        if ($w['widget'] == 'fiber_stats') {
            $foundFiber = true;
            echo "✓ fiber_stats found in position {$i}\n";
            
            // Try to render
            try {
                require_once __DIR__ . '/../system/widgets/fiber_stats.php';
                $widgetObj = new fiber_stats();
                $content = $widgetObj->getWidget($w);
                echo "  Rendered content length: " . strlen($content) . " chars\n";
                
                if (empty($content)) {
                    echo "  ⚠ Content is empty - this may be normal if no OLTs exist\n";
                }
            } catch (Throwable $e) {
                echo "  ✗ Render error: " . $e->getMessage() . "\n";
                $errors[] = "Widget render failed: " . $e->getMessage();
            }
            break;
        }
    }
    
    if (!$foundFiber) {
        $errors[] = "fiber_stats not in widget list";
        echo "✗ fiber_stats NOT found in widget list!\n";
    }
    
} catch (Throwable $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
echo "</pre>";

// SUMMARY
echo "<h3>Summary</h3><pre>";
if (count($errors) == 0) {
    echo "✅ ALL CHECKS PASSED!\n";
    echo "The widget should now be working.\n";
} else {
    echo "❌ ERRORS FOUND (" . count($errors) . "):\n";
    foreach ($errors as $e) {
        echo "  ✗ {$e}\n";
    }
}

if (count($fixes) > 0) {
    echo "\n🔧 FIXES APPLIED (" . count($fixes) . "):\n";
    foreach ($fixes as $f) {
        echo "  ✓ {$f}\n";
    }
}
echo "</pre>";

// ACTIONS
echo "<h3>Next Steps</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='../?_route=dashboard' target='_blank' style='padding: 15px 30px; background: #337ab7; color: white; text-decoration: none; border-radius: 5px; font-size: 18px; display: inline-block; margin-right: 10px;'>🔍 VIEW DASHBOARD</a>";
echo "<button onclick='location.reload()' style='padding: 15px 30px; background: #5cb85c; color: white; border: none; border-radius: 5px; font-size: 18px; cursor: pointer;'>🔄 RE-RUN FIX</button>";
echo "</div>";

echo "<p><strong>After clicking 'VIEW DASHBOARD':</strong></p>";
echo "<ol>";
echo "<li>Press <strong>Ctrl+F5</strong> to hard refresh</li>";
echo "<li>If still not visible, check browser console (F12) for JavaScript errors</li>";
echo "<li>Check XAMPP error logs: C:\xampp\apache\logs\error.log</li>";
echo "</ol>";

echo "</div>";
