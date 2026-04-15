<?php
/**
 * Diagnostic script for Online Users Widget
 * Run this to check what's wrong
 */

require_once('init.php');

if (!_admin(false)) {
    die('You must be logged in as admin.');
}

echo '<h2>Online Users Widget Diagnostic</h2>';
echo '<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .error{color:red;} .info{color:blue;} table{border-collapse:collapse;width:100%;margin:10px 0;} td,th{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>';

$issues = [];
$checks = [];

// Check 1: Widget file exists
echo '<h3>1. Checking Widget Files</h3>';
$widgetFile = $root_path . 'system/widgets/online_users.php';
$templateFile = $root_path . 'ui/ui/widget/online_users.tpl';
$helperFile = $root_path . 'system/autoload/OnlineUsersHelper.php';

if (file_exists($widgetFile)) {
    echo '<p class="ok">✓ Widget file exists: system/widgets/online_users.php</p>';
    $checks['widget_file'] = true;
} else {
    echo '<p class="error">✗ Widget file NOT found: system/widgets/online_users.php</p>';
    $checks['widget_file'] = false;
    $issues[] = 'Widget PHP file missing';
}

if (file_exists($templateFile)) {
    echo '<p class="ok">✓ Template file exists: ui/ui/widget/online_users.tpl</p>';
    $checks['template_file'] = true;
} else {
    echo '<p class="error">✗ Template file NOT found: ui/ui/widget/online_users.tpl</p>';
    $checks['template_file'] = false;
    $issues[] = 'Template file missing';
}

if (file_exists($helperFile)) {
    echo '<p class="ok">✓ Helper file exists: system/autoload/OnlineUsersHelper.php</p>';
    $checks['helper_file'] = true;
} else {
    echo '<p class="error">✗ Helper file NOT found: system/autoload/OnlineUsersHelper.php</p>';
    $checks['helper_file'] = false;
    $issues[] = 'Helper file missing';
}

// Check 2: Database entry
echo '<h3>2. Checking Database Entry</h3>';
try {
    $widget = ORM::for_table('tbl_widgets')
        ->where('widget', 'online_users')
        ->where('user', 'Admin')
        ->find_one();
    
    if ($widget) {
        echo '<p class="ok">✓ Widget found in database</p>';
        echo '<table>';
        echo '<tr><th>Field</th><th>Value</th></tr>';
        echo '<tr><td>ID</td><td>' . $widget->id . '</td></tr>';
        echo '<tr><td>Widget</td><td>' . htmlspecialchars($widget->widget) . '</td></tr>';
        echo '<tr><td>Name</td><td>' . htmlspecialchars($widget->name) . '</td></tr>';
        echo '<tr><td>User</td><td>' . htmlspecialchars($widget->user) . '</td></tr>';
        echo '<tr><td>Enabled</td><td>' . ($widget->enabled ? '<span class="ok">Yes</span>' : '<span class="error">No</span>') . '</td></tr>';
        echo '<tr><td>Position</td><td>' . $widget->position . '</td></tr>';
        echo '<tr><td>Orders</td><td>' . $widget->orders . '</td></tr>';
        echo '</table>';
        
        if (!$widget->enabled) {
            $issues[] = 'Widget is disabled in database';
        }
        $checks['database'] = true;
    } else {
        echo '<p class="error">✗ Widget NOT found in database</p>';
        echo '<p class="info">Run this SQL to add it:</p>';
        echo '<pre style="background:#f5f5f5;padding:10px;border:1px solid #ddd;">';
        echo "INSERT INTO `tbl_widgets` (`widget`, `name`, `title`, `user`, `enabled`, `position`, `orders`, `content`) \n";
        echo "VALUES ('online_users', 'Online Users', 'Online Users', 'Admin', 1, 1, 999, '');";
        echo '</pre>';
        $checks['database'] = false;
        $issues[] = 'Widget not in database';
    }
} catch (Exception $e) {
    echo '<p class="error">✗ Database error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    $checks['database'] = false;
    $issues[] = 'Database error: ' . $e->getMessage();
}

// Check 3: Dashboard layout
echo '<h3>3. Checking Dashboard Layout</h3>';
try {
    $config = ORM::for_table('tbl_appconfig')
        ->where('setting', 'dashboard_Admin')
        ->find_one();
    
    if ($config) {
        echo '<p class="info">Current dashboard layout: <strong>' . htmlspecialchars($config->value) . '</strong></p>';
        echo '<p class="info">Make sure position 1 is included in your layout (e.g., "12" or "3,3,3,3")</p>';
    } else {
        echo '<p class="error">✗ Dashboard layout not configured</p>';
        echo '<p class="info">Go to Settings → Widgets → Dashboard Structure and configure it</p>';
        $issues[] = 'Dashboard layout not configured';
    }
} catch (Exception $e) {
    echo '<p class="error">✗ Error checking dashboard layout: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// Check 4: Test widget loading
echo '<h3>4. Testing Widget Loading</h3>';
if ($checks['widget_file'] && $checks['helper_file']) {
    try {
        require_once($widgetFile);
        if (class_exists('online_users')) {
            echo '<p class="ok">✓ Widget class can be loaded</p>';
            
            // Try to instantiate
            $widgetInstance = new online_users();
            if (method_exists($widgetInstance, 'getWidget')) {
                echo '<p class="ok">✓ Widget class has getWidget() method</p>';
                
                // Try to get widget content
                try {
                    $testWidget = ['id' => 999, 'widget' => 'online_users', 'position' => 1, 'orders' => 999];
                    $content = $widgetInstance->getWidget($testWidget);
                    if (!empty($content)) {
                        echo '<p class="ok">✓ Widget can generate content</p>';
                        echo '<p class="info">Content length: ' . strlen($content) . ' bytes</p>';
                    } else {
                        echo '<p class="error">✗ Widget returned empty content</p>';
                        $issues[] = 'Widget returns empty content';
                    }
                } catch (Exception $e) {
                    echo '<p class="error">✗ Error generating widget content: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '<pre style="background:#fee;padding:10px;border:1px solid #fcc;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                    $issues[] = 'Widget error: ' . $e->getMessage();
                }
            } else {
                echo '<p class="error">✗ Widget class missing getWidget() method</p>';
                $issues[] = 'Widget class missing getWidget() method';
            }
        } else {
            echo '<p class="error">✗ Widget class not found after loading file</p>';
            $issues[] = 'Widget class not found';
        }
    } catch (Exception $e) {
        echo '<p class="error">✗ Error loading widget: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre style="background:#fee;padding:10px;border:1px solid #fcc;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        $issues[] = 'Error loading widget: ' . $e->getMessage();
    }
} else {
    echo '<p class="error">✗ Cannot test widget loading - files missing</p>';
}

// Check 5: Check if widget appears in dashboard query
echo '<h3>5. Checking Dashboard Query</h3>';
try {
    $admin = Admin::_info();
    $tipeUser = $admin['user_type'];
    if (in_array($tipeUser, ['SuperAdmin', 'Admin'])) {
        $tipeUser = 'Admin';
    }
    
    $widgets = ORM::for_table('tbl_widgets')
        ->where("enabled", 1)
        ->where('user', $tipeUser)
        ->order_by_asc("orders")
        ->findArray();
    
    echo '<p class="info">Found ' . count($widgets) . ' enabled widgets for user type: ' . $tipeUser . '</p>';
    
    $found = false;
    foreach ($widgets as $w) {
        if ($w['widget'] == 'online_users') {
            $found = true;
            echo '<p class="ok">✓ Widget "online_users" is in the enabled widgets list</p>';
            break;
        }
    }
    
    if (!$found) {
        echo '<p class="error">✗ Widget "online_users" is NOT in the enabled widgets list</p>';
        $issues[] = 'Widget not in enabled widgets query';
    }
} catch (Exception $e) {
    echo '<p class="error">✗ Error checking dashboard query: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// Summary
echo '<h3>Summary</h3>';
if (empty($issues)) {
    echo '<p class="ok" style="font-size:18px;font-weight:bold;">✓ All checks passed! Widget should be working.</p>';
    echo '<p>If widget still doesn\'t appear:</p>';
    echo '<ul>';
    echo '<li>Clear browser cache and refresh dashboard</li>';
    echo '<li>Check browser console (F12) for JavaScript errors</li>';
    echo '<li>Check PHP error logs</li>';
    echo '<li>Make sure dashboard layout includes position 1</li>';
    echo '</ul>';
} else {
    echo '<p class="error" style="font-size:18px;font-weight:bold;">✗ Issues Found:</p>';
    echo '<ul>';
    foreach ($issues as $issue) {
        echo '<li class="error">' . htmlspecialchars($issue) . '</li>';
    }
    echo '</ul>';
}

echo '<hr>';
echo '<p><a href="?_route=dashboard">Go to Dashboard</a> | <a href="?_route=widgets&user=Admin">Go to Widgets Settings</a></p>';
echo '<p><small>Delete this file (diagnose_online_users_widget.php) after troubleshooting.</small></p>';
