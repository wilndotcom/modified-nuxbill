<?php
/**
 * Manual test - Add widget directly and test
 */
require_once('init.php');

if (!_admin(false)) {
    die('Login required');
}

echo '<h2>Manual Widget Test</h2>';

// Step 1: Add widget if not exists
$existing = ORM::for_table('tbl_widgets')
    ->where('widget', 'online_users')
    ->where('user', 'Admin')
    ->find_one();

if (!$existing) {
    $widget = ORM::for_table('tbl_widgets')->create();
    $widget->widget = 'online_users';
    $widget->name = 'Online Users';
    $widget->title = 'Online Users';
    $widget->user = 'Admin';
    $widget->enabled = 1;
    $widget->position = 1;
    $widget->orders = 999;
    $widget->content = '';
    $widget->save();
    echo '<p style="color:green;">✓ Widget added to database (ID: ' . $widget->id . ')</p>';
} else {
    echo '<p style="color:blue;">Widget already exists (ID: ' . $existing->id . ')</p>';
    if (!$existing->enabled) {
        $existing->enabled = 1;
        $existing->save();
        echo '<p style="color:green;">✓ Widget enabled</p>';
    }
}

// Step 2: Test widget loading
echo '<h3>Testing Widget Loading</h3>';
global $WIDGET_PATH, $ui;
$widgetFile = $WIDGET_PATH . DIRECTORY_SEPARATOR . 'online_users.php';

if (file_exists($widgetFile)) {
    echo '<p style="color:green;">✓ Widget file exists</p>';
    
    try {
        require_once($widgetFile);
        
        if (class_exists('online_users')) {
            echo '<p style="color:green;">✓ Widget class loaded</p>';
            
            $widgetInstance = new online_users();
            $testWidget = ['id' => 999, 'widget' => 'online_users', 'position' => 1, 'orders' => 999];
            
            $content = $widgetInstance->getWidget($testWidget);
            
            if (!empty($content)) {
                echo '<p style="color:green;">✓ Widget content generated (' . strlen($content) . ' bytes)</p>';
                echo '<hr><h3>Widget Output Preview:</h3>';
                echo '<div style="border:2px solid #ccc;padding:20px;background:#f9f9f9;">';
                echo $content;
                echo '</div>';
            } else {
                echo '<p style="color:red;">✗ Widget returned empty content</p>';
            }
        } else {
            echo '<p style="color:red;">✗ Widget class not found after loading</p>';
        }
    } catch (Throwable $e) {
        echo '<p style="color:red;">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre style="background:#fee;padding:10px;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
} else {
    echo '<p style="color:red;">✗ Widget file not found: ' . htmlspecialchars($widgetFile) . '</p>';
}

// Step 3: Check dashboard layout
echo '<h3>Dashboard Layout Check</h3>';
$config = ORM::for_table('tbl_appconfig')
    ->where('setting', 'dashboard_Admin')
    ->find_one();

if ($config) {
    echo '<p>Current layout: <strong>' . htmlspecialchars($config->value) . '</strong></p>';
    echo '<p>To show widgets in position 1, your layout should include position 1.</p>';
    echo '<p>Example layouts:</p>';
    echo '<ul>';
    echo '<li><code>12</code> - Full width (shows position 1)</li>';
    echo '<li><code>3,3,3,3</code> - 4 columns (shows positions 1,2,3,4)</li>';
    echo '<li><code>12.3,3,3,3</code> - Full width then 4 columns</li>';
    echo '</ul>';
} else {
    echo '<p style="color:orange;">⚠ Dashboard layout not configured. Go to Settings → Widgets to configure.</p>';
}

echo '<hr>';
echo '<p><a href="?_route=dashboard">Go to Dashboard</a> | ';
echo '<a href="?_route=widgets&user=Admin">Go to Widget Settings</a></p>';
