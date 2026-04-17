<?php
require_once('init.php');

$settings = [
    ['debt_notifications_enabled', '1'],
    ['debt_notification_channels', 'SMS,WhatsApp,Email,Inbox'],
    ['debt_grace_period_days', '7'],
    ['debt_auto_disconnect', '1'],
    ['debt_warning_days', '3'],
    ['debt_final_notice_days', '1'],
    ['debt_message_initial', 'Dear [[name]], your account has a debt of [[amount]]. Please settle within [[days]] days to avoid disconnection.'],
    ['debt_message_warning', 'URGENT: Dear [[name]], your debt of [[amount]] must be paid within [[days]] days. Your service will be disconnected after deadline.'],
    ['debt_message_final', 'FINAL NOTICE: Dear [[name]], your debt of [[amount]] must be paid by tomorrow. Immediate disconnection will occur after deadline.'],
    ['debt_message_disconnection', 'Dear [[name]], your service has been disconnected due to unpaid debt of [[amount]]. Please settle to restore service.'],
];

echo "Setting up debt notification configuration...\n\n";

foreach ($settings as $setting) {
    $existing = ORM::for_table('tbl_appconfig')
        ->where('setting', $setting[0])
        ->find_one();
    
    if (!$existing) {
        $d = ORM::for_table('tbl_appconfig')->create();
        $d->setting = $setting[0];
        $d->value = $setting[1];
        $d->save();
        echo "✓ Added: {$setting[0]}\n";
    } else {
        echo "⚠ Already exists: {$setting[0]}\n";
    }
}

echo "\n✓ Debt notification configuration complete!\n";
