<?php
include '../init.php';

echo "WIDGET CHECK\n";
echo "============\n\n";

// Check Admin widgets
$widgets = ORM::for_table('tbl_widgets')
    ->where('enabled', 1)
    ->where('user', 'Admin')
    ->order_by_asc('orders')
    ->findArray();

echo "Active widgets for Admin: " . count($widgets) . "\n\n";

$foundFiber = false;
foreach ($widgets as $w) {
    $marker = ($w['widget'] == 'fiber_stats') ? ' >>> ' : '     ';
    echo $marker . $w['widget'] . " (order: " . $w['orders'] . ")\n";
    
    if ($w['widget'] == 'fiber_stats') {
        $foundFiber = true;
    }
}

echo "\n";

if ($foundFiber) {
    echo "✓ fiber_stats widget IS in the list\n";
    
    // Try to render
    echo "\nTesting widget render...\n";
    try {
        require_once '../system/widgets/fiber_stats.php';
        $widget = new fiber_stats();
        $output = $widget->getWidget();
        echo "✓ Widget rendered: " . strlen($output) . " characters\n";
        
        if (strlen($output) < 100) {
            echo "⚠ Warning: Output seems very short\n";
            echo "Content: " . substr($output, 0, 200) . "\n";
        }
    } catch (Throwable $e) {
        echo "✗ Render ERROR: " . $e->getMessage() . "\n";
        echo "Line: " . $e->getLine() . "\n";
    }
} else {
    echo "✗ fiber_stats widget NOT in the list\n";
}

echo "\nDONE\n";
