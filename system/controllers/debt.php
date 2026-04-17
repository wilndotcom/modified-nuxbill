<?php

/**
 * Debt Notification Admin Controller
 * Manage debt notifications and settings
 */

_admin();
$ui->assign('_title', Lang::T('Debt Notifications'));
$ui->assign('_system_menu', 'debt');
$ui->assign('_admin', $admin);

$action = $routes['1'];
if (empty($action)) {
    $action = 'list';
}

switch ($action) {
    case 'list':
        // Show current debts
        $debts = ORM::for_table('tbl_customer_debt')
            ->select('tbl_customer_debt.*')
            ->select('tbl_customers.username', 'customer_username')
            ->select('tbl_customers.fullname', 'customer_fullname')
            ->select('tbl_customers.phonenumber', 'customer_phone')
            ->left_outer_join('tbl_customers', array('tbl_customer_debt.customer_id', '=', 'tbl_customers.id'))
            ->order_by_desc('tbl_customer_debt.detected_date')
            ->find_many();
        
        $ui->assign('debts', $debts);
        $ui->display('admin/debt/list.tpl');
        break;
        
    case 'settings':
        // Show/Edit settings
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Save settings
            $settings = [
                'debt_notifications_enabled' => _post('enabled', '0'),
                'debt_notification_channels' => implode(',', _post('channels', [])),
                'debt_grace_period_days' => _post('grace_days', '7'),
                'debt_auto_disconnect' => _post('auto_disconnect', '0'),
                'debt_warning_days' => _post('warning_days', '3'),
                'debt_final_notice_days' => _post('final_days', '1'),
                'debt_message_initial' => _post('message_initial', ''),
                'debt_message_warning' => _post('message_warning', ''),
                'debt_message_final' => _post('message_final', ''),
                'debt_message_disconnection' => _post('message_disconnection', ''),
            ];
            
            foreach ($settings as $key => $value) {
                $d = ORM::for_table('tbl_appconfig')->where('setting', $key)->find_one();
                if ($d) {
                    $d->value = $value;
                    $d->save();
                }
            }
            
            r2(getUrl('debt/settings'), 's', Lang::T('Settings saved successfully'));
        }
        
        // Load current settings
        $config_keys = [
            'debt_notifications_enabled',
            'debt_notification_channels',
            'debt_grace_period_days',
            'debt_auto_disconnect',
            'debt_warning_days',
            'debt_final_notice_days',
            'debt_message_initial',
            'debt_message_warning',
            'debt_message_final',
            'debt_message_disconnection',
        ];
        
        $settings = [];
        foreach ($config_keys as $key) {
            $d = ORM::for_table('tbl_appconfig')->where('setting', $key)->find_one();
            $settings[$key] = $d ? $d->value : '';
        }
        
        $ui->assign('settings', $settings);
        $ui->display('admin/debt/settings.tpl');
        break;
        
    case 'view':
        $id = $routes['2'];
        $debt = ORM::for_table('tbl_customer_debt')
            ->select('tbl_customer_debt.*')
            ->select('tbl_customers.username', 'customer_username')
            ->select('tbl_customers.fullname', 'customer_fullname')
            ->select('tbl_customers.phonenumber', 'customer_phone')
            ->select('tbl_customers.email', 'customer_email')
            ->left_outer_join('tbl_customers', array('tbl_customer_debt.customer_id', '=', 'tbl_customers.id'))
            ->where('tbl_customer_debt.id', $id)
            ->find_one();
        
        if (!$debt) {
            r2(getUrl('debt/list'), 'e', Lang::T('Debt record not found'));
        }
        
        // Get notification history
        $notifications = ORM::for_table('tbl_debt_notifications')
            ->where('debt_id', $id)
            ->order_by_desc('sent_date')
            ->find_many();
        
        $ui->assign('debt', $debt);
        $ui->assign('notifications', $notifications);
        $ui->display('admin/debt/view.tpl');
        break;
        
    default:
        r2(getUrl('debt/list'), 'e', Lang::T('Action not found'));
}
