<?php
/**
 * Quick script to add Online Users widget to database
 * Run this file once via browser: http://your-site/add_online_users_widget.php
 * Then delete this file for security
 */

// Include phpNuxBill initialization
require_once('init.php');

// Check if admin is logged in
if (!_admin(false)) {
    die('You must be logged in as admin to run this script.');
}

// Check if widget already exists
$existing = ORM::for_table('tbl_widgets')
    ->where('widget', 'online_users')
    ->where('user', 'Admin')
    ->find_one();

if ($existing) {
    echo '<h2>Widget already exists!</h2>';
    echo '<p>Widget ID: ' . $existing->id . '</p>';
    echo '<p>Status: ' . ($existing->enabled ? 'Enabled' : 'Disabled') . '</p>';
    echo '<p><a href="?_route=widgets&user=Admin">Go to Widgets Settings</a></p>';
} else {
    // Add widget to database
    $widget = ORM::for_table('tbl_widgets')->create();
    $widget->widget = 'online_users';
    $widget->name = 'Online Users';
    $widget->title = 'Online Users';
    $widget->user = 'Admin';
    $widget->enabled = 1;
    $widget->position = 1; // Top position
    $widget->orders = 999; // High order number (will appear last)
    $widget->content = '';
    
    try {
        $widget->save();
        echo '<h2 style="color: green;">✓ Widget added successfully!</h2>';
        echo '<p>Widget ID: ' . $widget->id . '</p>';
        echo '<p><strong>Next steps:</strong></p>';
        echo '<ol>';
        echo '<li><a href="?_route=widgets&user=Admin">Go to Widgets Settings</a></li>';
        echo '<li>Add the widget to your dashboard layout (e.g., add "3,3,3,3" for 4 columns)</li>';
        echo '<li>Save the dashboard structure</li>';
        echo '<li>Go to Dashboard to see the widget</li>';
        echo '</ol>';
    } catch (Exception $e) {
        echo '<h2 style="color: red;">✗ Error adding widget</h2>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
}

echo '<hr>';
echo '<p><small>You can delete this file (add_online_users_widget.php) after running it.</small></p>';
