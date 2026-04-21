<?php
/**
 * Find Unused Code Analysis
 * Shows what's documented/available but not actively used
 */

echo "<h2>🔍 Unused Code Analysis</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";

// 1. Check for defined functions never called
echo "\n📋 Checking Functions (Defined vs Called)...\n";

$all_php_files = array_merge(
    glob('system/*.php'),
    glob('system/controllers/*.php'),
    glob('system/plugin/*.php'),
    glob('system/paymentgateway/*.php'),
    glob('system/widgets/*.php'),
    glob('system/widgets/customer/*.php'),
    glob('*.php')
);

$defined_functions = [];
$called_functions = [];

foreach ($all_php_files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Find defined functions
    preg_match_all('/function\s+(\w+)\s*\(/', $content, $matches);
    foreach ($matches[1] as $func) {
        $defined_functions[$func] = $file;
    }
    
    // Find called functions (simple pattern)
    preg_match_all('/(\w+)\s*\(/', $content, $calls);
    foreach ($calls[1] as $call) {
        if (!in_array($call, ['if', 'while', 'for', 'foreach', 'switch', 'return', 'echo', 'print', 'include', 'require', 'isset', 'empty', 'count', 'strlen', 'preg_match', 'preg_replace', 'str_replace', 'explode', 'implode', 'array_merge', 'glob', 'file_get_contents', 'file_put_contents', 'basename', 'dirname', 'date', 'time', 'json_encode', 'json_decode', 'ORM', 'Text', 'Lang', 'Message', 'Admin', 'User', 'r2', 'r', '_post', '_get', '_auth', '_admin', '_log', 'sendTelegram', 'run_hook', 'getUrl'])) {
            $called_functions[$call] = true;
        }
    }
}

$unused_functions = [];
foreach ($defined_functions as $func => $file) {
    if (!isset($called_functions[$func]) && !strpos($file, 'paymentgateway') && !strpos($file, 'widget')) {
        // Payment gateway and widget functions are called dynamically
        if (!preg_match('/^(paybilltillsbankmpesa_|mpesa_|hotspot_|wallet_)/', $func)) {
            $unused_functions[$func] = $file;
        }
    }
}

if (count($unused_functions) > 0) {
    echo "\n⚠️  Potentially Unused Functions:\n";
    foreach (array_slice($unused_functions, 0, 20) as $func => $file) {
        echo "   - $func (in $file)\n";
    }
    if (count($unused_functions) > 20) {
        echo "   ... and " . (count($unused_functions) - 20) . " more\n";
    }
} else {
    echo "✅ No obviously unused functions found\n";
}

// 2. Check for orphaned templates
echo "\n\n📋 Checking Templates (Not Referenced)...\n";
$all_tpl_files = [];
foreach (glob('ui/ui/**/*.tpl', GLOB_BRACE) as $f) $all_tpl_files[] = $f;
foreach (glob('system/plugin/ui/*.tpl') as $f) $all_tpl_files[] = $f;
foreach (glob('system/paymentgateway/ui/*.tpl') as $f) $all_tpl_files[] = $f;

$referenced_templates = [];
foreach ($all_php_files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    preg_match_all('/display\([\'"]([\w\/\.]+)\.tpl[\'"]/', $content, $matches);
    foreach ($matches[1] as $tpl) {
        $referenced_templates[$tpl . '.tpl'] = true;
    }
    preg_match_all('/fetch\([\'"]([\w\/\.]+)\.tpl[\'"]/', $content, $matches);
    foreach ($matches[1] as $tpl) {
        $referenced_templates[$tpl . '.tpl'] = true;
    }
}

$orphaned = [];
foreach ($all_tpl_files as $tpl) {
    $basename = basename($tpl);
    $found = false;
    foreach ($referenced_templates as $ref => $v) {
        if (strpos($tpl, $ref) !== false || $basename === $ref) {
            $found = true;
            break;
        }
    }
    if (!$found && !in_array($basename, ['index.html', 'header.tpl', 'footer.tpl', 'error.tpl'])) {
        $orphaned[] = $tpl;
    }
}

if (count($orphaned) > 0) {
    echo "\n⚠️  Potentially Unused Templates:\n";
    foreach (array_slice($orphaned, 0, 15) as $tpl) {
        echo "   - $tpl\n";
    }
} else {
    echo "✅ All templates appear to be referenced\n";
}

// 3. Check for disabled/unused widgets
echo "\n\n📋 Checking Widgets Status...\n";
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
    
    $stmt = $pdo->query("SELECT * FROM tbl_widgets ORDER BY user, position");
    $widgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nWidget Status:\n";
    foreach ($widgets as $w) {
        $status = $w['enabled'] ? '✅ Enabled' : '❌ DISABLED';
        echo "   - {$w['title']} ({$w['widget']}) [{$w['user']}] - $status\n";
    }
} catch (PDOException $e) {
    echo "   Database error: " . $e->getMessage() . "\n";
}

// 4. Check for orphaned CSS/JS
echo "\n\n📋 Checking CSS/JS Files...\n";
$css_files = glob('ui/ui/**/*.{css,js}', GLOB_BRACE);
if (count($css_files) > 0) {
    echo "Found " . count($css_files) . " CSS/JS files in ui/ui/\n";
    foreach (array_slice($css_files, 0, 10) as $f) {
        echo "   - $f\n";
    }
}

// 5. Check config options that might not be used
echo "\n\n📋 Checking App Config Settings...\n";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $stmt = $pdo->query("SELECT setting, value FROM tbl_appconfig WHERE value = '' OR value IS NULL ORDER BY setting LIMIT 20");
    $empty_configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($empty_configs) > 0) {
        echo "\n⚠️  Empty/Unset Configuration Options:\n";
        foreach ($empty_configs as $c) {
            echo "   - {$c['setting']} = (empty)\n";
        }
    }
} catch (PDOException $e) {
    echo "   Database error\n";
}

echo "\n</pre>";
echo "<p><strong>Analysis completed!</strong></p>";
