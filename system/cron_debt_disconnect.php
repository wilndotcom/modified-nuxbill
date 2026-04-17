<?php
/**
 * Debt Disconnection Cron Job
 * Run this script to disconnect customers with overdue debt
 * 
 * Recommended cron: 0 * * * * (every hour)
 * Or: * /30 * * * * (every 30 minutes)
 */

require_once __DIR__ . '/../init.php';

echo "=== Debt Disconnection Cron ===\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n\n";

// Check if auto disconnection is enabled
$enabled = $config['debt_auto_disconnect'] ?? '1';
if ($enabled != '1') {
    echo "Auto disconnection is disabled. Exiting.\n";
    exit(0);
}

// Find customers with overdue debt
$now = date('Y-m-d H:i:s');
$debts = ORM::for_table('tbl_customer_debt')
    ->where_in('status', ['Active', 'Notified', 'Warning', 'Final'])
    ->where_lt('deadline_date', $now)
    ->find_many();

$disconnected = 0;
$errors = 0;

foreach ($debts as $debt) {
    $customer_id = $debt->customer_id;
    
    // Get customer details
    $customer = ORM::for_table('tbl_customers')->find_one($customer_id);
    if (!$customer) {
        echo "Customer $customer_id not found. Skipping.\n";
        continue;
    }
    
    try {
        // Get active recharges for this customer
        $recharges = ORM::for_table('tbl_user_recharges')
            ->where('customer_id', $customer_id)
            ->where('status', 'on')
            ->find_many();
        
        foreach ($recharges as $recharge) {
            // Disconnect from router
            $router = ORM::for_table('tbl_routers')
                ->where('name', $recharge->routers)
                ->find_one();
            
            if ($router) {
                try {
                    // Remove from hotspot active
                    $client = Mikrotik::getClient($router->ip_address, $router->username, $router->password);
                    
                    if ($recharge->type == 'Hotspot') {
                        $client->sendSync(new RouterOS\Request('/ip/hotspot/active/remove') 
                            ->setArgument('numbers', $customer->username));
                    } elseif ($recharge->type == 'PPPOE') {
                        $client->sendSync(new RouterOS\Request('/ppp/active/remove') 
                            ->setArgument('numbers', $customer->pppoe_username));
                    }
                    
                } catch (Exception $e) {
                    echo "Router error for customer $customer_id: " . $e->getMessage() . "\n";
                }
            }
            
            // Update recharge status
            $recharge->status = 'off';
            $recharge->save();
        }
        
        // Update debt status
        $debt->status = 'Disconnected';
        $debt->disconnected_date = $now;
        $debt->save();
        
        // Update customer status
        $customer->status = 'Inactive';
        $customer->save();
        
        // Send disconnection notification
        $message = $config['debt_message_disconnection'] ?? 'Dear [[name]], your service has been disconnected due to unpaid debt of [[amount]]. Please settle to restore service.';
        $message = str_replace([
            '[[name]]',
            '[[amount]]'
        ], [
            $customer->fullname,
            $config['currency_code'] . ' ' . number_format($debt->amount, 2)
        ], $message);
        
        // Log to inbox
        $inbox = ORM::for_table('tbl_customers_inbox')->create();
        $inbox->customer_id = $customer_id;
        $inbox->date_created = $now;
        $inbox->subject = "Service Disconnected - Debt Overdue";
        $inbox->body = $message;
        $inbox->from = 'System';
        $inbox->save();
        
        // Log notification
        $notif = ORM::for_table('tbl_debt_notifications')->create();
        $notif->debt_id = $debt->id;
        $notif->customer_id = $customer_id;
        $notif->notification_type = 'Disconnection';
        $notif->channel = 'Inbox';
        $notif->message_content = $message;
        $notif->sent_date = $now;
        $notif->status = 'Sent';
        $notif->save();
        
        $disconnected++;
        echo "Disconnected {$customer->fullname} (ID: $customer_id)\n";
        
        // Log action
        _log("Customer $customer_id disconnected due to overdue debt: " . $debt->amount, 'Debt');
        
    } catch (Exception $e) {
        echo "Error disconnecting customer $customer_id: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n=== Summary ===\n";
echo "Overdue debts found: " . count($debts) . "\n";
echo "Customers disconnected: $disconnected\n";
echo "Errors: $errors\n";
echo "Finished: " . date('Y-m-d H:i:s') . "\n";

// Log cron execution
_log("Debt disconnection cron executed. Disconnected: $disconnected, Errors: $errors", 'Cron');
