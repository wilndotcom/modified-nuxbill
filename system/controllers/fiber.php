<?php

/**
 * Fiber Management Controller
 * 
 * Handles OLT Devices, ONU Management, CPE Routers, Profiles
 */

_admin();
$ui->assign('_title', Lang::T('Fiber Management'));
$ui->assign('_system_menu', 'fiber');

$action = $routes['1'] ?? 'olt-devices';
$ui->assign('_admin', $admin);

// Check if user has admin access
if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin', 'Agent'])) {
    _alert(Lang::T('You do not have permission to access this page'), 'danger', 'dashboard');
}

switch ($action) {
    // OLT Devices
    case 'olt-devices':
        $devices = ORM::for_table('tbl_olt_devices')->find_array();
        $ui->assign('devices', $devices);
        $ui->display('admin/fiber/olt-devices.tpl');
        break;
        
    case 'olt-add':
        $ui->display('admin/fiber/olt-add.tpl');
        break;
        
    case 'olt-add-post':
        // Validate CSRF
        if ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
            _alert(Lang::T('Invalid CSRF token'), 'danger', 'fiber/olt-devices');
        }
        
        $name = _post('name');
        $brand = _post('brand');
        $ip_address = _post('ip_address');
        $port = _post('port', 161);
        $username = _post('username');
        $password = _post('password');
        $description = _post('description');
        $status = _post('status', 'Active');
        
        if (empty($name) || empty($brand) || empty($ip_address) || empty($username)) {
            _alert(Lang::T('Please fill in all required fields'), 'danger', 'fiber/olt-add');
        }
        
        $olt = ORM::for_table('tbl_olt_devices')->create();
        $olt->name = $name;
        $olt->brand = $brand;
        $olt->ip_address = $ip_address;
        $olt->port = $port;
        $olt->username = $username;
        $olt->password = $password;
        $olt->description = $description;
        $olt->status = $status;
        $olt->created_at = date('Y-m-d H:i:s');
        $olt->save();
        
        _log("OLT device added: {$name} ({$ip_address})", 'OLT');
        _alert(Lang::T('OLT Device added successfully'), 'success', 'fiber/olt-devices');
        break;
        
    case 'olt-edit':
        $id = $routes['2'] ?? 0;
        $device = ORM::for_table('tbl_olt_devices')->find_one($id);
        if (!$device) {
            _alert(Lang::T('OLT Device not found'), 'danger', 'fiber/olt-devices');
        }
        $ui->assign('device', $device->as_array());
        $ui->display('admin/fiber/olt-edit.tpl');
        break;
        
    case 'olt-edit-post':
        if ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
            _alert(Lang::T('Invalid CSRF token'), 'danger', 'fiber/olt-devices');
        }
        
        $id = _post('id');
        $device = ORM::for_table('tbl_olt_devices')->find_one($id);
        if (!$device) {
            _alert(Lang::T('OLT Device not found'), 'danger', 'fiber/olt-devices');
        }
        
        $device->name = _post('name');
        $device->brand = _post('brand');
        $device->ip_address = _post('ip_address');
        $device->port = _post('port', 161);
        $device->username = _post('username');
        if (!empty(_post('password'))) {
            $device->password = _post('password');
        }
        $device->description = _post('description');
        $device->status = _post('status', 'Active');
        $device->updated_at = date('Y-m-d H:i:s');
        $device->save();
        
        _log("OLT device updated: {$device->name}", 'OLT');
        _alert(Lang::T('OLT Device updated successfully'), 'success', 'fiber/olt-devices');
        break;
        
    case 'olt-delete':
        $id = $routes['2'] ?? 0;
        $device = ORM::for_table('tbl_olt_devices')->find_one($id);
        if ($device) {
            // Check if OLT has associated ONUs
            $onus = ORM::for_table('tbl_onus')->where('olt_id', $id)->count();
            if ($onus > 0) {
                _alert(Lang::T('Cannot delete OLT with associated ONUs'), 'danger', 'fiber/olt-devices');
            }
            
            _log("OLT device deleted: {$device->name}", 'OLT');
            $device->delete();
            _alert(Lang::T('OLT Device deleted successfully'), 'success', 'fiber/olt-devices');
        } else {
            _alert(Lang::T('OLT Device not found'), 'danger', 'fiber/olt-devices');
        }
        break;
    
    // ONU Management
    case 'onus':
        $olt_id = _get('olt_id');
        $query = ORM::for_table('tbl_onus')
            ->select('tbl_onus.*')
            ->select('tbl_olt_devices.name', 'olt_name')
            ->left_outer_join('tbl_olt_devices', array('tbl_onus.olt_id', '=', 'tbl_olt_devices.id'));
        
        if ($olt_id) {
            $query->where('tbl_onus.olt_id', $olt_id);
        }
        
        $onus = $query->find_array();
        $ui->assign('onus', $onus);
        $ui->assign('olt_id', $olt_id);
        
        // Get OLTs for filter dropdown
        $olts = ORM::for_table('tbl_olt_devices')->where('status', 'Active')->find_array();
        $ui->assign('olts', $olts);
        
        $ui->display('admin/fiber/onus.tpl');
        break;
        
    case 'onu-add':
        $olts = ORM::for_table('tbl_olt_devices')->where('status', 'Active')->find_array();
        $ui->assign('olts', $olts);
        
        $customers = ORM::for_table('tbl_customers')->find_array();
        $ui->assign('customers', $customers);
        
        $ui->display('admin/fiber/onu-add.tpl');
        break;
        
    case 'onu-add-post':
        if ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
            _alert(Lang::T('Invalid CSRF token'), 'danger', 'fiber/onus');
        }
        
        $olt_id = _post('olt_id');
        $serial_number = _post('serial_number');
        $customer_id = _post('customer_id');
        $onu_id = _post('onu_id');
        $pon_port = _post('pon_port', 1);
        $description = _post('description');
        $status = _post('status', 'Active');
        
        if (empty($olt_id) || empty($serial_number)) {
            _alert(Lang::T('Please fill in all required fields'), 'danger', 'fiber/onu-add');
        }
        
        // Check if serial number already exists
        $exists = ORM::for_table('tbl_onus')->where('serial_number', $serial_number)->find_one();
        if ($exists) {
            _alert(Lang::T('ONU with this serial number already exists'), 'danger', 'fiber/onu-add');
        }
        
        $onu = ORM::for_table('tbl_onus')->create();
        $onu->olt_id = $olt_id;
        $onu->serial_number = $serial_number;
        $onu->customer_id = $customer_id ?: null;
        $onu->onu_id = $onu_id;
        $onu->pon_port = $pon_port;
        $onu->description = $description;
        $onu->status = $status;
        $onu->created_at = date('Y-m-d H:i:s');
        $onu->save();
        
        _log("ONU added: {$serial_number}", 'ONU');
        _alert(Lang::T('ONU added successfully'), 'success', 'fiber/onus');
        break;
        
    case 'onu-edit':
        $id = $routes['2'] ?? 0;
        $onu = ORM::for_table('tbl_onus')->find_one($id);
        if (!$onu) {
            _alert(Lang::T('ONU not found'), 'danger', 'fiber/onus');
        }
        
        $ui->assign('onu', $onu->as_array());
        
        $olts = ORM::for_table('tbl_olt_devices')->where('status', 'Active')->find_array();
        $ui->assign('olts', $olts);
        
        $customers = ORM::for_table('tbl_customers')->find_array();
        $ui->assign('customers', $customers);
        
        $ui->display('admin/fiber/onu-edit.tpl');
        break;
        
    case 'onu-edit-post':
        if ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
            _alert(Lang::T('Invalid CSRF token'), 'danger', 'fiber/onus');
        }
        
        $id = _post('id');
        $onu = ORM::for_table('tbl_onus')->find_one($id);
        if (!$onu) {
            _alert(Lang::T('ONU not found'), 'danger', 'fiber/onus');
        }
        
        $onu->olt_id = _post('olt_id');
        $onu->serial_number = _post('serial_number');
        $onu->customer_id = _post('customer_id') ?: null;
        $onu->onu_id = _post('onu_id');
        $onu->pon_port = _post('pon_port', 1);
        $onu->description = _post('description');
        $onu->status = _post('status', 'Active');
        $onu->updated_at = date('Y-m-d H:i:s');
        $onu->save();
        
        _log("ONU updated: {$onu->serial_number}", 'ONU');
        _alert(Lang::T('ONU updated successfully'), 'success', 'fiber/onus');
        break;
        
    case 'onu-delete':
        $id = $routes['2'] ?? 0;
        $onu = ORM::for_table('tbl_onus')->find_one($id);
        if ($onu) {
            _log("ONU deleted: {$onu->serial_number}", 'ONU');
            $onu->delete();
            _alert(Lang::T('ONU deleted successfully'), 'success', 'fiber/onus');
        } else {
            _alert(Lang::T('ONU not found'), 'danger', 'fiber/onus');
        }
        break;
    
    // CPE Routers
    case 'cpe-routers':
        $query = ORM::for_table('tbl_cpe_routers')
            ->select('tbl_cpe_routers.*')
            ->select('tbl_customers.fullname', 'customer_name')
            ->select('tbl_customers.username', 'customer_username')
            ->select('tbl_onus.serial_number', 'onu_serial')
            ->left_outer_join('tbl_customers', array('tbl_cpe_routers.customer_id', '=', 'tbl_customers.id'))
            ->left_outer_join('tbl_onus', array('tbl_cpe_routers.onu_id', '=', 'tbl_onus.id'));
        
        $routers = $query->find_array();
        $ui->assign('routers', $routers);
        $ui->display('admin/fiber/cpe-routers.tpl');
        break;
        
    case 'cpe-add':
        $customers = ORM::for_table('tbl_customers')->find_array();
        $ui->assign('customers', $customers);
        
        $onus = ORM::for_table('tbl_onus')->where('status', 'Active')->find_array();
        $ui->assign('onus', $onus);
        
        $ui->display('admin/fiber/cpe-add.tpl');
        break;
        
    case 'cpe-add-post':
        if ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
            _alert(Lang::T('Invalid CSRF token'), 'danger', 'fiber/cpe-routers');
        }
        
        $customer_id = _post('customer_id');
        $onu_id = _post('onu_id') ?: null;
        $mac_address = _post('mac_address');
        $ip_address = _post('ip_address');
        $brand = _post('brand');
        $model = _post('model');
        $protocol = _post('protocol', 'HTTP');
        $username = _post('username');
        $password = _post('password');
        $status = _post('status', 'Active');
        
        if (empty($customer_id) || empty($mac_address) || empty($ip_address) || empty($brand)) {
            _alert(Lang::T('Please fill in all required fields'), 'danger', 'fiber/cpe-add');
        }
        
        // Validate MAC address format
        if (!preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $mac_address)) {
            _alert(Lang::T('Invalid MAC address format'), 'danger', 'fiber/cpe-add');
        }
        
        $router = ORM::for_table('tbl_cpe_routers')->create();
        $router->customer_id = $customer_id;
        $router->onu_id = $onu_id;
        $router->mac_address = strtoupper($mac_address);
        $router->ip_address = $ip_address;
        $router->brand = $brand;
        $router->model = $model;
        $router->protocol = $protocol;
        $router->username = $username;
        $router->password = $password;
        $router->status = $status;
        $router->created_at = date('Y-m-d H:i:s');
        $router->save();
        
        _log("CPE Router added: {$mac_address} for customer #{$customer_id}", 'CPE');
        _alert(Lang::T('CPE Router added successfully'), 'success', 'fiber/cpe-routers');
        break;
        
    case 'cpe-edit':
        $id = $routes['2'] ?? 0;
        $router = ORM::for_table('tbl_cpe_routers')->find_one($id);
        if (!$router) {
            _alert(Lang::T('CPE Router not found'), 'danger', 'fiber/cpe-routers');
        }
        
        $ui->assign('router', $router->as_array());
        
        $customers = ORM::for_table('tbl_customers')->find_array();
        $ui->assign('customers', $customers);
        
        $onus = ORM::for_table('tbl_onus')->where('status', 'Active')->find_array();
        $ui->assign('onus', $onus);
        
        $ui->display('admin/fiber/cpe-edit.tpl');
        break;
        
    case 'cpe-edit-post':
        if ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
            _alert(Lang::T('Invalid CSRF token'), 'danger', 'fiber/cpe-routers');
        }
        
        $id = _post('id');
        $router = ORM::for_table('tbl_cpe_routers')->find_one($id);
        if (!$router) {
            _alert(Lang::T('CPE Router not found'), 'danger', 'fiber/cpe-routers');
        }
        
        $router->customer_id = _post('customer_id');
        $router->onu_id = _post('onu_id') ?: null;
        $router->mac_address = strtoupper(_post('mac_address'));
        $router->ip_address = _post('ip_address');
        $router->brand = _post('brand');
        $router->model = _post('model');
        $router->protocol = _post('protocol');
        $router->username = _post('username');
        if (!empty(_post('password'))) {
            $router->password = _post('password');
        }
        $router->status = _post('status', 'Active');
        $router->updated_at = date('Y-m-d H:i:s');
        $router->save();
        
        _log("CPE Router updated: #{$id}", 'CPE');
        _alert(Lang::T('CPE Router updated successfully'), 'success', 'fiber/cpe-routers');
        break;
        
    case 'cpe-delete':
        $id = $routes['2'] ?? 0;
        $router = ORM::for_table('tbl_cpe_routers')->find_one($id);
        if ($router) {
            _log("CPE Router deleted: #{$id}", 'CPE');
            $router->delete();
            _alert(Lang::T('CPE Router deleted successfully'), 'success', 'fiber/cpe-routers');
        } else {
            _alert(Lang::T('CPE Router not found'), 'danger', 'fiber/cpe-routers');
        }
        break;
        
    case 'cpe-status':
        $id = $routes['2'] ?? 0;
        $router = ORM::for_table('tbl_cpe_routers')
            ->select('tbl_cpe_routers.*')
            ->select('tbl_customers.fullname', 'customer_name')
            ->left_outer_join('tbl_customers', array('tbl_cpe_routers.customer_id', '=', 'tbl_customers.id'))
            ->find_one($id);
        
        if (!$router) {
            _alert(Lang::T('CPE Router not found'), 'danger', 'fiber/cpe-routers');
        }
        
        $ui->assign('router', $router->as_array());
        
        // Try to get CPE status using driver
        $cpeStatus = null;
        $cpeClients = [];
        
        try {
            $driverFile = 'system/devices/cpe/' . $router->brand . '.php';
            if (!file_exists($driverFile)) {
                // Try alternative names
                $brandMap = [
                    'TP-Link' => 'TPLink',
                    'Huawei' => 'HuaweiCPE',
                    'ZTE' => 'ZTECPE',
                ];
                $driverClass = $brandMap[$router->brand] ?? $router->brand;
                $driverFile = 'system/devices/cpe/' . $driverClass . '.php';
            }
            
            if (file_exists($driverFile)) {
                require_once $driverFile;
                $driverClass = basename($driverFile, '.php');
                
                if (class_exists($driverClass)) {
                    $port = ($router->protocol == 'HTTPS') ? 443 : 80;
                    $cpe = new $driverClass($router->ip_address, $router->username, $router->password, $port, strtolower($router->protocol));
                    
                    if ($cpe->connect()) {
                        $cpeStatus = $cpe->getStatus();
                        $cpeClients = $cpe->getConnectedClients();
                        $cpe->disconnect();
                    }
                }
            }
        } catch (Throwable $e) {
            _log("CPE Status Check Error: " . $e->getMessage(), 'CPE');
        }
        
        $ui->assign('cpeStatus', $cpeStatus);
        $ui->assign('cpeClients', $cpeClients);
        $ui->display('admin/fiber/cpe-routers/status.tpl');
        break;
        
    case 'cpe-configure':
        $id = $routes['2'] ?? 0;
        $router = ORM::for_table('tbl_cpe_routers')
            ->select('tbl_cpe_routers.*')
            ->select('tbl_customers.fullname', 'customer_name')
            ->left_outer_join('tbl_customers', array('tbl_cpe_routers.customer_id', '=', 'tbl_customers.id'))
            ->find_one($id);
        
        if (!$router) {
            _alert(Lang::T('CPE Router not found'), 'danger', 'fiber/cpe-routers');
        }
        
        $ui->assign('router', $router->as_array());
        
        // Load existing config if any
        $config = json_decode($router->config ?? '{}', true);
        $ui->assign('config', $config);
        
        $ui->display('admin/fiber/cpe-routers/configure.tpl');
        break;
        
    case 'cpe-configure-post':
        if ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
            _alert(Lang::T('Invalid CSRF token'), 'danger', 'fiber/cpe-routers');
        }
        
        $id = $routes['2'] ?? 0;
        $router = ORM::for_table('tbl_cpe_routers')->find_one($id);
        if (!$router) {
            _alert(Lang::T('CPE Router not found'), 'danger', 'fiber/cpe-routers');
        }
        
        // Save configuration as JSON
        $config = [
            'wifi_2g_ssid' => _post('wifi_2g_ssid'),
            'wifi_2g_password' => _post('wifi_2g_password'),
            'wifi_2g_channel' => _post('wifi_2g_channel'),
            'wifi_5g_ssid' => _post('wifi_5g_ssid'),
            'wifi_5g_password' => _post('wifi_5g_password'),
            'wifi_5g_channel' => _post('wifi_5g_channel'),
            'wifi_security' => _post('wifi_security'),
            'lan_ip' => _post('lan_ip'),
            'lan_subnet' => _post('lan_subnet'),
            'dhcp_enabled' => _post('dhcp_enabled'),
            'dhcp_start' => _post('dhcp_start'),
            'dhcp_end' => _post('dhcp_end'),
            'admin_username' => _post('admin_username'),
            'remote_management' => _post('remote_management'),
            'tr069_acs_url' => _post('tr069_acs_url'),
            'firewall_spi' => _post('firewall_spi'),
            'wan_ping' => _post('wan_ping'),
        ];
        
        $router->config = json_encode($config);
        $router->updated_at = date('Y-m-d H:i:s');
        $router->save();
        
        _log("CPE Router configured: #{$id}", 'CPE');
        _alert(Lang::T('Configuration saved successfully'), 'success', 'fiber/cpe-configure/' . $id);
        break;
    
    // OLT Profiles
    case 'profiles':
        $profiles = ORM::for_table('tbl_olt_profiles')
            ->select('tbl_olt_profiles.*')
            ->select('tbl_olt_devices.name', 'olt_name')
            ->left_outer_join('tbl_olt_devices', array('tbl_olt_profiles.olt_id', '=', 'tbl_olt_devices.id'))
            ->find_array();
        $ui->assign('profiles', $profiles);
        $ui->display('admin/fiber/profiles.tpl');
        break;
        
    case 'profile-add':
        $olts = ORM::for_table('tbl_olt_devices')->where('status', 'Active')->find_array();
        $ui->assign('olts', $olts);
        $ui->display('admin/fiber/profile-add.tpl');
        break;
        
    case 'profile-add-post':
        if ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
            _alert(Lang::T('Invalid CSRF token'), 'danger', 'fiber/profiles');
        }
        
        $name = _post('name');
        $olt_id = _post('olt_id');
        $upload_speed = _post('upload_speed');
        $download_speed = _post('download_speed');
        $tcont = _post('tcont', 1);
        $gemport = _post('gemport', 1);
        $vlan = _post('vlan', 100);
        $description = _post('description');
        
        if (empty($name) || empty($olt_id)) {
            _alert(Lang::T('Please fill in all required fields'), 'danger', 'fiber/profile-add');
        }
        
        $profile = ORM::for_table('tbl_olt_profiles')->create();
        $profile->name = $name;
        $profile->olt_id = $olt_id;
        $profile->upload_speed = $upload_speed;
        $profile->download_speed = $download_speed;
        $profile->tcont = $tcont;
        $profile->gemport = $gemport;
        $profile->vlan = $vlan;
        $profile->description = $description;
        $profile->created_at = date('Y-m-d H:i:s');
        $profile->save();
        
        _log("OLT Profile added: {$name}", 'OLT');
        _alert(Lang::T('Profile added successfully'), 'success', 'fiber/profiles');
        break;
        
    case 'profile-edit':
        $id = $routes['2'] ?? 0;
        $profile = ORM::for_table('tbl_olt_profiles')->find_one($id);
        if (!$profile) {
            _alert(Lang::T('Profile not found'), 'danger', 'fiber/profiles');
        }
        
        $ui->assign('profile', $profile->as_array());
        
        $olts = ORM::for_table('tbl_olt_devices')->where('status', 'Active')->find_array();
        $ui->assign('olts', $olts);
        
        $ui->display('admin/fiber/profile-edit.tpl');
        break;
        
    case 'profile-edit-post':
        if ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
            _alert(Lang::T('Invalid CSRF token'), 'danger', 'fiber/profiles');
        }
        
        $id = _post('id');
        $profile = ORM::for_table('tbl_olt_profiles')->find_one($id);
        if (!$profile) {
            _alert(Lang::T('Profile not found'), 'danger', 'fiber/profiles');
        }
        
        $profile->name = _post('name');
        $profile->olt_id = _post('olt_id');
        $profile->upload_speed = _post('upload_speed');
        $profile->download_speed = _post('download_speed');
        $profile->tcont = _post('tcont', 1);
        $profile->gemport = _post('gemport', 1);
        $profile->vlan = _post('vlan', 100);
        $profile->description = _post('description');
        $profile->updated_at = date('Y-m-d H:i:s');
        $profile->save();
        
        _log("OLT Profile updated: {$profile->name}", 'OLT');
        _alert(Lang::T('Profile updated successfully'), 'success', 'fiber/profiles');
        break;
        
    case 'profile-delete':
        $id = $routes['2'] ?? 0;
        $profile = ORM::for_table('tbl_olt_profiles')->find_one($id);
        if ($profile) {
            _log("OLT Profile deleted: {$profile->name}", 'OLT');
            $profile->delete();
            _alert(Lang::T('Profile deleted successfully'), 'success', 'fiber/profiles');
        } else {
            _alert(Lang::T('Profile not found'), 'danger', 'fiber/profiles');
        }
        break;
    
    default:
        $ui->display('admin/404.tpl');
}
