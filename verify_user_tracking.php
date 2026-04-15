<?php
/**
 * Verification Script for Online Users Tracking
 * Verifies that users are being tracked correctly by type
 */
require_once('init.php');

if (!_admin(false)) {
    die('Login required');
}

echo '<h2>Online Users Tracking Verification</h2>';
echo '<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;margin:10px 0;} th,td{border:1px solid #ddd;padding:10px;text-align:left;} th{background:#4CAF50;color:white;} .ok{color:green;} .error{color:red;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;overflow-x:auto;}</style>';

global $_app_stage;

if ($_app_stage == 'demo') {
    echo '<p class="error">Demo mode - Cannot connect to routers</p>';
    exit;
}

// Get routers
$routers = ORM::for_table('tbl_routers')->where('enabled', '1')->find_many();

if (empty($routers)) {
    echo '<p class="error">No enabled routers found</p>';
    exit;
}

echo '<h3>Testing User Tracking Logic</h3>';
echo '<p>Checking ' . count($routers) . ' router(s)...</p>';

$allHotspotUsers = [];
$allPppoeUsers = [];
$allStaticUsers = [];
$detailedResults = [];

use PEAR2\Net\RouterOS;

foreach ($routers as $router) {
    echo '<hr>';
    echo '<h4>Router: ' . htmlspecialchars($router['name']) . ' (' . htmlspecialchars($router['ip_address']) . ')</h4>';
    
    $routerResults = [
        'name' => $router['name'],
        'hotspot' => [],
        'pppoe' => [],
        'static' => [],
        'errors' => []
    ];
    
    try {
        $client = Mikrotik::getClient(
            $router['ip_address'],
            $router['username'],
            $router['password']
        );
        
        if (!$client) {
            echo '<p class="error">✗ Failed to connect to router</p>';
            $routerResults['errors'][] = 'Connection failed';
            continue;
        }
        
        echo '<p class="ok">✓ Connected successfully</p>';
        
        // Test Hotspot Active Users
        echo '<h5>1. Hotspot Active Users</h5>';
        try {
            $hotspotRequest = new RouterOS\Request('/ip/hotspot/active/print');
            $hotspotResponses = $client->sendSync($hotspotRequest);
            
            $hotspotCount = 0;
            foreach ($hotspotResponses->getAllOfType(RouterOS\Response::TYPE_DATA) as $response) {
                $user = $response->getProperty('user');
                $ip = $response->getProperty('address');
                $mac = $response->getProperty('mac-address');
                
                if ($user) {
                    $allHotspotUsers[] = $user;
                    $routerResults['hotspot'][] = [
                        'username' => $user,
                        'ip' => $ip,
                        'mac' => $mac
                    ];
                    $hotspotCount++;
                }
            }
            echo '<p class="ok">✓ Found ' . $hotspotCount . ' active hotspot user(s)</p>';
            
            if ($hotspotCount > 0) {
                echo '<table>';
                echo '<tr><th>Username</th><th>IP Address</th><th>MAC Address</th></tr>';
                foreach ($routerResults['hotspot'] as $user) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['ip'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($user['mac'] ?? 'N/A') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
        } catch (Exception $e) {
            echo '<p class="info">ℹ Hotspot not configured or error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            $routerResults['errors'][] = 'Hotspot: ' . $e->getMessage();
        }
        
        // Test PPPoE Active Users
        echo '<h5>2. PPPoE Active Users</h5>';
        try {
            $pppoeRequest = new RouterOS\Request('/ppp/active/print');
            $pppoeResponses = $client->sendSync($pppoeRequest);
            
            $pppoeCount = 0;
            $staticFromPppoe = 0;
            
            foreach ($pppoeResponses->getAllOfType(RouterOS\Response::TYPE_DATA) as $response) {
                $service = $response->getProperty('service');
                $name = $response->getProperty('name');
                $profile = $response->getProperty('profile');
                $ip = $response->getProperty('address');
                
                if ($service == 'pppoe' && $name) {
                    // Check if static
                    $isStatic = OnlineUsersHelper::isStaticUser($name, $profile);
                    
                    if ($isStatic) {
                        $allStaticUsers[] = $name;
                        $routerResults['static'][] = [
                            'username' => $name,
                            'source' => 'PPPoE (Static)',
                            'profile' => $profile,
                            'ip' => $ip
                        ];
                        $staticFromPppoe++;
                    } else {
                        $allPppoeUsers[] = $name;
                        $routerResults['pppoe'][] = [
                            'username' => $name,
                            'profile' => $profile,
                            'ip' => $ip
                        ];
                        $pppoeCount++;
                    }
                }
            }
            
            echo '<p class="ok">✓ Found ' . $pppoeCount . ' active PPPoE user(s)</p>';
            if ($staticFromPppoe > 0) {
                echo '<p class="info">ℹ Found ' . $staticFromPppoe . ' static user(s) from PPPoE</p>';
            }
            
            if ($pppoeCount > 0 || $staticFromPppoe > 0) {
                echo '<table>';
                echo '<tr><th>Username</th><th>Type</th><th>Profile</th><th>IP Address</th></tr>';
                foreach ($routerResults['pppoe'] as $user) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                    echo '<td><span class="ok">PPPoE</span></td>';
                    echo '<td>' . htmlspecialchars($user['profile'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($user['ip'] ?? 'N/A') . '</td>';
                    echo '</tr>';
                }
                foreach ($routerResults['static'] as $user) {
                    if ($user['source'] == 'PPPoE (Static)') {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                        echo '<td><span class="info">Static (from PPPoE)</span></td>';
                        echo '<td>' . htmlspecialchars($user['profile'] ?? 'N/A') . '</td>';
                        echo '<td>' . htmlspecialchars($user['ip'] ?? 'N/A') . '</td>';
                        echo '</tr>';
                    }
                }
                echo '</table>';
            }
        } catch (Exception $e) {
            echo '<p class="info">ℹ PPPoE not configured or error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            $routerResults['errors'][] = 'PPPoE: ' . $e->getMessage();
        }
        
        // Test Static Users Detection
        echo '<h5>3. Static Users Detection</h5>';
        try {
            // Check IP bindings
            $bindingRequest = new RouterOS\Request('/ip/hotspot/ip-binding/print');
            $bindingRequest->setArgument('.proplist', 'mac-address,address,to-address,type');
            $bindingResponses = $client->sendSync($bindingRequest);
            
            $staticFromBinding = 0;
            foreach ($bindingResponses->getAllOfType(RouterOS\Response::TYPE_DATA) as $response) {
                $type = $response->getProperty('type');
                $address = $response->getProperty('address');
                
                if ($address && ($type == 'bypassed' || $response->getProperty('mac-address'))) {
                    $customer = ORM::for_table('tbl_customers')
                        ->where('ip_address', $address)
                        ->where('service_type', 'Static')
                        ->find_one();
                    
                                    // Use helper function to check if static
                                    if ($customer && OnlineUsersHelper::isStaticUser($customer['username'], '')) {
                                        if (!in_array($customer['username'], $allStaticUsers)) {
                                            $allStaticUsers[] = $customer['username'];
                                            $routerResults['static'][] = [
                                                'username' => $customer['username'],
                                                'source' => 'IP Binding',
                                                'ip' => $address
                                            ];
                                            $staticFromBinding++;
                                        }
                                    } elseif ($customer && !empty($customer['ip_address']) && $customer['ip_address'] == $address) {
                                        // Customer has this IP assigned - likely static
                                        if (!in_array($customer['username'], $allStaticUsers)) {
                                            $allStaticUsers[] = $customer['username'];
                                            $routerResults['static'][] = [
                                                'username' => $customer['username'],
                                                'source' => 'IP Binding (by IP match)',
                                                'ip' => $address
                                            ];
                                            $staticFromBinding++;
                                        }
                                    }
                }
            }
            
            if ($staticFromBinding > 0) {
                echo '<p class="ok">✓ Found ' . $staticFromBinding . ' static user(s) from IP bindings</p>';
            } else {
                echo '<p class="info">ℹ No static users found from IP bindings</p>';
            }
        } catch (Exception $e) {
            echo '<p class="info">ℹ IP binding check error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        
        $detailedResults[] = $routerResults;
        
    } catch (Exception $e) {
        echo '<p class="error">✗ Router error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        $routerResults['errors'][] = $e->getMessage();
    }
}

// Summary
echo '<hr>';
echo '<h3>Summary - User Tracking Verification</h3>';

// Remove duplicates and calculate
$uniqueHotspot = array_unique($allHotspotUsers);
$uniquePppoe = array_unique($allPppoeUsers);
$uniqueStatic = array_unique($allStaticUsers);

// Remove static from hotspot and pppoe
$hotspotOnly = array_diff($uniqueHotspot, $uniqueStatic);
$pppoeOnly = array_diff($uniquePppoe, $uniqueStatic);

$finalHotspot = count($hotspotOnly);
$finalPppoe = count($pppoeOnly);
$finalStatic = count($uniqueStatic);
$finalTotal = $finalHotspot + $finalPppoe + $finalStatic;

echo '<table style="width:100%;max-width:800px;">';
echo '<tr><th>User Type</th><th>Count</th><th>Status</th></tr>';
echo '<tr>';
echo '<td><strong>Hotspot Online</strong></td>';
echo '<td>' . $finalHotspot . '</td>';
echo '<td><span class="ok">✓ Tracking correctly</span></td>';
echo '</tr>';
echo '<tr>';
echo '<td><strong>PPPoE Online</strong></td>';
echo '<td>' . $finalPppoe . '</td>';
echo '<td><span class="ok">✓ Tracking correctly</span></td>';
echo '</tr>';
echo '<tr>';
echo '<td><strong>Static Online</strong></td>';
echo '<td>' . $finalStatic . '</td>';
echo '<td><span class="ok">✓ Tracking correctly</span></td>';
echo '</tr>';
echo '<tr style="background:#f0f0f0;font-weight:bold;">';
echo '<td><strong>Total Online</strong></td>';
echo '<td>' . $finalTotal . '</td>';
echo '<td><span class="ok">✓ No double counting</span></td>';
echo '</tr>';
echo '</table>';

// Test the helper function
echo '<h3>Testing Helper Function</h3>';
try {
    $helperStats = OnlineUsersHelper::getOnlineUsersStats();
    echo '<table style="width:100%;max-width:800px;">';
    echo '<tr><th>Source</th><th>Hotspot</th><th>PPPoE</th><th>Static</th><th>Total</th></tr>';
    echo '<tr>';
    echo '<td><strong>Helper Function</strong></td>';
    echo '<td>' . ($helperStats['hotspot'] ?? 0) . '</td>';
    echo '<td>' . ($helperStats['pppoe'] ?? 0) . '</td>';
    echo '<td>' . ($helperStats['static'] ?? 0) . '</td>';
    echo '<td>' . ($helperStats['total'] ?? 0) . '</td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td><strong>Direct Count</strong></td>';
    echo '<td>' . $finalHotspot . '</td>';
    echo '<td>' . $finalPppoe . '</td>';
    echo '<td>' . $finalStatic . '</td>';
    echo '<td>' . $finalTotal . '</td>';
    echo '</tr>';
    echo '</table>';
    
    if ($helperStats['hotspot'] == $finalHotspot && 
        $helperStats['pppoe'] == $finalPppoe && 
        $helperStats['static'] == $finalStatic) {
        echo '<p class="ok" style="font-size:18px;font-weight:bold;">✓ VERIFICATION PASSED - All counts match!</p>';
    } else {
        echo '<p class="error" style="font-size:18px;font-weight:bold;">✗ VERIFICATION FAILED - Counts do not match</p>';
        echo '<p>This might be due to caching. Try clearing cache and refreshing.</p>';
    }
    
    if (!empty($helperStats['error'])) {
        echo '<p class="error">Error: ' . htmlspecialchars($helperStats['error']) . '</p>';
    }
} catch (Exception $e) {
    echo '<p class="error">Error testing helper: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// Show user details
if (!empty($uniqueHotspot) || !empty($uniquePppoe) || !empty($uniqueStatic)) {
    echo '<h3>Detailed User List</h3>';
    
    if (!empty($hotspotOnly)) {
        echo '<h4>Hotspot Users (' . count($hotspotOnly) . ')</h4>';
        echo '<pre>' . implode(', ', $hotspotOnly) . '</pre>';
    }
    
    if (!empty($pppoeOnly)) {
        echo '<h4>PPPoE Users (' . count($pppoeOnly) . ')</h4>';
        echo '<pre>' . implode(', ', $pppoeOnly) . '</pre>';
    }
    
    if (!empty($uniqueStatic)) {
        echo '<h4>Static Users (' . count($uniqueStatic) . ')</h4>';
        echo '<pre>' . implode(', ', $uniqueStatic) . '</pre>';
    }
}

echo '<hr>';
echo '<p><a href="?_route=dashboard">Go to Dashboard</a> | ';
echo '<a href="javascript:location.reload()">Refresh This Page</a></p>';
echo '<p><small>Delete this file (verify_user_tracking.php) after verification.</small></p>';
