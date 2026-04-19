<?php

/**
 * Fiber Stats Widget
 * Displays OLT and ONU statistics on the dashboard
 */

class fiber_stats
{
    public function getWidget()
    {
        global $ui, $admin;
        
        // Admin check
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            return '';
        }
        
        try {
            // Get OLT statistics
            $totalOlts = ORM::for_table('tbl_olt_devices')->count();
            $activeOlts = ORM::for_table('tbl_olt_devices')->where('status', 'Active')->count();
            $offlineOlts = ORM::for_table('tbl_olt_devices')->where('status', 'Offline')->count();
            
            // Get ONU statistics
            $totalOnus = ORM::for_table('tbl_onus')->count();
            $activeOnus = ORM::for_table('tbl_onus')->where('status', 'Active')->count();
            $suspendedOnus = ORM::for_table('tbl_onus')->where('status', 'Suspended')->count();
            $inactiveOnus = ORM::for_table('tbl_onus')->where('status', 'Inactive')->count();
            $offlineOnus = ORM::for_table('tbl_onus')->where('status', 'Offline')->count();
            
            // Get CPE Router statistics
            $totalCpes = ORM::for_table('tbl_cpe_routers')->count();
            $activeCpes = ORM::for_table('tbl_cpe_routers')->where('status', 'Active')->count();
            $offlineCpes = ORM::for_table('tbl_cpe_routers')->where('status', 'Offline')->count();
            
            // Get recent OLT sync time
            $lastSyncOnu = ORM::for_table('tbl_onus')
                ->where_not_null('last_seen')
                ->order_by_desc('last_seen')
                ->find_one();
            $lastSyncTime = $lastSyncOnu ? $lastSyncOnu->last_seen : null;
            
            $ui->assign('total_olts', $totalOlts);
            $ui->assign('active_olts', $activeOlts);
            $ui->assign('offline_olts', $offlineOlts);
            $ui->assign('total_onus', $totalOnus);
            $ui->assign('active_onus', $activeOnus);
            $ui->assign('suspended_onus', $suspendedOnus);
            $ui->assign('inactive_onus', $inactiveOnus);
            $ui->assign('offline_onus', $offlineOnus);
            $ui->assign('total_cpes', $totalCpes);
            $ui->assign('active_cpes', $activeCpes);
            $ui->assign('offline_cpes', $offlineCpes);
            $ui->assign('last_sync_time', $lastSyncTime);
            
        } catch (Exception $e) {
            $ui->assign('total_olts', 0);
            $ui->assign('active_olts', 0);
            $ui->assign('offline_olts', 0);
            $ui->assign('total_onus', 0);
            $ui->assign('active_onus', 0);
            $ui->assign('suspended_onus', 0);
            $ui->assign('inactive_onus', 0);
            $ui->assign('offline_onus', 0);
            $ui->assign('total_cpes', 0);
            $ui->assign('active_cpes', 0);
            $ui->assign('offline_cpes', 0);
            $ui->assign('last_sync_time', null);
            $ui->assign('error', $e->getMessage());
        }
        
        return $ui->fetch('widget/fiber_stats.tpl');
    }
}
