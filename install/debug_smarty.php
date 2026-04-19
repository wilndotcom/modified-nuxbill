<?php
include '../init.php';

echo "<h2>Smarty Debug</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";

// Test fetching the template
try {
    echo "Testing template fetch...\n\n";
    
    // Assign test variables
    $ui->assign('total_olts', 5);
    $ui->assign('active_olts', 3);
    $ui->assign('offline_olts', 2);
    $ui->assign('total_onus', 10);
    $ui->assign('active_onus', 7);
    $ui->assign('suspended_onus', 1);
    $ui->assign('inactive_onus', 1);
    $ui->assign('offline_onus', 1);
    $ui->assign('total_cpes', 3);
    $ui->assign('active_cpes', 2);
    $ui->assign('offline_cpes', 1);
    $ui->assign('last_sync_time', date('Y-m-d H:i:s'));
    $ui->assign('error', null);
    
    echo "Variables assigned.\n";
    
    // Try to fetch
    $output = $ui->fetch('widget/fiber_stats.tpl');
    
    echo "Fetch completed.\n";
    echo "Output length: " . strlen($output) . "\n\n";
    
    if (strlen($output) > 0) {
        echo "✓ SUCCESS! Template rendered.\n";
        echo "First 500 chars:\n";
        echo htmlspecialchars(substr($output, 0, 500));
    } else {
        echo "✗ Template returned empty.\n";
        
        // Check if template exists in Smarty's view
        $tplPaths = $ui->getTemplateDir();
        echo "\nTemplate paths:\n";
        print_r($tplPaths);
        
        // Check specific file
        $testFile = $tplPaths[0] . 'widget/fiber_stats.tpl';
        echo "\nLooking for: {$testFile}\n";
        echo "Exists: " . (file_exists($testFile) ? 'YES' : 'NO') . "\n";
    }
    
} catch (Throwable $e) {
    echo "✗ ERROR:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
