<?php
/**
 * Debt Notification Cron Job
 * Run this script daily to send debt notifications to customers
 * 
 * Recommended cron: 0 9 * * * (daily at 9 AM)
 * Or: 0 * /6 * * * (every 6 hours)
 */

require_once __DIR__ . '/../init.php';

echo "=== Debt Notification Cron ===\n";
echo "Started: " . date('Y-m-d H:i:s') . "\n\n";

// Check if debt notifications are enabled
$enabled = $config['debt_notifications_enabled'] ?? '1';
if ($enabled != '1') {
    echo "Debt notifications are disabled. Exiting.\n";
    exit(0);
}

// Get configuration
$channels = explode(',', $config['debt_notification_channels'] ?? 'SMS,WhatsApp,Email,Inbox');
$grace_days = intval($config['debt_grace_period_days'] ?? 7);
$warning_days = intval($config['debt_warning_days'] ?? 3);
$final_days = intval($config['debt_final_notice_days'] ?? 1);

// Message templates
$templates = [
    'initial' => $config['debt_message_initial'] ?? 'Dear [[name]], your account has a debt of [[amount]]. Please settle within [[days]] days to avoid disconnection.',
    'warning' => $config['debt_message_warning'] ?? 'URGENT: Dear [[name]], your debt of [[amount]] must be paid within [[days]] days. Your service will be disconnected after deadline.',
    'final' => $config['debt_message_final'] ?? 'FINAL NOTICE: Dear [[name]], your debt of [[amount]] must be paid by tomorrow. Immediate disconnection will occur after deadline.',
];

// Find customers with negative balance (debt)
$customers = ORM::for_table('tbl_customers')
    ->where_lt('balance', 0)
    ->find_many();

$notifications_sent = 0;
$errors = 0;

foreach ($customers as $customer) {
    $debt_amount = abs($customer->balance);
    $customer_id = $customer->id;
    
    // Check if active debt record exists
    $debt = ORM::for_table('tbl_customer_debt')
        ->where('customer_id', $customer_id)
        ->where_in('status', ['Active', 'Notified', 'Warning', 'Final'])
        ->find_one();
    
    if (!$debt) {
        // Create new debt record
        $debt = ORM::for_table('tbl_customer_debt')->create();
        $debt->customer_id = $customer_id;
        $debt->amount = $debt_amount;
        $debt->detected_date = date('Y-m-d H:i:s');
        $debt->deadline_date = date('Y-m-d H:i:s', strtotime("+$grace_days days"));
        $debt->status = 'Active';
        $debt->save();
        
        $debt_id = $debt->id;
        $days_remaining = $grace_days;
        $notification_type = 'Initial';
        $message = $templates['initial'];
    } else {
        // Update debt amount if changed
        if ($debt->amount != $debt_amount) {
            $debt->amount = $debt_amount;
            $debt->save();
        }
        
        $debt_id = $debt->id;
        $deadline = strtotime($debt->deadline_date);
        $now = time();
        $days_remaining = ceil(($deadline - $now) / (60 * 60 * 24));
        
        // Determine notification type based on days remaining
        if ($days_remaining <= $final_days && $debt->status != 'Final') {
            $notification_type = 'Final';
            $message = $templates['final'];
            $debt->status = 'Final';
        } elseif ($days_remaining <= $warning_days && $debt->status != 'Warning' && $debt->status != 'Final') {
            $notification_type = 'Warning';
            $message = $templates['warning'];
            $debt->status = 'Warning';
        } elseif ($debt->notification_count == 0) {
            $notification_type = 'Initial';
            $message = $templates['initial'];
            $debt->status = 'Notified';
        } else {
            // No notification needed at this time
            continue;
        }
        
        $debt->save();
    }
    
    // Prepare message
    $message = str_replace([
        '[[name]]',
        '[[amount]]',
        '[[days]]'
    ], [
        $customer->fullname,
        $config['currency_code'] . ' ' . number_format($debt_amount, 2),
        $days_remaining
    ], $message);
    
    // Send notifications through each channel
    foreach ($channels as $channel) {
        $channel = trim($channel);
        $sent = false;
        
        try {
            switch ($channel) {
                case 'SMS':
                    // Use existing SMS system
                    if (function_exists('sendSMS')) {
                        $sent = sendSMS($customer->phonenumber, $message);
                    }
                    break;
                    
                case 'WhatsApp':
                    // Use existing WhatsApp system
                    if (function_exists('sendWhatsapp')) {
                        $sent = sendWhatsapp($customer->phonenumber, $message);
                    }
                    break;
                    
                case 'Email':
                    // Use existing email system
                    if (!empty($customer->email) && function_exists('sendEmail')) {
                        $sent = sendEmail($customer->email, 'Debt Notification', $message);
                    }
                    break;
                    
                case 'Inbox':
                    // Send to customer inbox
                    $inbox = ORM::for_table('tbl_customers_inbox')->create();
                    $inbox->customer_id = $customer_id;
                    $inbox->date_created = date('Y-m-d H:i:s');
                    $inbox->subject = "Debt Notification - " . $notification_type;
                    $inbox->body = $message;
                    $inbox->from = 'System';
                    $inbox->save();
                    $sent = true;
                    break;
            }
            
            // Log notification
            if ($sent) {
                $notif = ORM::for_table('tbl_debt_notifications')->create();
                $notif->debt_id = $debt_id;
                $notif->customer_id = $customer_id;
                $notif->notification_type = $notification_type;
                $notif->channel = $channel;
                $notif->message_content = $message;
                $notif->sent_date = date('Y-m-d H:i:s');
                $notif->status = 'Sent';
                $notif->save();
            }
            
        } catch (Exception $e) {
            echo "Error sending $channel to customer $customer_id: " . $e->getMessage() . "\n";
            $errors++;
        }
    }
    
    // Update debt record
    $debt->notification_count = $debt->notification_count + 1;
    $debt->last_notification_date = date('Y-m-d H:i:s');
    $debt->save();
    
    $notifications_sent++;
    echo "Sent $notification_type notification to {$customer->fullname} (ID: $customer_id)\n";
}

echo "\n=== Summary ===\n";
echo "Customers processed: " . count($customers) . "\n";
echo "Notifications sent: $notifications_sent\n";
echo "Errors: $errors\n";
echo "Finished: " . date('Y-m-d H:i:s') . "\n";

// Log cron execution
_log("Debt notification cron executed. Sent: $notifications_sent, Errors: $errors", 'Cron');
