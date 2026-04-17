<?php

/**
 * Online Users Widget
 * Displays real-time online user statistics from MikroTik routers
 */

// Admin check
if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
    return;
}

// Get online users data
try {
    $online_data = OnlineUsersHelper::getOnlineUsers();
    $ui->assign('online_count', $online_data['total']);
    $ui->assign('hotspot_count', count($online_data['hotspot']));
    $ui->assign('pppoe_count', count($online_data['pppoe']));
    $ui->assign('static_count', count($online_data['static']));
    $ui->assign('online_users', array_merge($online_data['hotspot'], $online_data['pppoe'], $online_data['static']));
    $ui->assign('last_update', date('Y-m-d H:i:s'));
} catch (Exception $e) {
    $ui->assign('online_count', 0);
    $ui->assign('hotspot_count', 0);
    $ui->assign('pppoe_count', 0);
    $ui->assign('static_count', 0);
    $ui->assign('online_users', []);
    $ui->assign('error', $e->getMessage());
}

// Run the widget
run_widget('Online Users');
