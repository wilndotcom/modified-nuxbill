<?php

include "../init.php";
$lockFile = "$CACHE_PATH/router_monitor.lock";

if (!is_dir($CACHE_PATH)) {
    echo "Directory '$CACHE_PATH' does not exist. Exiting...\n";
    exit;
}

$lock = fopen($lockFile, 'c');

if ($lock === false) {
    echo "Failed to open lock file. Exiting...\n";
    exit;
}

if (!flock($lock, LOCK_EX | LOCK_NB)) {
    echo "Script is already running. Exiting...\n";
    fclose($lock);
    exit;
}


$isCli = true;
if (php_sapi_name() !== 'cli') {
    $isCli = false;
    echo "<pre>";
}
echo "PHP Time\t" . date('Y-m-d H:i:s') . "\n";
$res = ORM::raw_execute('SELECT NOW() AS WAKTU;');
$statement = ORM::get_last_statement();
$rows = [];
while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    echo "MYSQL Time\t" . $row['WAKTU'] . "\n";
}

$_c = $config;


$textExpired = Lang::getNotifText('expired');

$d = ORM::for_table('tbl_user_recharges')->where('status', 'on')->where_lte('expiration', date("Y-m-d"))->find_many();
echo "Found " . count($d) . " user(s)\n";
run_hook('cronjob'); #HOOK

foreach ($d as $ds) {
    try {
        $date_now = strtotime(date("Y-m-d H:i:s"));
        $expiration = strtotime($ds['expiration'] . ' ' . $ds['time']);
        echo $ds['expiration'] . " : " . ($isCli ? $ds['username'] : Lang::maskText($ds['username']));

        if ($date_now >= $expiration) {
            echo " : EXPIRED \r\n";

            // Fetch user recharge details
            $u = ORM::for_table('tbl_user_recharges')->where('id', $ds['id'])->find_one();
            if (!$u) {
                throw new Exception("User recharge record not found for ID: " . $ds['id']);
            }

            // Fetch customer details
            $c = ORM::for_table('tbl_customers')->where('id', $ds['customer_id'])->find_one();
            if (!$c) {
                $c = $u;
            }

            // Fetch plan details
            $p = ORM::for_table('tbl_plans')->where('id', $u['plan_id'])->find_one();
            if (!$p) {
                throw new Exception("Plan not found for ID: " . $u['plan_id']);
            }

            $dvc = Package::getDevice($p);
            if ($_app_stage != 'demo') {
                if (file_exists($dvc)) {
                    require_once $dvc;
                    (new $p['device'])->remove_customer($c, $p);
                } else {
                    throw new Exception("Cron error: Devices " . $p['device'] . "not found, cannot disconnect ".$c['username']."\n");
                }
            }

            // Send notification and update user status
            try {
                echo Message::sendPackageNotification(
                    $c,
                    $u['namebp'],
                    $p['price'],
                    Message::getMessageType($p['type'], $textExpired),
                    $config['user_notification_expired']
                ) . "\n";
                $u->status = 'off';
                $u->save();
            } catch (Throwable $e) {
                _log($e->getMessage());
                sendTelegram($e->getMessage());
                echo "Error: " . $e->getMessage() . "\n";
            }

            // Auto-renewal from deposit
            if ($config['enable_balance'] == 'yes' && $c['auto_renewal']) {
                [$bills, $add_cost] = User::getBills($ds['customer_id']);
                if ($add_cost != 0) {
                    $p['price'] += $add_cost;
                }

                if ($p && $c['balance'] >= $p['price']) {
                    if (Package::rechargeUser($ds['customer_id'], $ds['routers'], $p['id'], 'Customer', 'Balance')) {
                        Balance::min($ds['customer_id'], $p['price']);
                        echo "plan enabled: " . (string) $p['enabled'] . " | User balance: " . (string) $c['balance'] . " | price " . (string) $p['price'] . "\n";
                        echo "auto renewal Success\n";
                    } else {
                        echo "plan enabled: " . $p['enabled'] . " | User balance: " . $c['balance'] . " | price " . $p['price'] . "\n";
                        echo "auto renewal Failed\n";
                        Message::sendTelegram("FAILED RENEWAL #cron\n\n#u." . $c['username'] . " #buy #Hotspot \n" . $p['name_plan'] .
                            "\nRouter: " . $p['routers'] .
                            "\nPrice: " . $p['price']);
                    }
                } else {
                    echo "no renewal | plan enabled: " . (string) $p['enabled'] . " | User balance: " . (string) $c['balance'] . " | price " . (string) $p['price'] . "\n";
                }
            } else {
                echo "no renewal | balance" . $config['enable_balance'] . " auto_renewal " . $c['auto_renewal'] . "\n";
            }
        } else {
            echo " : ACTIVE \r\n";
        }
    } catch (Throwable $e) {
        // Catch any unexpected errors
        _log($e->getMessage());
        sendTelegram($e->getMessage());
        echo "Unexpected Error: " . $e->getMessage() . "\n";
    }
}

//Cek interim-update radiusrest
if ($config['frrest_interim_update'] != 0) {

    $r_a = ORM::for_table('rad_acct')
        ->whereRaw("BINARY acctstatustype = 'Start' OR acctstatustype = 'Interim-Update'")
        ->where_lte('dateAdded', date("Y-m-d H:i:s"))->find_many();

    foreach ($r_a as $ra) {
        $interval = $_c['frrest_interim_update'] * 60;
        $timeUpdate = strtotime($ra['dateAdded']) + $interval;
        $timeNow = strtotime(date("Y-m-d H:i:s"));
        if ($timeNow >= $timeUpdate) {
            $ra->acctstatustype = 'Stop';
            $ra->save();
        }
    }
}

if ($config['router_check']) {
    echo "Checking router status...\n";
    $routers = ORM::for_table('tbl_routers')->where('enabled', '1')->find_many();
    if (!$routers) {
        echo "No active routers found in the database.\n";
        flock($lock, LOCK_UN);
        fclose($lock);
        unlink($lockFile);
        exit;
    }

    $offlineRouters = [];
    $errors = [];

    foreach ($routers as $router) {
        // check if custom port
        if (strpos($router->ip_address, ':') === false) {
            $ip = $router->ip_address;
            $port = 8728;
        } else {
            [$ip, $port] = explode(':', $router->ip_address);
        }
        $isOnline = false;

        try {
            $timeout = 5;
            if (is_callable('fsockopen') && false === stripos(ini_get('disable_functions'), 'fsockopen')) {
                $fsock = @fsockopen($ip, $port, $errno, $errstr, $timeout);
                if ($fsock) {
                    fclose($fsock);
                    $isOnline = true;
                } else {
                    throw new Exception("Unable to connect to $ip on port $port using fsockopen: $errstr ($errno)");
                }
            } elseif (is_callable('stream_socket_client') && false === stripos(ini_get('disable_functions'), 'stream_socket_client')) {
                $connection = @stream_socket_client("$ip:$port", $errno, $errstr, $timeout);
                if ($connection) {
                    fclose($connection);
                    $isOnline = true;
                } else {
                    throw new Exception("Unable to connect to $ip on port $port using stream_socket_client: $errstr ($errno)");
                }
            } else {
                throw new Exception("Neither fsockopen nor stream_socket_client are enabled on the server.");
            }
        } catch (Exception $e) {
            _log($e->getMessage());
            $errors[] = "Error with router $ip: " . $e->getMessage();
        }

        if ($isOnline) {
            $router->last_seen = date('Y-m-d H:i:s');
            $router->status = 'Online';
        } else {
            $router->status = 'Offline';
            $offlineRouters[] = $router;
        }

        $router->save();
    }

    if (!empty($offlineRouters)) {
        $message = "Dear Administrator,\n";
        $message .= "The following routers are offline:\n";
        foreach ($offlineRouters as $router) {
            $message .= "Name: {$router->name}, IP: {$router->ip_address}, Last Seen: {$router->last_seen}\n";
        }
        $message .= "\nPlease check the router's status and take appropriate action.\n\nBest regards,\nRouter Monitoring System";

        $adminEmail = $config['mail_from'];
        $subject = "Router Offline Alert";
        Message::SendEmail($adminEmail, $subject, $message);
        sendTelegram($message);
    }

    if (!empty($errors)) {
        $message = "The following errors occurred during router monitoring:\n";
        foreach ($errors as $error) {
            $message .= "$error\n";
        }

        $adminEmail = $config['mail_from'];
        $subject = "Router Monitoring Error Alert";
        Message::SendEmail($adminEmail, $subject, $message);
        sendTelegram($message);
    }
    echo "Router monitoring finished checking.\n";
}

// OLT Sync - Synchronize ONU status from OLT devices
echo "\nStarting OLT synchronization...\n";
try {
    $oltStartTime = microtime(true);
    
    // Get all active OLT devices
    $olts = ORM::for_table('tbl_olt_devices')
        ->where('status', 'Active')
        ->find_many();
    
    if ($olts && count($olts) > 0) {
        echo "Found " . count($olts) . " active OLT device(s)\n";
        
        $updatedOnus = 0;
        $errorOlts = 0;
        
        foreach ($olts as $olt) {
            echo "  Processing OLT: {$olt->name}... ";
            
            try {
                // Load OLT driver
                $driverFile = 'system/devices/olt/' . strtolower($olt->brand) . '.php';
                $driverClass = $olt->brand;
                
                if (!file_exists($driverFile)) {
                    $driverFile = 'system/devices/olt/generic_snmp.php';
                    $driverClass = 'GenericSNMP';
                }
                
                if (file_exists($driverFile)) {
                    require_once $driverFile;
                    
                    if (class_exists($driverClass)) {
                        $oltDriver = new $driverClass($olt->ip_address, $olt->port, $olt->username, $olt->password);
                        
                        if ($oltDriver->connect()) {
                            // Update OLT last seen
                            $olt->last_seen = date('Y-m-d H:i:s');
                            $olt->save();
                            
                            // Get ONUs from this OLT
                            $onus = ORM::for_table('tbl_onus')
                                ->where('olt_id', $olt->id)
                                ->find_many();
                            
                            // Get ONU list from OLT
                            $oltOnus = $oltDriver->getOnuList();
                            
                            if ($oltOnus !== false) {
                                foreach ($onus as $onu) {
                                    $onuFound = false;
                                    
                                    foreach ($oltOnus as $oltOnu) {
                                        if ($oltOnu['serial_number'] == $onu->serial_number || 
                                            $oltOnu['onu_id'] == $onu->onu_id) {
                                            
                                            $onuFound = true;
                                            $oldStatus = $onu->status;
                                            $newStatus = $oltOnu['status'];
                                            
                                            $onu->status = $newStatus;
                                            $onu->signal_level = $oltOnu['signal_level'] ?? null;
                                            $onu->distance = $oltOnu['distance'] ?? null;
                                            $onu->last_seen = date('Y-m-d H:i:s');
                                            $onu->save();
                                            
                                            $updatedOnus++;
                                            break;
                                        }
                                    }
                                    
                                    if (!$onuFound && $onu->status != 'Offline') {
                                        $onu->status = 'Offline';
                                        $onu->save();
                                        $updatedOnus++;
                                    }
                                }
                            }
                            
                            $oltDriver->disconnect();
                            echo "OK ({$updatedOnus} ONUs)\n";
                        } else {
                            echo "Failed (Connection error)\n";
                            $errorOlts++;
                        }
                    } else {
                        echo "Failed (Driver class not found)\n";
                        $errorOlts++;
                    }
                } else {
                    echo "Skipped (No driver)\n";
                    $errorOlts++;
                }
            } catch (Throwable $e) {
                echo "Error: " . $e->getMessage() . "\n";
                _log("OLT Sync Error for {$olt->name}: " . $e->getMessage(), 'OLT');
                $errorOlts++;
            }
        }
        
        $oltDuration = round(microtime(true) - $oltStartTime, 2);
        echo "OLT sync completed in {$oltDuration}s ({$updatedOnus} ONUs updated, {$errorOlts} errors)\n";
    } else {
        echo "No active OLT devices found.\n";
    }
} catch (Throwable $e) {
    echo "OLT sync error: " . $e->getMessage() . "\n";
    _log("OLT Sync Cron Error: " . $e->getMessage(), 'OLT');
}

flock($lock, LOCK_UN);
fclose($lock);
unlink($lockFile);

$timestampFile = "$UPLOAD_PATH/cron_last_run.txt";
file_put_contents($timestampFile, time());

run_hook('cronjob_end'); #HOOK
echo "\nCron job finished and completed successfully.\n";