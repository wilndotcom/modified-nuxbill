<?php
include '../init.php';

echo "<h2>Testing Dashboard Widget Rendering</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";

// Simulate what dashboard.php does
$tipeUser = 'Admin';

$widgets = ORM::for_table('tbl_widgets')
    ->where("enabled", 1)
    ->where('user', $tipeUser)
    ->order_by_asc("orders")
    ->findArray();

echo "Found " . count($widgets) . " widgets\n\n";

$fiberFound = false;
$fiberContent = '';

foreach ($widgets as $i => $w) {
    if ($w['widget'] == 'fiber_stats') {
        $fiberFound = true;
        echo ">>> Testing fiber_stats widget...\n";
        
        // Check if file exists
        $widgetFile = __DIR__ . '/../system/widgets/' . $w['widget'] . '.php';
        echo "Widget file: {$widgetFile}\n";
        echo "File exists: " . (file_exists($widgetFile) ? 'YES' : 'NO') . "\n";
        
        if (file_exists($widgetFile)) {
            try {
                require_once $widgetFile;
                
                if (class_exists($w['widget'])) {
                    echo "Class exists: YES\n";
                    
                    $widgetObj = new $w['widget']();
                    echo "Object created: YES\n";
                    
                    $content = $widgetObj->getWidget($w);
                    $fiberContent = $content;
                    
                    echo "Content length: " . strlen($content) . " chars\n";
                    
                    if (strlen($content) > 0) {
                        echo "✓ Widget rendered successfully!\n";
                    } else {
                        echo "✗ Widget returned EMPTY content\n";
                    }
                } else {
                    echo "✗ Class doesn't exist\n";
                }
            } catch (Throwable $e) {
                echo "✗ ERROR: " . $e->getMessage() . "\n";
                echo "Line: " . $e->getLine() . "\n";
            }
        }
        break;
    }
}

echo "\n<strong>SUMMARY:</strong>\n";
if ($fiberFound) {
    echo "✓ fiber_stats widget found in database\n";
    if (strlen($fiberContent) > 0) {
        echo "✓ Widget content generated (" . strlen($fiberContent) . " chars)\n";
    } else {
        echo "✗ Widget content is EMPTY\n";
        echo "\nThis means the widget is running but returning nothing.\n";
        echo "Possible causes:\n";
        echo "1. No OLT devices in database (widget shows empty when no data)\n";
        echo "2. PHP error in widget template\n";
        echo "3. Template file not found by Smarty\n";
    }
} else {
    echo "✗ fiber_stats widget NOT found\n";
}

echo "</pre>";
