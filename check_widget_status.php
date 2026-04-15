<?php
/**
 * Check Widget Status and Fix Issues
 */
require_once('init.php');

if (!_admin(false)) {
    die('Login required');
}

echo '<h2>Widget Status Check & Fix</h2>';
echo '<style>body{font-family:Arial;padding:20px;} .ok{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;}</style>';

// Check 1: Database entry
echo '<h3>1. Database Check</h3>';
$widget = ORM::for_table('tbl_widgets')
    ->where('widget', 'online_users')
    ->where('user', 'Admin')
    ->find_one();

if ($widget) {
    echo '<p class="ok">✓ Widget exists in database (ID: ' . $widget->id . ')</p>';
    echo '<p>Status: ' . ($widget->enabled ? '<span class="ok">Enabled</span>' : '<span class="error">Disabled</span>') . '</p>';
    echo '<p>Position: ' . $widget->position . '</p>';
    echo '<p>Orders: ' . $widget->orders . '</p>';
    
    // Ensure it's enabled
    if (!$widget->enabled) {
        $widget->enabled = 1;
        $widget->save();
        echo '<p class="ok">✓ Widget enabled</p>';
    }
} else {
    echo '<p class="error">✗ Widget NOT in database - Adding now...</p>';
    $widget = ORM::for_table('tbl_widgets')->create();
    $widget->widget = 'online_users';
    $widget->name = 'Online Users';
    $widget->title = 'Online Users';
    $widget->user = 'Admin';
    $widget->enabled = 1;
    $widget->position = 1;
    $widget->orders = 1;
    $widget->content = '';
    $widget->save();
    echo '<p class="ok">✓ Widget added to database (ID: ' . $widget->id . ')</p>';
}

// Check 2: Files exist
echo '<h3>2. File Check</h3>';
global $root_path, $WIDGET_PATH;
$widgetFile = $WIDGET_PATH . DIRECTORY_SEPARATOR . 'online_users.php';
$templateFile = $root_path . 'ui/ui/widget/online_users.tpl';
$helperFile = $root_path . 'system/autoload/OnlineUsersHelper.php';

$filesOk = true;
if (file_exists($widgetFile)) {
    echo '<p class="ok">✓ Widget file: ' . htmlspecialchars($widgetFile) . '</p>';
} else {
    echo '<p class="error">✗ Widget file missing: ' . htmlspecialchars($widgetFile) . '</p>';
    $filesOk = false;
}

if (file_exists($templateFile)) {
    echo '<p class="ok">✓ Template file: ' . htmlspecialchars($templateFile) . '</p>';
} else {
    echo '<p class="error">✗ Template file missing: ' . htmlspecialchars($templateFile) . '</p>';
    $filesOk = false;
}

if (file_exists($helperFile)) {
    echo '<p class="ok">✓ Helper file: ' . htmlspecialchars($helperFile) . '</p>';
} else {
    echo '<p class="error">✗ Helper file missing: ' . htmlspecialchars($helperFile) . '</p>';
    $filesOk = false;
}

// Check 3: Test widget loading
echo '<h3>3. Widget Loading Test</h3>';
if ($filesOk) {
    try {
        require_once($widgetFile);
        if (class_exists('online_users')) {
            echo '<p class="ok">✓ Widget class loaded</p>';
            
            global $ui;
            $widgetInstance = new online_users();
            $testWidget = ['id' => $widget->id, 'widget' => 'online_users', 'position' => 1, 'orders' => 1];
            
            $content = $widgetInstance->getWidget($testWidget);
            
            if (!empty($content)) {
                echo '<p class="ok">✓ Widget generates content (' . strlen($content) . ' bytes)</p>';
                echo '<h4>Widget Preview:</h4>';
                echo '<div style="border:2px solid #4CAF50;padding:20px;background:#f9f9f9;margin:10px 0;">';
                echo $content;
                echo '</div>';
            } else {
                echo '<p class="error">✗ Widget returned empty content</p>';
            }
        } else {
            echo '<p class="error">✗ Widget class not found</p>';
        }
    } catch (Throwable $e) {
        echo '<p class="error">✗ Error loading widget: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
}

// Check 4: Dashboard layout
echo '<h3>4. Dashboard Layout</h3>';
$config = ORM::for_table('tbl_appconfig')
    ->where('setting', 'dashboard_Admin')
    ->find_one();

if ($config) {
    $layout = $config->value;
    echo '<p>Current layout: <strong>' . htmlspecialchars($layout) . '</strong></p>';
    
    // Check if layout includes position 1
    $rows = explode('.', $layout);
    $hasPos1 = false;
    foreach ($rows as $row) {
        if ($row == '12' || (strpos($row, ',') !== false && count(explode(',', $row)) > 0)) {
            $hasPos1 = true;
            break;
        }
    }
    
    if (!$hasPos1 && $layout != '12') {
        echo '<p class="info">⚠ Layout may not show position 1 widgets</p>';
        echo '<p>Recommended: Add "12" at the start (e.g., "12.' . htmlspecialchars($layout) . '")</p>';
    } else {
        echo '<p class="ok">✓ Layout should display position 1 widgets</p>';
    }
} else {
    echo '<p class="info">⚠ No dashboard layout configured</p>';
    echo '<p>Creating default layout...</p>';
    $config = ORM::for_table('tbl_appconfig')->create();
    $config->setting = 'dashboard_Admin';
    $config->value = '12';
    $config->save();
    echo '<p class="ok">✓ Default layout created: 12 (Full width)</p>';
}

echo '<hr>';
echo '<h3>Summary</h3>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ol>';
echo '<li><a href="?_route=dashboard" target="_blank">Go to Dashboard</a> - The widget should appear at the top!</li>';
echo '<li>If it still doesn\'t show, clear browser cache (Ctrl+F5)</li>';
echo '<li>Check browser console (F12) for errors</li>';
echo '</ol>';

echo '<hr>';
echo '<p><small>Delete this file (check_widget_status.php) after verifying.</small></p>';
