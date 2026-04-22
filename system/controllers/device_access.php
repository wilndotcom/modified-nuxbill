<?php

/**
 * Device Access Controller
 * 
 * CPE Device Management Dashboard with Statistics and Charts
 */

_admin();
$ui->assign('_title', Lang::T('Device Access'));
$ui->assign('_system_menu', 'device_access');

$ui->assign('_admin', $admin);

// Check if user has admin access
if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin', 'Agent'])) {
    _alert(Lang::T('You do not have permission to access this page'), 'danger', 'dashboard');
}

$action = $routes['1'] ?? 'dashboard';

switch ($action) {
    case 'dashboard':
        // Check if table exists first
        try {
            $check = ORM::get_db()->query("SHOW TABLES LIKE 'tbl_cpe_devices'")->fetch();
            if (!$check) {
                // Table doesn't exist, show error message
                $ui->assign('table_missing', true);
                $ui->assign('totalDevices', 0);
                $ui->assign('pppoeCount', 0);
                $ui->assign('staticCount', 0);
                $ui->assign('routerCount', 0);
                $ui->assign('topDeviceTypes', []);
                $ui->assign('typeLabels', []);
                $ui->assign('typeValues', []);
                $ui->assign('pppoeVsStaticLabels', []);
                $ui->assign('pppoeVsStaticValues', []);
                $ui->assign('devices', []);
                $ui->assign('allDevices', []);
                $ui->assign('categoryFilter', 'all');
                $ui->assign('sortField', 'name');
                $ui->assign('sortOrder', 'desc');
                $ui->display('admin/device_access/dashboard.tpl');
                break;
            }
        } catch (Exception $e) {
            // Database error
            $ui->assign('table_missing', true);
            $ui->display('admin/device_access/dashboard.tpl');
            break;
        }

        // Fetch all devices with their router names
        $devices = ORM::for_table('tbl_cpe_devices')
            ->select('tbl_cpe_devices.*')
            ->select('tbl_routers.name', 'router_name')
            ->join('tbl_routers', ['tbl_cpe_devices.router_id', '=', 'tbl_routers.id'])
            ->find_array();

        // Fetch all routers for counting
        $routers = ORM::for_table('tbl_routers')->find_array();
        $routerCount = count($routers);

        // Process devices and add simulated data
        foreach ($devices as &$device) {
            // Simulate Radio Type for Ubiquiti devices
            if ($device['device_type'] === 'Ubiquiti') {
                $radioTypes = ['NanoStation', 'PowerBeam', 'LiteBeam'];
                $device['radio_type'] = $radioTypes[array_rand($radioTypes)];
            } else {
                $device['radio_type'] = 'N/A';
            }

            // Ensure access_url is set
            $device['access_url'] = $device['access_url'] ?? "http://{$device['ip_address']}:{$device['port']}";
        }
        unset($device);

        // Category filter
        $categoryFilter = _get('category', 'all');
        if ($categoryFilter !== 'all') {
            $devices = array_filter($devices, function($device) use ($categoryFilter) {
                return $device['device_type'] === $categoryFilter;
            });
        }

        // Calculate statistics
        $totalDevices = count($devices);
        $pppoeCount = count(array_filter($devices, function($device) {
            return $device['type'] === 'PPPoE';
        }));
        $staticCount = count(array_filter($devices, function($device) {
            return $device['type'] === 'Static';
        }));

        // Device type counts
        $tendaCount = count(array_filter($devices, function($device) {
            return $device['device_type'] === 'Tenda';
        }));
        $ubiquitiCount = count(array_filter($devices, function($device) {
            return $device['device_type'] === 'Ubiquiti';
        }));
        $huaweiCount = count(array_filter($devices, function($device) {
            return $device['device_type'] === 'Huawei';
        }));
        $tplinkCount = count(array_filter($devices, function($device) {
            return $device['device_type'] === 'TP-Link';
        }));
        $otherCount = count(array_filter($devices, function($device) {
            return $device['device_type'] === 'Other';
        }));

        // Prepare data for Most Used Device Types card (top 3 device types)
        $deviceTypeCounts = [
            'Tenda' => $tendaCount,
            'Ubiquiti' => $ubiquitiCount,
            'Huawei' => $huaweiCount,
            'TP-Link' => $tplinkCount,
            'Other' => $otherCount
        ];
        arsort($deviceTypeCounts);
        $topDeviceTypes = array_slice($deviceTypeCounts, 0, 3, true);

        // Prepare chart data
        $typeDistribution = [
            'Tenda' => $tendaCount,
            'Ubiquiti' => $ubiquitiCount,
            'Huawei' => $huaweiCount,
            'TP-Link' => $tplinkCount,
            'Other' => $otherCount
        ];
        $pppoeVsStaticDistribution = [
            'PPPoE' => $pppoeCount,
            'Static' => $staticCount
        ];

        $typeLabels = array_keys($typeDistribution);
        $typeValues = array_values($typeDistribution);
        $pppoeVsStaticLabels = array_keys($pppoeVsStaticDistribution);
        $pppoeVsStaticValues = array_values($pppoeVsStaticDistribution);

        // Sorting logic
        $sortField = _get('sort', 'name');
        $sortOrder = _get('order', 'desc');

        $validSortFields = ['name', 'type', 'device_type', 'ip_address', 'pppoe_username', 'router_name', 'port'];
        if (!in_array($sortField, $validSortFields)) {
            $sortField = 'name';
        }

        usort($devices, function($a, $b) use ($sortField, $sortOrder) {
            $aValue = $a[$sortField] ?? '';
            $bValue = $b[$sortField] ?? '';
            if ($sortOrder === 'asc') {
                return $aValue <=> $bValue;
            } else {
                return $bValue <=> $aValue;
            }
        });

        // Get the last 10 devices added
        usort($devices, function($a, $b) {
            return $b['id'] <=> $a['id'];
        });
        $recentDevices = array_slice($devices, 0, 10);

        // Assign to UI
        $ui->assign('totalDevices', $totalDevices);
        $ui->assign('pppoeCount', $pppoeCount);
        $ui->assign('staticCount', $staticCount);
        $ui->assign('routerCount', $routerCount);
        $ui->assign('topDeviceTypes', $topDeviceTypes);
        $ui->assign('typeLabels', $typeLabels);
        $ui->assign('typeValues', $typeValues);
        $ui->assign('pppoeVsStaticLabels', $pppoeVsStaticLabels);
        $ui->assign('pppoeVsStaticValues', $pppoeVsStaticValues);
        $ui->assign('devices', $recentDevices);
        $ui->assign('allDevices', $devices);
        $ui->assign('categoryFilter', $categoryFilter);
        $ui->assign('sortField', $sortField);
        $ui->assign('sortOrder', $sortOrder);

        $ui->display('admin/device_access/dashboard.tpl');
        break;

    case 'list':
        // List all devices with pagination
        $devices = ORM::for_table('tbl_cpe_devices')
            ->select('tbl_cpe_devices.*')
            ->select('tbl_routers.name', 'router_name')
            ->join('tbl_routers', ['tbl_cpe_devices.router_id', '=', 'tbl_routers.id'])
            ->find_array();

        $ui->assign('devices', $devices);
        $ui->display('admin/device_access/list.tpl');
        break;

    case 'add':
        $routers = ORM::for_table('tbl_routers')->find_array();
        $ui->assign('routers', $routers);
        $ui->display('admin/device_access/add.tpl');
        break;

    case 'add-post':
        // Validate CSRF
        if ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
            _alert(Lang::T('Invalid CSRF token'), 'danger', 'device_access/add');
        }

        $name = _post('name');
        $type = _post('type');
        $device_type = _post('device_type');
        $ip_address = _post('ip_address');
        $pppoe_username = _post('pppoe_username');
        $router_id = _post('router_id');
        $port = _post('port', 80);
        $access_url = _post('access_url');
        $customer_id = _post('customer_id');

        if (empty($name) || empty($type) || empty($device_type) || empty($ip_address)) {
            _alert(Lang::T('All fields are required'), 'danger', 'device_access/add');
        }

        // Check if IP already exists
        $existing = ORM::for_table('tbl_cpe_devices')
            ->where('ip_address', $ip_address)
            ->find_one();

        if ($existing) {
            _alert(Lang::T('Device with this IP already exists'), 'danger', 'device_access/add');
        }

        $device = ORM::for_table('tbl_cpe_devices')->create();
        $device->name = $name;
        $device->type = $type;
        $device->device_type = $device_type;
        $device->ip_address = $ip_address;
        $device->pppoe_username = $pppoe_username;
        $device->router_id = $router_id;
        $device->port = $port;
        $device->access_url = $access_url ?: "http://{$ip_address}:{$port}";
        $device->customer_id = $customer_id;
        $device->created_at = date('Y-m-d H:i:s');
        $device->save();

        _alert(Lang::T('Device added successfully'), 'success', 'device_access/dashboard');
        break;

    case 'edit':
        $id = $routes['2'];
        $device = ORM::for_table('tbl_cpe_devices')->find_one($id);

        if (!$device) {
            _alert(Lang::T('Device not found'), 'danger', 'device_access/dashboard');
        }

        $routers = ORM::for_table('tbl_routers')->find_array();
        $ui->assign('routers', $routers);
        $ui->assign('device', $device);
        $ui->display('admin/device_access/edit.tpl');
        break;

    case 'edit-post':
        $id = _post('id');
        $device = ORM::for_table('tbl_cpe_devices')->find_one($id);

        if (!$device) {
            _alert(Lang::T('Device not found'), 'danger', 'device_access/dashboard');
        }

        if ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
            _alert(Lang::T('Invalid CSRF token'), 'danger', 'device_access/edit/' . $id);
        }

        $device->name = _post('name');
        $device->type = _post('type');
        $device->device_type = _post('device_type');
        $device->ip_address = _post('ip_address');
        $device->pppoe_username = _post('pppoe_username');
        $device->router_id = _post('router_id');
        $device->port = _post('port', 80);
        $device->access_url = _post('access_url');
        $device->customer_id = _post('customer_id');
        $device->save();

        _alert(Lang::T('Device updated successfully'), 'success', 'device_access/dashboard');
        break;

    case 'delete':
        $id = $routes['2'];
        $device = ORM::for_table('tbl_cpe_devices')->find_one($id);

        if ($device) {
            $device->delete();
            _alert(Lang::T('Device deleted successfully'), 'success', 'device_access/dashboard');
        } else {
            _alert(Lang::T('Device not found'), 'danger', 'device_access/dashboard');
        }
        break;

    default:
        r2(getUrl('device_access/dashboard'));
        break;
}
