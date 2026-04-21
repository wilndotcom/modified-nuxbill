<?php
/**
 * Project Audit Script - Check for missing codebase
 */

echo "<h2>🔍 Project Audit Report</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";

$issues = [];
$warnings = [];

// 1. Check Payment Gateways for required functions
echo "\n📋 Checking Payment Gateways...\n";
$gateway_files = glob('system/paymentgateway/*.php');
$required_functions = [
    '_validate_config',
    '_show_config',
    '_save_config',
    '_create_transaction',
    '_get_status'
];

foreach ($gateway_files as $file) {
    $basename = basename($file, '.php');
    $content = file_get_contents($file);
    
    foreach ($required_functions as $func) {
        $function_name = $basename . $func;
        if (!preg_match("/function\s+$function_name\s*\(/", $content)) {
            if ($func === '_get_status' || $func === '_create_transaction') {
                $warnings[] = "Gateway '$basename' missing function: $function_name";
            }
        } else {
            echo "  ✅ $basename$func\n";
        }
    }
}

// 2. Check Plugin Files for syntax errors
echo "\n📋 Checking Plugin Files...\n";
$plugin_files = glob('system/plugin/*.php');
foreach ($plugin_files as $file) {
    $output = [];
    $return = 0;
    exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return);
    if ($return !== 0) {
        $issues[] = "Syntax error in: $file";
        echo "  ❌ $file - Syntax Error\n";
    } else {
        echo "  ✅ " . basename($file) . "\n";
    }
}

// 3. Check if template files exist for plugins
echo "\n📋 Checking Plugin Templates...\n";
$plugin_templates = [
    'system/plugin/ui/hotspot_settings.tpl',
    'system/plugin/ui/mpesa_transactions.tpl',
    'system/paymentgateway/ui/paybilltillsbankmpesa.tpl',
    'system/paymentgateway/ui/mpesa.tpl',
];

foreach ($plugin_templates as $template) {
    if (!file_exists($template)) {
        $issues[] = "Missing template: $template";
        echo "  ❌ Missing: $template\n";
    } else {
        echo "  ✅ $template\n";
    }
}

// 4. Check for common missing database columns
echo "\n📋 Checking Database Connection...\n";
$config_file = file_get_contents('config.php');
preg_match("/db_user\s*=\s*['\"](.+?)['\"]/", $config_file, $db_user_match);
preg_match("/db_pass\s*=\s*['\"](.+?)['\"]/", $config_file, $db_pass_match);
preg_match("/db_name\s*=\s*['\"](.+?)['\"]/", $config_file, $db_name_match);

$db_user = $db_user_match[1] ?? 'root';
$db_pass = $db_pass_match[1] ?? '';
$db_name = $db_name_match[1] ?? 'phpnuxbill';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "  ✅ Database connected\n";
    
    // Check required tables
    $required_tables = ['tbl_customers', 'tbl_tickets', 'tbl_payment_gateway', 'tbl_appconfig'];
    foreach ($required_tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if (!$stmt->fetch()) {
            $issues[] = "Missing table: $table";
            echo "  ❌ Missing table: $table\n";
        } else {
            echo "  ✅ Table exists: $table\n";
        }
    }
    
    // Check ticket columns
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_tickets LIKE 'admin_read_at'");
    if (!$stmt->fetch()) {
        $warnings[] = "Missing column: tbl_tickets.admin_read_at";
        echo "  ⚠️  Missing column: tbl_tickets.admin_read_at\n";
    } else {
        echo "  ✅ Column exists: admin_read_at\n";
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_tickets LIKE 'customer_read_at'");
    if (!$stmt->fetch()) {
        $warnings[] = "Missing column: tbl_tickets.customer_read_at";
        echo "  ⚠️  Missing column: tbl_tickets.customer_read_at\n";
    } else {
        echo "  ✅ Column exists: customer_read_at\n";
    }
    
} catch (PDOException $e) {
    $issues[] = "Database connection failed: " . $e->getMessage();
    echo "  ❌ Database connection failed\n";
}

// 5. Check for required files
echo "\n📋 Checking Required Files...\n";
$required_files = [
    'config.php',
    'init.php',
    'index.php',
    'system/boot.php',
    'download.php',
    'system/plugin/initiatempesa.php',
    'system/plugin/c2b.php',
    'system/plugin/initiatepaybilltillsbankmpesa.php',
];

foreach ($required_files as $file) {
    if (!file_exists($file)) {
        $issues[] = "Missing required file: $file";
        echo "  ❌ Missing: $file\n";
    } else {
        echo "  ✅ $file\n";
    }
}

// 6. Check language files
echo "\n📋 Checking Language Files...\n";
$lan_dir = 'system/lan/';
if (is_dir($lan_dir)) {
    $lan_files = glob($lan_dir . '*.json');
    if (count($lan_files) > 0) {
        echo "  ✅ Language files found: " . count($lan_files) . "\n";
    } else {
        $warnings[] = "No language files found in system/lan/";
        echo "  ⚠️  No language files\n";
    }
}

// Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 SUMMARY\n";
echo str_repeat("=", 60) . "\n";

if (count($issues) === 0 && count($warnings) === 0) {
    echo "\n✅ No issues found! Project looks complete.\n";
} else {
    if (count($issues) > 0) {
        echo "\n❌ CRITICAL ISSUES (" . count($issues) . "):\n";
        foreach ($issues as $issue) {
            echo "   - $issue\n";
        }
    }
    
    if (count($warnings) > 0) {
        echo "\n⚠️  WARNINGS (" . count($warnings) . "):\n";
        foreach ($warnings as $warning) {
            echo "   - $warning\n";
        }
    }
}

echo "\n</pre>";
echo "<p><strong>Audit completed!</strong></p>";
