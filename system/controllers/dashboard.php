<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

_admin();
$ui->assign('_title', Lang::T('Dashboard'));
$ui->assign('_admin', $admin);

if (isset($_GET['refresh'])) {
    $files = scandir($CACHE_PATH);
    foreach ($files as $file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (is_file($CACHE_PATH . DIRECTORY_SEPARATOR . $file) && $ext == 'temp') {
            unlink($CACHE_PATH . DIRECTORY_SEPARATOR . $file);
        }
    }
    r2(getUrl('dashboard'), 's', 'Data Refreshed');
}

// AJAX endpoint for online users refresh
if (isset($_GET['action']) && $_GET['action'] == 'online-users-refresh') {
    header('Content-Type: application/json');
    try {
        // Clear cache to force refresh
        OnlineUsersHelper::clearCache('online_users_all');
        $online_data = OnlineUsersHelper::getOnlineUsers();
        echo json_encode([
            'success' => true,
            'data' => [
                'total' => $online_data['total'],
                'hotspot' => count($online_data['hotspot']),
                'pppoe' => count($online_data['pppoe']),
                'static' => count($online_data['static']),
                'last_update' => date('Y-m-d H:i:s')
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Online Users Detailed List View
if (isset($routes[1]) && $routes[1] == 'online-users') {
    _admin();
    $user_type = isset($routes[2]) ? $routes[2] : 'all';
    
    try {
        $online_data = OnlineUsersHelper::getOnlineUsers();
        $online_users = [];
        
        // Add type to each user and filter if needed
        foreach ($online_data['hotspot'] as $user) {
            $user['type'] = 'hotspot';
            $online_users[] = $user;
        }
        foreach ($online_data['pppoe'] as $user) {
            $user['type'] = 'pppoe';
            $online_users[] = $user;
        }
        foreach ($online_data['static'] as $user) {
            $user['type'] = 'static';
            $online_users[] = $user;
        }
        
        // Filter by type if specified
        if ($user_type != 'all') {
            $online_users = array_filter($online_users, function($user) use ($user_type) {
                return $user['type'] == $user_type;
            });
        }
        
        $ui->assign('online_users', $online_users);
        $ui->assign('total_count', count($online_users));
        $ui->assign('user_type', $user_type);
        $ui->assign('last_update', date('Y-m-d H:i:s'));
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->assign('_title', Lang::T('Online Users'));
        $ui->display('admin/dashboard/online_users_list.tpl');
        exit;
    } catch (Exception $e) {
        _alert(Lang::T('Error loading online users: ') . $e->getMessage(), 'danger', 'dashboard');
    }
}

// Kick User AJAX endpoint
if (isset($_GET['action']) && $_GET['action'] == 'kick-user') {
    header('Content-Type: application/json');
    
    $csrf_token = _req('token');
    if (!Csrf::check($csrf_token)) {
        echo json_encode(['success' => false, 'error' => 'Invalid token']);
        exit;
    }
    
    $username = _req('username');
    $router_name = _req('router');
    
    if (empty($username) || empty($router_name)) {
        echo json_encode(['success' => false, 'error' => 'Missing parameters']);
        exit;
    }
    
    try {
        $router = ORM::for_table('tbl_routers')->where('name', $router_name)->find_one();
        if (!$router) {
            echo json_encode(['success' => false, 'error' => 'Router not found']);
            exit;
        }
        
        $client = Mikrotik::getClient($router->ip_address, $router->username, $router->password);
        
        // Try to remove from hotspot active
        $hotspot_users = $client->sendSync(new RouterOS\Request('/ip/hotspot/active/print'));
        foreach ($hotspot_users as $user) {
            if ($user->getProperty('user') == $username) {
                $removeReq = new RouterOS\Request('/ip/hotspot/active/remove');
                $removeReq->setArgument('numbers', $user->getProperty('.id'));
                $client->sendSync($removeReq);
                echo json_encode(['success' => true]);
                exit;
            }
        }
        
        // Try to remove from PPPoE active
        $pppoe_users = $client->sendSync(new RouterOS\Request('/ppp/active/print'));
        foreach ($pppoe_users as $user) {
            if ($user->getProperty('name') == $username) {
                $removeReq = new RouterOS\Request('/ppp/active/remove');
                $removeReq->setArgument('numbers', $user->getProperty('.id'));
                $client->sendSync($removeReq);
                echo json_encode(['success' => true]);
                exit;
            }
        }
        
        echo json_encode(['success' => false, 'error' => 'User not found in active sessions']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

$tipeUser = _req("user");
if (empty($tipeUser)) {
    $tipeUser = 'Admin';
}
$ui->assign('tipeUser', $tipeUser);

$reset_day = $config['reset_day'];
if (empty($reset_day)) {
    $reset_day = 1;
}
//first day of month
if (date("d") >= $reset_day) {
    $start_date = date('Y-m-' . $reset_day);
} else {
    $start_date = date('Y-m-' . $reset_day, strtotime("-1 MONTH"));
}

$current_date = date('Y-m-d');
$ui->assign('start_date', $start_date);
$ui->assign('current_date', $current_date);

// Add ticket statistics for Ticket Siren Widget
$high_priority_tickets = ORM::for_table('tbl_tickets')
    ->where('priority', 'High')
    ->where('status', 'Open')
    ->where_null('admin_read_at')
    ->count();
    
$medium_priority_tickets = ORM::for_table('tbl_tickets')
    ->where('priority', 'Medium')
    ->where('status', 'Open')
    ->where_null('admin_read_at')
    ->count();
    
$total_urgent_tickets = $high_priority_tickets + $medium_priority_tickets;

$ui->assign('high_priority_tickets', $high_priority_tickets);
$ui->assign('medium_priority_tickets', $medium_priority_tickets);
$ui->assign('total_urgent_tickets', $total_urgent_tickets);

$tipeUser = $admin['user_type'];
if (in_array($tipeUser, ['SuperAdmin', 'Admin'])) {
    $tipeUser = 'Admin';
}

$widgets = ORM::for_table('tbl_widgets')->where("enabled", 1)->where('user', $tipeUser)->order_by_asc("orders")->findArray();
$count = count($widgets);
for ($i = 0; $i < $count; $i++) {
    try{
        if(file_exists($WIDGET_PATH . DIRECTORY_SEPARATOR . $widgets[$i]['widget'].".php")){
            require_once $WIDGET_PATH . DIRECTORY_SEPARATOR . $widgets[$i]['widget'].".php";
            $widgets[$i]['content'] = (new $widgets[$i]['widget'])->getWidget($widgets[$i]);
        }else{
            $widgets[$i]['content'] = "Widget not found";
        }
    } catch (Throwable $e) {
        $widgets[$i]['content'] = $e->getMessage();
    }
}

$ui->assign('widgets', $widgets);
run_hook('view_dashboard'); #HOOK
$ui->display('admin/dashboard.tpl');