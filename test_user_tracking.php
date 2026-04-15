<?php
/**
 * Quick Test - Verify User Tracking
 */
require_once('init.php');

if (!_admin(false)) {
    die('Login required');
}

echo '<h2>Quick User Tracking Test</h2>';
echo '<style>body{font-family:Arial;padding:20px;} .ok{color:green;font-weight:bold;} .error{color:red;} table{border-collapse:collapse;width:100%;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;} th{background:#4CAF50;color:white;}</style>';

try {
    // Get stats from helper
    $stats = OnlineUsersHelper::getOnlineUsersStats();
    
    echo '<h3>Current Online Users Statistics</h3>';
    echo '<table>';
    echo '<tr><th>User Type</th><th>Count</th><th>Source</th></tr>';
    echo '<tr>';
    echo '<td><strong>Hotspot Online</strong></td>';
    echo '<td style="font-size:1.5em;font-weight:bold;">' . ($stats['hotspot'] ?? 0) . '</td>';
    echo '<td>/ip/hotspot/active</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td><strong>PPPoE Online</strong></td>';
    echo '<td style="font-size:1.5em;font-weight:bold;">' . ($stats['pppoe'] ?? 0) . '</td>';
    echo '<td>/ppp/active (service=pppoe)</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td><strong>Static Online</strong></td>';
    echo '<td style="font-size:1.5em;font-weight:bold;">' . ($stats['static'] ?? 0) . '</td>';
    echo '<td>PPPoE with static profile/IP + IP bindings</td>';
    echo '</tr>';
    echo '<tr style="background:#f0f0f0;font-weight:bold;">';
    echo '<td><strong>Total Online</strong></td>';
    echo '<td style="font-size:1.8em;font-weight:bold;color:#4CAF50;">' . ($stats['total'] ?? 0) . '</td>';
    echo '<td>Sum (no double counting)</td>';
    echo '</tr>';
    echo '</table>';
    
    if (!empty($stats['error'])) {
        echo '<p class="error">⚠ Warning: ' . htmlspecialchars($stats['error']) . '</p>';
    } else {
        echo '<p class="ok">✓ Data fetched successfully</p>';
    }
    
    // Verify no double counting
    $calculatedTotal = ($stats['hotspot'] ?? 0) + ($stats['pppoe'] ?? 0) + ($stats['static'] ?? 0);
    if ($calculatedTotal == ($stats['total'] ?? 0)) {
        echo '<p class="ok">✓ Verification: Total matches sum (no double counting)</p>';
    } else {
        echo '<p class="error">✗ Verification failed: Total (' . ($stats['total'] ?? 0) . ') does not match sum (' . $calculatedTotal . ')</p>';
    }
    
    echo '<hr>';
    echo '<h3>How It Works</h3>';
    echo '<ul>';
    echo '<li><strong>Hotspot Users:</strong> Counted from <code>/ip/hotspot/active/print</code> - All active hotspot sessions</li>';
    echo '<li><strong>PPPoE Users:</strong> Counted from <code>/ppp/active/print</code> where <code>service=pppoe</code> - Excludes static users</li>';
    echo '<li><strong>Static Users:</strong> Detected from:<ul>';
    echo '<li>PPPoE sessions where customer has <code>service_type=Static</code> or static IP</li>';
    echo '<li>PPPoE sessions with profile name containing "static"</li>';
    echo '<li>Active IP bindings matching customers with static IP</li>';
    echo '</ul></li>';
    echo '<li><strong>Total:</strong> Sum of all three (static users removed from hotspot/pppoe counts to avoid double counting)</li>';
    echo '</ul>';
    
    echo '<hr>';
    echo '<p><a href="?_route=dashboard">Go to Dashboard</a> | ';
    echo '<a href="verify_user_tracking.php">Run Detailed Verification</a></p>';
    
} catch (Exception $e) {
    echo '<p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}
