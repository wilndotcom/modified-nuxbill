<?php
/**
 * Customer Wallet Widget
 * Shows balance or debt prominently on dashboard
 */

class wallet
{
    public function getWidget()
    {
        global $ui, $_user, $_c;
        
        // Only show if balance feature is enabled
        if ($_c['enable_balance'] != 'yes') {
            return '';
        }
        
        $balance = $_user['balance'] ?? 0;
        $ui->assign('wallet_balance', $balance);
        $ui->assign('wallet_is_debt', $balance < 0);
        
        return $ui->fetch('widget/customers/wallet.tpl');
    }
}
