<?php
require_once('init.php');

// Add Online Users widget if not exists
$existing = ORM::for_table('tbl_widgets')
    ->where('widget', 'online_users')
    ->where('user', 'Admin')
    ->find_one();

if (!$existing) {
    $widget = ORM::for_table('tbl_widgets')->create();
    $widget->widget = 'online_users';
    $widget->title = 'Online Users';
    $widget->user = 'Admin';
    $widget->enabled = 1;
    $widget->position = 1;
    $widget->orders = 12;
    $widget->content = '';
    $widget->save();
    echo "✓ Online Users widget added (ID: {$widget->id})\n";
} else {
    echo "✓ Widget already exists (ID: {$existing->id})\n";
}

// Check current widgets
$widgets = ORM::for_table('tbl_widgets')
    ->where('user', 'Admin')
    ->order_by_asc('orders')
    ->find_many();

echo "\nCurrent Admin widgets:\n";
foreach ($widgets as $w) {
    echo "  - {$w->title} ({$w->widget}) - " . ($w->enabled ? 'Enabled' : 'Disabled') . "\n";
}
