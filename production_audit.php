<?php
/**
 * Production Audit Script - Check everything before deployment
 */

echo "<h1>🔍 Production Audit Report</h1>";
echo "<pre style='background:#f5f5f5;padding:15px;border-radius:5px;'>";

$issues = [];
$warnings = [];
$success = [];

// 1. Check Git Status
echo "\n📋 === GIT STATUS CHECK ===\n";
chdir(__DIR__);
$gitStatus = shell_exec('git status --porcelain 2>&1');
if ($gitStatus) {
    $lines = explode("\n", trim($gitStatus));
    if (count($lines) > 0 && $lines[0] != '') {
        echo "⚠️ Untracked/Modified files found:\n";
        foreach ($lines as $line) {
            if ($line) echo "  - $line\n";
        }
        $warnings[] = "Uncommitted changes found";
    } else {
        echo "✅ All files committed\n";
        $success[] = "Git clean";
    }
} else {
    echo "❌ Could not check git status\n";
    $issues[] = "Git check failed";
}

// 2. Check PHP Syntax
echo "\n🐘 === PHP SYNTAX CHECK ===\n";
$phpFiles = [
    'system/controllers/device_access.php',
    'system/controllers/fiber.php',
    'system/controllers/ticket.php',
    'system/controllers/customer_ticket.php',
    'system/paymentgateway/mpesa.php',
    'system/paymentgateway/paybilltillsbankmpesa.php',
    'system/plugin/CreateHotspotUser.php',
    'system/plugin/hotspot_settings.php',
    'system/plugin/initiatempesa.php',
    'system/plugin/initiatepaybilltillsbankmpesa.php',
    'system/plugin/c2b.php',
    'system/plugin/mpesa_transactions.php',
];

foreach ($phpFiles as $file) {
    if (file_exists($file)) {
        $result = shell_exec("php -l $file 2>&1");
        if (strpos($result, 'No syntax errors') !== false) {
            echo "✅ $file\n";
        } else {
            echo "❌ $file - SYNTAX ERROR\n";
            echo "   $result\n";
            $issues[] = "Syntax error in $file";
        }
    } else {
        echo "⚠️ $file - MISSING\n";
        $issues[] = "Missing file: $file";
    }
}

// 3. Check Database Tables
echo "\n🗄️ === DATABASE CHECK ===\n";
try {
    require_once 'config.php';
    $db = ORM::get_db();
    
    $tables = [
        'tbl_cpe_devices',
        'tbl_olt_devices',
        'tbl_onu_devices',
        'tbl_olt_profiles',
        'tbl_tickets',
        'tbl_ticket_replies',
        'tbl_widgets',
        'tbl_payment_gateway',
    ];
    
    foreach ($tables as $table) {
        $check = $db->query("SHOW TABLES LIKE '$table'")->fetch();
        if ($check) {
            echo "✅ $table exists\n";
            $success[] = "Table $table exists";
        } else {
            echo "⚠️ $table - MISSING\n";
            $warnings[] = "Missing table: $table";
        }
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    $issues[] = "Database connection failed";
}

// 4. Check Templates
echo "\n🎨 === TEMPLATE CHECK ===\n";
$templates = [
    'ui/ui/admin/device_access/dashboard.tpl',
    'ui/ui/admin/device_access/add.tpl',
    'ui/ui/admin/device_access/edit.tpl',
    'ui/ui/admin/device_access/list.tpl',
    'ui/ui/admin/fiber/olt-devices.tpl',
    'ui/ui/admin/fiber/onu-devices.tpl',
    'ui/ui/admin/fiber/onu-profiles.tpl',
    'ui/ui/widget/customers/wallet.tpl',
    'ui/ui/customer/ticket/create.tpl',
    'ui/ui/customer/ticket/list.tpl',
];

foreach ($templates as $tpl) {
    if (file_exists($tpl)) {
        echo "✅ $tpl\n";
    } else {
        echo "⚠️ $tpl - MISSING\n";
        $warnings[] = "Missing template: $tpl";
    }
}

// 5. Check Critical Config
echo "\n⚙️ === CONFIGURATION CHECK ===\n";
if (file_exists('config.php')) {
    echo "✅ config.php exists\n";
} else {
    echo "❌ config.php - MISSING (CRITICAL)\n";
    $issues[] = "config.php missing";
}

if (file_exists('init.php')) {
    echo "✅ init.php exists\n";
} else {
    echo "❌ init.php - MISSING (CRITICAL)\n";
    $issues[] = "init.php missing";
}

// 6. Summary
echo "\n📊 === SUMMARY ===\n";
echo "✅ Success: " . count($success) . "\n";
echo "⚠️ Warnings: " . count($warnings) . "\n";
echo "❌ Issues: " . count($issues) . "\n";

if (count($issues) > 0) {
    echo "\n❌ CRITICAL ISSUES (Must fix before deployment):\n";
    foreach ($issues as $i) {
        echo "  - $i\n";
    }
}

if (count($warnings) > 0) {
    echo "\n⚠️ WARNINGS (Should fix before deployment):\n";
    foreach ($warnings as $w) {
        echo "  - $w\n";
    }
}

if (count($issues) == 0 && count($warnings) == 0) {
    echo "\n🎉 READY FOR PRODUCTION! 🎉\n";
}

echo "\n</pre>";
?>
