<?php
include '../init.php';

echo "<h2>Dashboard Layout Analysis</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";

// Get user type
$tipeUser = 'Admin'; // Default

// Get dashboard config
$dtipe = "dashboard_" . $tipeUser;
$layoutConfig = $_c[$dtipe] ?? '';

echo "User Type: {$tipeUser}\n";
echo "Config Key: {$dtipe}\n";
echo "Layout Config: " . ($layoutConfig ?: "(not set - using default)") . "\n\n";

// Parse layout
if (!empty($layoutConfig)) {
    $rows = explode(".", $layoutConfig);
    echo "Layout rows: " . count($rows) . "\n";
    
    $expectedPositions = 0;
    foreach ($rows as $i => $cols) {
        echo "  Row " . ($i + 1) . ": ";
        if ($cols == "12") {
            echo "1 column (full width)\n";
            $expectedPositions++;
        } else {
            $colss = explode(",", $cols);
            echo count($colss) . " columns\n";
            $expectedPositions += count($colss);
        }
    }
    echo "\nTotal widget positions needed: {$expectedPositions}\n";
}

// Get all widgets for this user
echo "\n<strong>Active Widgets for {$tipeUser}:</strong>\n";
$widgets = ORM::for_table('tbl_widgets')
    ->where('enabled', 1)
    ->where('user', $tipeUser)
    ->order_by_asc('orders')
    ->findArray();

$pos = 1;
foreach ($widgets as $w) {
    $isFiber = ($w['widget'] == 'fiber_stats');
    $marker = $isFiber ? ' >>> ' : '     ';
    $w['position'] = $w['position'] ?? 1; // Default position if not set
    
    echo $marker . $w['widget'] . " (order: " . $w['orders'] . ", position: " . $w['position'] . ")\n";
    $pos++;
}

// Check if fiber_stats has position assigned
$fiberWidget = ORM::for_table('tbl_widgets')
    ->where('widget', 'fiber_stats')
    ->where('user', $tipeUser)
    ->find_one();

if ($fiberWidget) {
    echo "\n<strong>fiber_stats widget details:</strong>\n";
    echo "  ID: " . $fiberWidget->id . "\n";
    echo "  User: '" . $fiberWidget->user . "'\n";
    echo "  Enabled: " . $fiberWidget->enabled . "\n";
    echo "  Order: " . $fiberWidget->orders . "\n";
    echo "  Position: " . ($fiberWidget->position ?? "NULL") . "\n";
    
    // Fix position if not set
    if (empty($fiberWidget->position)) {
        echo "\n⚠ Position is empty! Fixing...\n";
        $fiberWidget->position = 1;
        $fiberWidget->save();
        echo "✓ Position set to 1\n";
    }
}

echo "\n<strong>Fix Applied:</strong> All fiber_stats widgets now have position=1\n";

// Fix all fiber_stats widgets to have position 1
$brokenWidgets = ORM::for_table('tbl_widgets')
    ->where('widget', 'fiber_stats')
    ->find_many();

foreach ($brokenWidgets as $bw) {
    if (empty($bw->position)) {
        $bw->position = 1;
        $bw->save();
        echo "✓ Fixed widget ID {$bw->id} - position set to 1\n";
    }
}

echo "✓ All widgets updated\n";

echo "\n<a href='../?_route=dashboard' style='padding: 10px 20px; background: #337ab7; color: white; text-decoration: none; border-radius: 4px;'>View Dashboard</a>\n";

echo "</pre>";
