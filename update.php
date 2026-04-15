<?php
/**
 * Database Update Script
 * Runs SQL migrations from system/updates.json
 */

session_start();
require_once 'system/vendor/autoload.php';
require_once 'init.php';

// Check if admin is logged in
_admin();

$step = isset($_GET['step']) ? intval($_GET['step']) : 0;
$ui = new Smarty();
$ui->assign('_title', 'Database Update');
$ui->assign('_system_menu', 'settings');

// Get admin info
$admin = Admin::_info();
$ui->assign('_admin', $admin);

// Check if SuperAdmin
if ($admin['user_type'] != 'SuperAdmin') {
    r2(getUrl('dashboard'), 'e', Lang::T('You do not have permission to access this page'));
}

$updates_file = $root_path . 'system/updates.json';
if (!file_exists($updates_file)) {
    die('Updates file not found: ' . $updates_file);
}

$updates = json_decode(file_get_contents($updates_file), true);
if (!$updates) {
    die('Invalid updates.json file');
}

// Get applied updates from database
$applied_updates = [];
try {
    $applied = ORM::for_table('tbl_appconfig')->where('setting', 'applied_updates')->find_one();
    if ($applied) {
        $applied_updates = json_decode($applied->value, true) ?: [];
    }
} catch (Exception $e) {
    // Table might not exist yet
}

// Find pending updates
$pending_updates = [];
foreach ($updates as $version => $queries) {
    if (!in_array($version, $applied_updates)) {
        $pending_updates[$version] = $queries;
    }
}

if ($step == 4) {
    // Run updates
    $success = 0;
    $failed = 0;
    $errors = [];
    
    foreach ($pending_updates as $version => $queries) {
        foreach ($queries as $query) {
            try {
                ORM::raw_execute($query);
                $success++;
            } catch (Exception $e) {
                $failed++;
                $errors[] = "Version $version: " . $e->getMessage();
            } catch (Throwable $e) {
                $failed++;
                $errors[] = "Version $version: " . $e->getMessage();
            }
        }
        
        // Mark version as applied
        $applied_updates[] = $version;
    }
    
    // Save applied updates
    try {
        $applied = ORM::for_table('tbl_appconfig')->where('setting', 'applied_updates')->find_one();
        if ($applied) {
            $applied->value = json_encode($applied_updates);
            $applied->save();
        } else {
            $applied = ORM::for_table('tbl_appconfig')->create();
            $applied->setting = 'applied_updates';
            $applied->value = json_encode($applied_updates);
            $applied->save();
        }
    } catch (Exception $e) {
        // Ignore if can't save
    }
    
    $ui->assign('success', $success);
    $ui->assign('failed', $failed);
    $ui->assign('errors', $errors);
    $ui->assign('applied_versions', array_keys($pending_updates));
    $ui->display('admin/update-result.tpl');
} else {
    // Show update page
    $ui->assign('pending_updates', $pending_updates);
    $ui->assign('applied_updates', $applied_updates);
    $ui->assign('total_pending', count($pending_updates));
    $ui->display('admin/update.tpl');
}
