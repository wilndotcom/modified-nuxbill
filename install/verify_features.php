<?php

/**
 * Feature Verification Script
 * 
 * Checks if all implemented features are properly installed.
 * 
 * Usage: http://localhost/modified-nuxbill/install/verify_features.php
 */

include '../init.php';

echo "<h2>PHPNuxBill Feature Verification</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Check File Existence
$requiredFiles = [
    'system/widgets/fiber_stats.php' => 'Fiber Stats Widget Controller',
    'ui/ui/admin/widget/fiber_stats.tpl' => 'Fiber Stats Widget Template',
    'ui/ui/admin/fiber/cpe-routers/status.tpl' => 'CPE Status Page',
    'ui/ui/admin/fiber/cpe-routers/configure.tpl' => 'CPE Configure Page',
    'system/cron_olt_sync.php' => 'OLT Sync Script',
    'system/devices/olt/GenericSNMP.php' => 'Generic SNMP OLT Driver',
];

echo "<strong>1. Checking Required Files...</strong>\n";
foreach ($requiredFiles as $file => $desc) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        echo "  ✓ {$desc} - EXISTS\n";
        $success[] = $desc;
    } else {
        echo "  ✗ {$desc} - MISSING ({$file})\n";
        $errors[] = "Missing file: {$file}";
    }
}

// 2. Check Database Tables
echo "\n<strong>2. Checking Database Tables...</strong>\n";
$tables = ['tbl_olt_devices', 'tbl_onus', 'tbl_olt_profiles', 'tbl_cpe_routers'];
foreach ($tables as $table) {
    try {
        $exists = ORM::for_table($table)->raw_query("SHOW TABLES LIKE '{$table}'")->find_one();
        if ($exists) {
            $count = ORM::for_table($table)->count();
            echo "  ✓ {$table} - EXISTS ({$count} records)\n";
        } else {
            echo "  ✗ {$table} - MISSING\n";
            $errors[] = "Missing table: {$table}";
        }
    } catch (Exception $e) {
        echo "  ✗ {$table} - ERROR: " . $e->getMessage() . "\n";
        $errors[] = "Table error: {$table}";
    }
}

// 3. Check Dashboard Widget
echo "\n<strong>3. Checking Dashboard Widget...</strong>\n";
try {
    $widgetCount = ORM::for_table('tbl_widgets')
        ->where('widget', 'fiber_stats')
        ->where('enabled', 1)
        ->count();
    
    if ($widgetCount > 0) {
        echo "  ✓ fiber_stats widget - ENABLED ({$widgetCount} entries)\n";
        $success[] = "Dashboard widget installed";
    } else {
        echo "  ✗ fiber_stats widget - NOT FOUND or DISABLED\n";
        echo "     <a href='add_fiber_widget.php' style='color: blue;'>Click here to install widget</a>\n";
        $warnings[] = "Widget not in database - run add_fiber_widget.php";
    }
} catch (Exception $e) {
    echo "  ✗ Error checking widget: " . $e->getMessage() . "\n";
    $errors[] = "Widget check failed";
}

// 4. Check Language Translations
echo "\n<strong>4. Checking Language File...</strong>\n";
$langFile = __DIR__ . '/../system/lan/english.json';
if (file_exists($langFile)) {
    $langContent = file_get_contents($langFile);
    $langData = json_decode($langContent, true);
    
    $requiredKeys = [
        'OLT_Fiber_Plans',
        'Fiber_Equipment',
        'CPE_Router_Status',
    ];
    
    foreach ($requiredKeys as $key) {
        if (isset($langData[$key])) {
            echo "  ✓ Language key '{$key}' - EXISTS\n";
        } else {
            echo "  ✗ Language key '{$key}' - MISSING\n";
            $warnings[] = "Missing language key: {$key}";
        }
    }
} else {
    echo "  ✗ Language file not found\n";
    $errors[] = "Missing language file";
}

// 5. Check Controller Changes
echo "\n<strong>5. Checking Controllers...</strong>\n";
$controllers = [
    'system/controllers/fiber.php' => ['cpe-status', 'cpe-configure', 'Paginator::findMany'],
    'system/controllers/customers.php' => ['customerOnu', 'customerCpe'],
    'system/controllers/autoload.php' => ['olt-devices', 'OLT'],
];

foreach ($controllers as $file => $markers) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        $found = 0;
        foreach ($markers as $marker) {
            if (strpos($content, $marker) !== false) {
                $found++;
            }
        }
        if ($found === count($markers)) {
            echo "  ✓ {$file} - UPDATED ({$found}/" . count($markers) . " markers found)\n";
        } else {
            echo "  ⚠ {$file} - PARTIAL ({$found}/" . count($markers) . " markers found)\n";
            $warnings[] = "{$file} may be incomplete";
        }
    } else {
        echo "  ✗ {$file} - NOT FOUND\n";
        $errors[] = "Missing controller: {$file}";
    }
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "<strong>SUMMARY</strong>\n";
echo str_repeat("=", 50) . "\n";

if (count($errors) === 0 && count($warnings) === 0) {
    echo "✓ <strong style='color: green;'>ALL FEATURES INSTALLED CORRECTLY!</strong>\n";
    echo "\n<strong>Next Steps:</strong>\n";
    echo "1. Clear browser cache (Ctrl+F5)\n";
    echo "2. Log out and log back in\n";
    echo "3. Visit the Dashboard to see the Fiber Stats widget\n";
    echo "4. Go to Plan > Recharge to see OLT/Fiber option\n";
} else {
    if (count($errors) > 0) {
        echo "\n<strong style='color: red;'>ERRORS (" . count($errors) . "):</strong>\n";
        foreach ($errors as $error) {
            echo "  ✗ {$error}\n";
        }
    }
    
    if (count($warnings) > 0) {
        echo "\n<strong style='color: orange;'>WARNINGS (" . count($warnings) . "):</strong>\n";
        foreach ($warnings as $warning) {
            echo "  ⚠ {$warning}\n";
        }
    }
    
    echo "\n<strong>Fixes:</strong>\n";
    if (in_array("Widget not in database - run add_fiber_widget.php", $warnings)) {
        echo "→ <a href='add_fiber_widget.php' style='color: blue; font-weight: bold;'>Click here to install Dashboard Widget</a>\n";
    }
}

echo "</pre>";

// Quick links
echo "<div style='margin-top: 20px;'>";
echo "<a href='add_fiber_widget.php' style='padding: 10px 20px; background: #5cb85c; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Install Widget</a>";
echo "<a href='../?_route=dashboard' style='padding: 10px 20px; background: #337ab7; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Dashboard</a>";
echo "<a href='../?_route=plan/recharge' style='padding: 10px 20px; background: #f0ad4e; color: white; text-decoration: none; border-radius: 4px;'>Plan Recharge</a>";
echo "</div>";
