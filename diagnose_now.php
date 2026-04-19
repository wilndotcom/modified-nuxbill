<?php
/**
 * Post-Login Diagnostic
 */

include 'init.php';

echo "<h2>Current Status Diagnostic</h2>";
echo "<hr>";

// 1. Check current user
echo "<h3>1. Current User</h3>";
$uid = User::getID();
if ($uid) {
    echo "Session User ID: $uid<br>";
    $user = User::_info();
    if ($user && $user->id) {
        echo "User loaded: YES<br>";
        echo "Username: {$user->username}<br>";
        echo "Fullname: {$user->fullname}<br>";
    } else {
        echo "User loaded: NO (User::_info() returned false)<br>";
        
        // Try to load manually
        $manual = ORM::for_table('tbl_customers')->find_one($uid);
        if ($manual && $manual->id) {
            echo "Manual DB load: SUCCESS<br>";
            echo "Username from DB: {$manual->username}<br>";
        } else {
            echo "Manual DB load: FAILED - User ID $uid not found<br>";
            echo "<span style='color:red'>DATABASE ISSUE: User doesn't exist!</span><br>";
        }
    }
} else {
    echo "<span style='color:red'>NOT LOGGED IN</span><br>";
}
echo "<hr>";

// 2. Check all users in database
echo "<h3>2. Database Check</h3>";
$all_users = ORM::for_table('tbl_customers')->find_many();
echo "Total users in database: " . count($all_users) . "<br>";
if (count($all_users) > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Status</th></tr>";
    foreach ($all_users as $u) {
        $is_current = ($u->id == $uid) ? ' <b>(YOU)</b>' : '';
        echo "<tr><td>{$u->id}</td><td>{$u->username}</td><td>{$u->status}{$is_current}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<span style='color:red'>NO USERS IN DATABASE!</span><br>";
}
echo "<hr>";

// 3. Controller check
echo "<h3>3. Controller File Check</h3>";
$controller = 'system/controllers/customer_ticket.php';
echo "File exists: " . (file_exists($controller) ? 'YES' : 'NO') . "<br>";
echo "File readable: " . (is_readable($controller) ? 'YES' : 'NO') . "<br>";
if (file_exists($controller)) {
    echo "File size: " . filesize($controller) . " bytes<br>";
    echo "Last modified: " . date('Y-m-d H:i:s', filemtime($controller)) . "<br>";
}
echo "<hr>";

// 4. Test links
echo "<h3>4. Test Links</h3>";
if ($uid) {
    echo "<a href='?_route=customer_ticket/list'>Test Support Tickets</a><br><br>";
    echo "<a href='?_route=customer_ticket/list&force=1'>Test with force parameter</a><br><br>";
} else {
    echo "<a href='?_route=login'>Go to Login</a><br>";
}
echo "<hr>";

// 5. Instructions
echo "<h3>5. What to do:</h3>";
if (!$uid) {
    echo "<p><b>You are not logged in.</b></p>";
    echo "<ol>";
    echo "<li><a href='?_route=login'>Login here</a></li>";
    echo "<li>Come back to this page</li>";
    echo "</ol>";
} elseif (!$user || !$user->id) {
    echo "<p><b>Problem identified:</b> Your session has user ID $uid but this user doesn't exist in the database.</p>";
    echo "<p><b>Solution:</b></p>";
    echo "<ol>";
    echo "<li>You need to create a user with ID $uid in the database, OR</li>";
    echo "<li>Clear session and login with an existing user (see table above for valid users)</li>";
    echo "</ol>";
} else {
    echo "<p style='color:green'><b>User is authenticated correctly!</b></p>";
    echo "<p>Click the Test Support Tickets link above to check if it works now.</p>";
}
