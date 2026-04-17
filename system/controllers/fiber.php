<?php

/**
 * Fiber Management Controller
 * Handles OLT Devices, ONUs, Profiles, and CPE Routers
 */

_admin();
$ui->assign('_title', Lang::T('Fiber Management'));
$ui->assign('_system_menu', 'fiber');
$ui->assign('_admin', $admin);

$action = $routes['1'];
if (empty($action)) {
    $action = 'olt-devices';
}

switch ($action) {
    // OLT Devices Management
    case 'olt-devices':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $devices = ORM::for_table('tbl_olt_devices')
            ->order_by_asc('name')
            ->find_many();
        
        $ui->assign('devices', $devices);
        $ui->assign('_title', Lang::T('OLT Devices'));
        $ui->display('admin/fiber/olt-devices.tpl');
        break;
        
    case 'olt-add':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $ui->assign('_title', Lang::T('Add OLT Device'));
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/fiber/olt-add.tpl');
        break;
        
    case 'olt-add-post':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _post('csrf_token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/olt-add'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $name = _post('name');
        $brand = _post('brand');
        $ip_address = _post('ip_address');
        $port = _post('port');
        $username = _post('username');
        $password = _post('password');
        $description = _post('description');
        $status = _post('status');
        
        $msg = '';
        if (empty($name)) $msg .= Lang::T('Name is required') . '<br>';
        if (empty($brand)) $msg .= Lang::T('Brand is required') . '<br>';
        if (empty($ip_address)) $msg .= Lang::T('IP Address is required') . '<br>';
        if (empty($port)) $msg .= Lang::T('Port is required') . '<br>';
        if (empty($username)) $msg .= Lang::T('Username is required') . '<br>';
        
        if ($msg == '') {
            $d = ORM::for_table('tbl_olt_devices')->create();
            $d->name = $name;
            $d->brand = $brand;
            $d->ip_address = $ip_address;
            $d->port = $port;
            $d->username = $username;
            $d->password = $password;
            $d->description = $description;
            $d->status = $status ?: 'Active';
            $d->created_at = date('Y-m-d H:i:s');
            $d->save();
            
            _log('Admin ' . $admin['username'] . ' added OLT device: ' . $name, 'OLT');
            r2(getUrl('fiber/olt-devices'), 's', Lang::T('OLT Device added successfully'));
        } else {
            r2(getUrl('fiber/olt-add'), 'e', $msg);
        }
        break;
        
    case 'olt-edit':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $id = $routes['2'];
        $device = ORM::for_table('tbl_olt_devices')->find_one($id);
        
        if (!$device) {
            r2(getUrl('fiber/olt-devices'), 'e', Lang::T('OLT Device not found'));
        }
        
        $ui->assign('device', $device);
        $ui->assign('_title', Lang::T('Edit OLT Device'));
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/fiber/olt-edit.tpl');
        break;
        
    case 'olt-edit-post':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _post('csrf_token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/olt-devices'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $id = _post('id');
        $device = ORM::for_table('tbl_olt_devices')->find_one($id);
        
        if (!$device) {
            r2(getUrl('fiber/olt-devices'), 'e', Lang::T('OLT Device not found'));
        }
        
        $name = _post('name');
        $brand = _post('brand');
        $ip_address = _post('ip_address');
        $port = _post('port');
        $username = _post('username');
        $password = _post('password');
        $description = _post('description');
        $status = _post('status');
        
        $msg = '';
        if (empty($name)) $msg .= Lang::T('Name is required') . '<br>';
        if (empty($brand)) $msg .= Lang::T('Brand is required') . '<br>';
        if (empty($ip_address)) $msg .= Lang::T('IP Address is required') . '<br>';
        if (empty($port)) $msg .= Lang::T('Port is required') . '<br>';
        if (empty($username)) $msg .= Lang::T('Username is required') . '<br>';
        
        if ($msg == '') {
            $device->name = $name;
            $device->brand = $brand;
            $device->ip_address = $ip_address;
            $device->port = $port;
            $device->username = $username;
            if (!empty($password)) $device->password = $password;
            $device->description = $description;
            $device->status = $status;
            $device->updated_at = date('Y-m-d H:i:s');
            $device->save();
            
            _log('Admin ' . $admin['username'] . ' updated OLT device: ' . $name, 'OLT');
            r2(getUrl('fiber/olt-devices'), 's', Lang::T('OLT Device updated successfully'));
        } else {
            r2(getUrl('fiber/olt-edit/' . $id), 'e', $msg);
        }
        break;
        
    case 'olt-delete':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _req('token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/olt-devices'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $id = $routes['2'];
        $device = ORM::for_table('tbl_olt_devices')->find_one($id);
        
        if ($device) {
            $name = $device->name;
            $device->delete();
            _log('Admin ' . $admin['username'] . ' deleted OLT device: ' . $name, 'OLT');
            r2(getUrl('fiber/olt-devices'), 's', Lang::T('OLT Device deleted successfully'));
        } else {
            r2(getUrl('fiber/olt-devices'), 'e', Lang::T('OLT Device not found'));
        }
        break;

    // ONU Management
    case 'onus':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $onus = ORM::for_table('tbl_onus')
            ->select('tbl_onus.*')
            ->select('tbl_olt_devices.name', 'olt_name')
            ->select('tbl_customers.username', 'customer_username')
            ->select('tbl_customers.fullname', 'customer_fullname')
            ->left_outer_join('tbl_olt_devices', array('tbl_onus.olt_id', '=', 'tbl_olt_devices.id'))
            ->left_outer_join('tbl_customers', array('tbl_onus.customer_id', '=', 'tbl_customers.id'))
            ->order_by_desc('tbl_onus.created_at')
            ->find_many();
        
        $ui->assign('onus', $onus);
        $ui->assign('_title', Lang::T('ONUs'));
        $ui->display('admin/fiber/onus.tpl');
        break;
        
    case 'onu-add':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $olts = ORM::for_table('tbl_olt_devices')->where('status', 'Active')->find_many();
        $customers = ORM::for_table('tbl_customers')->where('status', 'Active')->find_many();
        $profiles = ORM::for_table('tbl_olt_profiles')->find_many();
        
        $ui->assign('olts', $olts);
        $ui->assign('customers', $customers);
        $ui->assign('profiles', $profiles);
        $ui->assign('_title', Lang::T('Add ONU'));
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/fiber/onu-add.tpl');
        break;
        
    case 'onu-add-post':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _post('csrf_token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/onu-add'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $olt_id = _post('olt_id');
        $serial_number = _post('serial_number');
        $onu_id = _post('onu_id');
        $pon_port = _post('pon_port');
        $customer_id = _post('customer_id');
        $profile_id = _post('profile_id');
        $status = _post('status');
        
        $msg = '';
        if (empty($olt_id)) $msg .= Lang::T('OLT is required') . '<br>';
        if (empty($serial_number)) $msg .= Lang::T('Serial Number is required') . '<br>';
        if (empty($onu_id)) $msg .= Lang::T('ONU ID is required') . '<br>';
        
        if ($msg == '') {
            $d = ORM::for_table('tbl_onus')->create();
            $d->olt_id = $olt_id;
            $d->serial_number = $serial_number;
            $d->onu_id = $onu_id;
            $d->pon_port = $pon_port;
            $d->customer_id = $customer_id;
            $d->profile_id = $profile_id;
            $d->status = $status ?: 'Inactive';
            $d->created_at = date('Y-m-d H:i:s');
            $d->save();
            
            _log('Admin ' . $admin['username'] . ' added ONU: ' . $serial_number, 'ONU');
            r2(getUrl('fiber/onus'), 's', Lang::T('ONU added successfully'));
        } else {
            r2(getUrl('fiber/onu-add'), 'e', $msg);
        }
        break;
        
    case 'onu-edit':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $id = $routes['2'];
        $onu = ORM::for_table('tbl_onus')->find_one($id);
        
        if (!$onu) {
            r2(getUrl('fiber/onus'), 'e', Lang::T('ONU not found'));
        }
        
        $olts = ORM::for_table('tbl_olt_devices')->where('status', 'Active')->find_many();
        $customers = ORM::for_table('tbl_customers')->where('status', 'Active')->find_many();
        $profiles = ORM::for_table('tbl_olt_profiles')->find_many();
        
        $ui->assign('onu', $onu);
        $ui->assign('olts', $olts);
        $ui->assign('customers', $customers);
        $ui->assign('profiles', $profiles);
        $ui->assign('_title', Lang::T('Edit ONU'));
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/fiber/onu-edit.tpl');
        break;
        
    case 'onu-edit-post':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _post('csrf_token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/onus'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $id = _post('id');
        $onu = ORM::for_table('tbl_onus')->find_one($id);
        
        if (!$onu) {
            r2(getUrl('fiber/onus'), 'e', Lang::T('ONU not found'));
        }
        
        $olt_id = _post('olt_id');
        $serial_number = _post('serial_number');
        $onu_id = _post('onu_id');
        $pon_port = _post('pon_port');
        $customer_id = _post('customer_id');
        $profile_id = _post('profile_id');
        $status = _post('status');
        
        $msg = '';
        if (empty($olt_id)) $msg .= Lang::T('OLT is required') . '<br>';
        if (empty($serial_number)) $msg .= Lang::T('Serial Number is required') . '<br>';
        if (empty($onu_id)) $msg .= Lang::T('ONU ID is required') . '<br>';
        
        if ($msg == '') {
            $onu->olt_id = $olt_id;
            $onu->serial_number = $serial_number;
            $onu->onu_id = $onu_id;
            $onu->pon_port = $pon_port;
            $onu->customer_id = $customer_id;
            $onu->profile_id = $profile_id;
            $onu->status = $status;
            $onu->updated_at = date('Y-m-d H:i:s');
            $onu->save();
            
            _log('Admin ' . $admin['username'] . ' updated ONU: ' . $serial_number, 'ONU');
            r2(getUrl('fiber/onus'), 's', Lang::T('ONU updated successfully'));
        } else {
            r2(getUrl('fiber/onu-edit/' . $id), 'e', $msg);
        }
        break;
        
    case 'onu-delete':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _req('token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/onus'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $id = $routes['2'];
        $onu = ORM::for_table('tbl_onus')->find_one($id);
        
        if ($onu) {
            $serial = $onu->serial_number;
            $onu->delete();
            _log('Admin ' . $admin['username'] . ' deleted ONU: ' . $serial, 'ONU');
            r2(getUrl('fiber/onus'), 's', Lang::T('ONU deleted successfully'));
        } else {
            r2(getUrl('fiber/onus'), 'e', Lang::T('ONU not found'));
        }
        break;
        
    case 'onu-activate':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _req('token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/onus'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $id = $routes['2'];
        $onu = ORM::for_table('tbl_onus')->find_one($id);
        
        if ($onu) {
            $onu->status = 'Active';
            $onu->updated_at = date('Y-m-d H:i:s');
            $onu->save();
            
            _log('Admin ' . $admin['username'] . ' activated ONU: ' . $onu->serial_number, 'ONU');
            r2(getUrl('fiber/onus'), 's', Lang::T('ONU activated successfully'));
        } else {
            r2(getUrl('fiber/onus'), 'e', Lang::T('ONU not found'));
        }
        break;
        
    case 'onu-suspend':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _req('token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/onus'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $id = $routes['2'];
        $onu = ORM::for_table('tbl_onus')->find_one($id);
        
        if ($onu) {
            $onu->status = 'Suspended';
            $onu->updated_at = date('Y-m-d H:i:s');
            $onu->save();
            
            _log('Admin ' . $admin['username'] . ' suspended ONU: ' . $onu->serial_number, 'ONU');
            r2(getUrl('fiber/onus'), 's', Lang::T('ONU suspended successfully'));
        } else {
            r2(getUrl('fiber/onus'), 'e', Lang::T('ONU not found'));
        }
        break;

    // OLT Profiles Management
    case 'profiles':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $profiles = ORM::for_table('tbl_olt_profiles')
            ->select('tbl_olt_profiles.*')
            ->select('tbl_olt_devices.name', 'olt_name')
            ->left_outer_join('tbl_olt_devices', array('tbl_olt_profiles.olt_id', '=', 'tbl_olt_devices.id'))
            ->order_by_asc('tbl_olt_profiles.name')
            ->find_many();
        
        $ui->assign('profiles', $profiles);
        $ui->assign('_title', Lang::T('OLT Profiles'));
        $ui->display('admin/fiber/profiles.tpl');
        break;
        
    case 'profile-add':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $olts = ORM::for_table('tbl_olt_devices')->where('status', 'Active')->find_many();
        
        $ui->assign('olts', $olts);
        $ui->assign('_title', Lang::T('Add Profile'));
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/fiber/profile-add.tpl');
        break;
        
    case 'profile-add-post':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _post('csrf_token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/profile-add'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $name = _post('name');
        $olt_id = _post('olt_id');
        $download_speed = _post('download_speed');
        $upload_speed = _post('upload_speed');
        $line_profile = _post('line_profile');
        $service_profile = _post('service_profile');
        $description = _post('description');
        
        $msg = '';
        if (empty($name)) $msg .= Lang::T('Profile Name is required') . '<br>';
        if (empty($olt_id)) $msg .= Lang::T('OLT is required') . '<br>';
        if (empty($download_speed)) $msg .= Lang::T('Download Speed is required') . '<br>';
        if (empty($upload_speed)) $msg .= Lang::T('Upload Speed is required') . '<br>';
        
        if ($msg == '') {
            $d = ORM::for_table('tbl_olt_profiles')->create();
            $d->name = $name;
            $d->olt_id = $olt_id;
            $d->download_speed = $download_speed;
            $d->upload_speed = $upload_speed;
            $d->line_profile = $line_profile;
            $d->service_profile = $service_profile;
            $d->description = $description;
            $d->created_at = date('Y-m-d H:i:s');
            $d->save();
            
            _log('Admin ' . $admin['username'] . ' added OLT profile: ' . $name, 'OLT');
            r2(getUrl('fiber/profiles'), 's', Lang::T('Profile added successfully'));
        } else {
            r2(getUrl('fiber/profile-add'), 'e', $msg);
        }
        break;
        
    case 'profile-edit':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $id = $routes['2'];
        $profile = ORM::for_table('tbl_olt_profiles')->find_one($id);
        
        if (!$profile) {
            r2(getUrl('fiber/profiles'), 'e', Lang::T('Profile not found'));
        }
        
        $olts = ORM::for_table('tbl_olt_devices')->where('status', 'Active')->find_many();
        
        $ui->assign('profile', $profile);
        $ui->assign('olts', $olts);
        $ui->assign('_title', Lang::T('Edit Profile'));
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/fiber/profile-edit.tpl');
        break;
        
    case 'profile-edit-post':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _post('csrf_token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/profiles'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $id = _post('id');
        $profile = ORM::for_table('tbl_olt_profiles')->find_one($id);
        
        if (!$profile) {
            r2(getUrl('fiber/profiles'), 'e', Lang::T('Profile not found'));
        }
        
        $name = _post('name');
        $olt_id = _post('olt_id');
        $download_speed = _post('download_speed');
        $upload_speed = _post('upload_speed');
        $line_profile = _post('line_profile');
        $service_profile = _post('service_profile');
        $description = _post('description');
        
        $msg = '';
        if (empty($name)) $msg .= Lang::T('Profile Name is required') . '<br>';
        if (empty($olt_id)) $msg .= Lang::T('OLT is required') . '<br>';
        if (empty($download_speed)) $msg .= Lang::T('Download Speed is required') . '<br>';
        if (empty($upload_speed)) $msg .= Lang::T('Upload Speed is required') . '<br>';
        
        if ($msg == '') {
            $profile->name = $name;
            $profile->olt_id = $olt_id;
            $profile->download_speed = $download_speed;
            $profile->upload_speed = $upload_speed;
            $profile->line_profile = $line_profile;
            $profile->service_profile = $service_profile;
            $profile->description = $description;
            $profile->updated_at = date('Y-m-d H:i:s');
            $profile->save();
            
            _log('Admin ' . $admin['username'] . ' updated OLT profile: ' . $name, 'OLT');
            r2(getUrl('fiber/profiles'), 's', Lang::T('Profile updated successfully'));
        } else {
            r2(getUrl('fiber/profile-edit/' . $id), 'e', $msg);
        }
        break;
        
    case 'profile-delete':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _req('token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/profiles'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $id = $routes['2'];
        $profile = ORM::for_table('tbl_olt_profiles')->find_one($id);
        
        if ($profile) {
            $name = $profile->name;
            $profile->delete();
            _log('Admin ' . $admin['username'] . ' deleted OLT profile: ' . $name, 'OLT');
            r2(getUrl('fiber/profiles'), 's', Lang::T('Profile deleted successfully'));
        } else {
            r2(getUrl('fiber/profiles'), 'e', Lang::T('Profile not found'));
        }
        break;

    // CPE Routers Management
    case 'cpe-routers':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $routers = ORM::for_table('tbl_cpe_routers')
            ->select('tbl_cpe_routers.*')
            ->select('tbl_customers.username', 'customer_username')
            ->select('tbl_customers.fullname', 'customer_fullname')
            ->select('tbl_onus.serial_number', 'onu_serial')
            ->left_outer_join('tbl_customers', array('tbl_cpe_routers.customer_id', '=', 'tbl_customers.id'))
            ->left_outer_join('tbl_onus', array('tbl_cpe_routers.onu_id', '=', 'tbl_onus.id'))
            ->order_by_desc('tbl_cpe_routers.created_at')
            ->find_many();
        
        $ui->assign('routers', $routers);
        $ui->assign('_title', Lang::T('CPE Routers'));
        $ui->display('admin/fiber/cpe-routers.tpl');
        break;
        
    case 'cpe-add':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $customers = ORM::for_table('tbl_customers')->where('status', 'Active')->find_many();
        $onus = ORM::for_table('tbl_onus')->where('status', 'Active')->find_many();
        
        $ui->assign('customers', $customers);
        $ui->assign('onus', $onus);
        $ui->assign('_title', Lang::T('Add CPE Router'));
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/fiber/cpe-add.tpl');
        break;
        
    case 'cpe-add-post':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _post('csrf_token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/cpe-add'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $customer_id = _post('customer_id');
        $onu_id = _post('onu_id');
        $mac_address = _post('mac_address');
        $ip_address = _post('ip_address');
        $brand = _post('brand');
        $model = _post('model');
        $protocol = _post('protocol');
        $username = _post('username');
        $password = _post('password');
        $status = _post('status');
        
        $msg = '';
        if (empty($customer_id)) $msg .= Lang::T('Customer is required') . '<br>';
        if (empty($mac_address)) $msg .= Lang::T('MAC Address is required') . '<br>';
        if (empty($ip_address)) $msg .= Lang::T('IP Address is required') . '<br>';
        if (empty($brand)) $msg .= Lang::T('Brand is required') . '<br>';
        
        if ($msg == '') {
            $d = ORM::for_table('tbl_cpe_routers')->create();
            $d->customer_id = $customer_id;
            $d->onu_id = $onu_id;
            $d->mac_address = $mac_address;
            $d->ip_address = $ip_address;
            $d->brand = $brand;
            $d->model = $model;
            $d->protocol = $protocol ?: 'HTTP';
            $d->username = $username;
            $d->password = $password;
            $d->status = $status ?: 'Active';
            $d->created_at = date('Y-m-d H:i:s');
            $d->save();
            
            _log('Admin ' . $admin['username'] . ' added CPE Router: ' . $mac_address, 'CPE');
            r2(getUrl('fiber/cpe-routers'), 's', Lang::T('CPE Router added successfully'));
        } else {
            r2(getUrl('fiber/cpe-add'), 'e', $msg);
        }
        break;
        
    case 'cpe-edit':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $id = $routes['2'];
        $router = ORM::for_table('tbl_cpe_routers')->find_one($id);
        
        if (!$router) {
            r2(getUrl('fiber/cpe-routers'), 'e', Lang::T('CPE Router not found'));
        }
        
        $customers = ORM::for_table('tbl_customers')->where('status', 'Active')->find_many();
        $onus = ORM::for_table('tbl_onus')->where('status', 'Active')->find_many();
        
        $ui->assign('router', $router);
        $ui->assign('customers', $customers);
        $ui->assign('onus', $onus);
        $ui->assign('_title', Lang::T('Edit CPE Router'));
        $ui->assign('csrf_token', Csrf::generateAndStoreToken());
        $ui->display('admin/fiber/cpe-edit.tpl');
        break;
        
    case 'cpe-edit-post':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _post('csrf_token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/cpe-routers'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $id = _post('id');
        $router = ORM::for_table('tbl_cpe_routers')->find_one($id);
        
        if (!$router) {
            r2(getUrl('fiber/cpe-routers'), 'e', Lang::T('CPE Router not found'));
        }
        
        $customer_id = _post('customer_id');
        $onu_id = _post('onu_id');
        $mac_address = _post('mac_address');
        $ip_address = _post('ip_address');
        $brand = _post('brand');
        $model = _post('model');
        $protocol = _post('protocol');
        $username = _post('username');
        $password = _post('password');
        $status = _post('status');
        
        $msg = '';
        if (empty($customer_id)) $msg .= Lang::T('Customer is required') . '<br>';
        if (empty($mac_address)) $msg .= Lang::T('MAC Address is required') . '<br>';
        if (empty($ip_address)) $msg .= Lang::T('IP Address is required') . '<br>';
        if (empty($brand)) $msg .= Lang::T('Brand is required') . '<br>';
        
        if ($msg == '') {
            $router->customer_id = $customer_id;
            $router->onu_id = $onu_id;
            $router->mac_address = $mac_address;
            $router->ip_address = $ip_address;
            $router->brand = $brand;
            $router->model = $model;
            $router->protocol = $protocol;
            $router->username = $username;
            if (!empty($password)) $router->password = $password;
            $router->status = $status;
            $router->updated_at = date('Y-m-d H:i:s');
            $router->save();
            
            _log('Admin ' . $admin['username'] . ' updated CPE Router: ' . $mac_address, 'CPE');
            r2(getUrl('fiber/cpe-routers'), 's', Lang::T('CPE Router updated successfully'));
        } else {
            r2(getUrl('fiber/cpe-edit/' . $id), 'e', $msg);
        }
        break;
        
    case 'cpe-delete':
        if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
            _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
        }
        
        $csrf_token = _req('token');
        if (!Csrf::check($csrf_token)) {
            r2(getUrl('fiber/cpe-routers'), 'e', Lang::T('Invalid or Expired CSRF Token'));
        }
        
        $id = $routes['2'];
        $router = ORM::for_table('tbl_cpe_routers')->find_one($id);
        
        if ($router) {
            $mac = $router->mac_address;
            $router->delete();
            _log('Admin ' . $admin['username'] . ' deleted CPE Router: ' . $mac, 'CPE');
            r2(getUrl('fiber/cpe-routers'), 's', Lang::T('CPE Router deleted successfully'));
        } else {
            r2(getUrl('fiber/cpe-routers'), 'e', Lang::T('CPE Router not found'));
        }
        break;
        
    default:
        r2(getUrl('dashboard'), 'e', Lang::T('Page not found'));
}
