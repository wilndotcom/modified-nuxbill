<?php
include '../init.php';

echo "TESTING WIDGET TEMPLATE\n";
echo "========================\n\n";

// Check if template exists
$tplPath = '../ui/ui/admin/widget/fiber_stats.tpl';
echo "Template file: {$tplPath}\n";
echo "Exists: " . (file_exists($tplPath) ? 'YES ✓' : 'NO ✗') . "\n\n";

if (file_exists($tplPath)) {
    echo "File size: " . filesize($tplPath) . " bytes\n";
    echo "Readable: " . (is_readable($tplPath) ? 'YES' : 'NO') . "\n\n";
    
    // Try to render
    echo "Attempting to render...\n";
    try {
        $ui->assign('total_olts', 5);
        $ui->assign('active_olts', 3);
        $ui->assign('offline_olts', 2);
        $ui->assign('total_onus', 10);
        $ui->assign('active_onus', 7);
        $ui->assign('suspended_onus', 1);
        $ui->assign('inactive_onus', 2);
        $ui->assign('offline_onus', 0);
        $ui->assign('total_cpes', 3);
        $ui->assign('active_cpes', 2);
        $ui->assign('offline_cpes', 1);
        $ui->assign('last_sync_time', date('Y-m-d H:i:s'));
        $ui->assign('error', null);
        
        $output = $ui->fetch('widget/fiber_stats.tpl');
        
        echo "✓ Rendered successfully!\n";
        echo "Output length: " . strlen($output) . " characters\n\n";
        echo "First 1000 characters of output:\n";
        echo "-------------------------------\n";
        echo substr($output, 0, 1000);
        
    } catch (Throwable $e) {
        echo "✗ ERROR: " . $e->getMessage() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "File: " . $e->getFile() . "\n";
    }
} else {
    echo "Template file is MISSING!\n";
    
    // List files in the widget directory
    $widgetDir = '../ui/ui/admin/widget/';
    if (is_dir($widgetDir)) {
        echo "\nFiles in widget directory:\n";
        $files = glob($widgetDir . '*.tpl');
        foreach ($files as $f) {
            echo "  - " . basename($f) . "\n";
        }
    } else {
        echo "Widget directory doesn't exist!\n";
    }
}

echo "\nDONE\n";
