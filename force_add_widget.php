<?php
/**
 * Force Add Online Users Widget
 * This will add the widget and ensure it's visible
 */
require_once('init.php');

if (!_admin(false)) {
    die('You must be logged in as admin.');
}

echo '<h2>Force Adding Online Users Widget</h2>';
echo '<style>body{font-family:Arial;padding:20px;} .ok{color:green;font-weight:bold;} .error{color:red;font-weight:bold;}</style>';

// Step 1: Add widget to database
$widget = ORM::for_table('tbl_widgets')
    ->where('widget', 'online_users')
    ->where('user', 'Admin')
    ->find_one();

if (!$widget) {
    $widget = ORM::for_table('tbl_widgets')->create();
    $widget->widget = 'online_users';
    $widget->name = 'Online Users';
    $widget->title = 'Online Users';
    $widget->user = 'Admin';
    $widget->enabled = 1;
    $widget->position = 1;
    $widget->orders = 1; // Set to 1 to appear first
    $widget->content = '';
    $widget->save();
    echo '<p class="ok">✓ Widget added to database (ID: ' . $widget->id . ')</p>';
} else {
    // Update to ensure it's enabled and in position 1
    $widget->enabled = 1;
    $widget->position = 1;
    $widget->orders = 1;
    $widget->save();
    echo '<p class="ok">✓ Widget updated (ID: ' . $widget->id . ')</p>';
}

// Step 2: Ensure dashboard layout includes position 1
$config = ORM::for_table('tbl_appconfig')
    ->where('setting', 'dashboard_Admin')
    ->find_one();

if (!$config) {
    // Create default layout
    $config = ORM::for_table('tbl_appconfig')->create();
    $config->setting = 'dashboard_Admin';
    $config->value = '12'; // Full width for position 1
    $config->save();
    echo '<p class="ok">✓ Dashboard layout created: 12 (Full width)</p>';
} else {
    $layout = $config->value;
    echo '<p>Current dashboard layout: <strong>' . htmlspecialchars($layout) . '</strong></p>';
    
    // Check if position 1 is included
    $rows = explode('.', $layout);
    $hasPosition1 = false;
    foreach ($rows as $row) {
        if ($row == '12' || strpos($row, ',') !== false) {
            $hasPosition1 = true;
            break;
        }
    }
    
    if (!$hasPosition1) {
        // Add position 1 to layout
        $newLayout = '12.' . $layout;
        $config->value = $newLayout;
        $config->save();
        echo '<p class="ok">✓ Dashboard layout updated to include position 1: ' . htmlspecialchars($newLayout) . '</p>';
    } else {
        echo '<p class="ok">✓ Dashboard layout already includes position 1</p>';
    }
}

// Step 3: Test widget loading
echo '<h3>Testing Widget</h3>';
global $WIDGET_PATH, $ui;
$widgetFile = $WIDGET_PATH . DIRECTORY_SEPARATOR . 'online_users.php';

if (file_exists($widgetFile)) {
    echo '<p class="ok">✓ Widget file exists</p>';
    
    try {
        require_once($widgetFile);
        if (class_exists('online_users')) {
            echo '<p class="ok">✓ Widget class loaded</p>';
            
            $widgetInstance = new online_users();
            $testWidget = ['id' => $widget->id, 'widget' => 'online_users', 'position' => 1, 'orders' => 1];
            $content = $widgetInstance->getWidget($testWidget);
            
            if (!empty($content)) {
                echo '<p class="ok">✓ Widget generates content (' . strlen($content) . ' bytes)</p>';
            } else {
                echo '<p class="error">✗ Widget returned empty content</p>';
            }
        } else {
            echo '<p class="error">✗ Widget class not found</p>';
        }
    } catch (Exception $e) {
        echo '<p class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
} else {
    echo '<p class="error">✗ Widget file not found: ' . htmlspecialchars($widgetFile) . '</p>';
}

echo '<hr>';
echo '<h3>Next Steps</h3>';
echo '<ol>';
echo '<li><a href="?_route=dashboard" target="_blank">Go to Dashboard</a> - The widget should now appear!</li>';
echo '<li>If it still doesn\'t show, clear your browser cache and refresh</li>';
echo '<li>Check browser console (F12) for any JavaScript errors</li>';
echo '</ol>';

echo '<hr>';
echo '<p><small>You can delete this file (force_add_widget.php) after verifying the widget appears.</small></p>';
