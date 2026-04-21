<?php

/**
 * BDCOM OLT Driver
 * 
 * Supports BDCOM GPON OLT devices via SNMP
 * Common models: P3310C, P3310B, P3608, P3608B, etc.
 * 
 * Uses BDCOM proprietary MIBs
 */

require_once __DIR__ . '/GenericSNMP.php';

class BDCOM extends GenericSNMP
{
    /**
     * BDCOM-specific SNMP OIDs
     */
    private $oids = [
        // ONU table
        'onu_table' => '1.3.6.1.4.1.3320.101.10.1.1',
        'onu_index' => '1.3.6.1.4.1.3320.101.10.1.1.1',    // ONU index
        'onu_description' => '1.3.6.1.4.1.3320.101.10.1.1.2', // ONU description
        'onu_type' => '1.3.6.1.4.1.3320.101.10.1.1.3',      // ONU type
        'onu_status' => '1.3.6.1.4.1.3320.101.10.1.1.4',     // Status
        'onu_mac' => '1.3.6.1.4.1.3320.101.10.1.1.5',        // MAC address
        'onu_vendor' => '1.3.6.1.4.1.3320.101.10.1.1.6',     // Vendor ID
        
        // ONU optical and performance
        'onu_rx_power' => '1.3.6.1.4.1.3320.101.10.5.1.3',   // RX optical power
        'onu_tx_power' => '1.3.6.1.4.1.3320.101.10.5.1.4',   // TX optical power
        'onu_distance' => '1.3.6.1.4.1.3320.101.10.5.1.5',   // Distance
        'onu_temperature' => '1.3.6.1.4.1.3320.101.10.5.1.6', // Temperature
        'onu_voltage' => '1.3.6.1.4.1.3320.101.10.5.1.7',    // Voltage
        
        // ONU control
        'onu_admin' => '1.3.6.1.4.1.3320.101.10.1.1.7',       // Admin state
        'onu_reset' => '1.3.6.1.4.1.3320.101.10.1.1.8',       // Reset
        
        // OLT system info
        'olt_model' => '1.3.6.1.4.1.3320.1.1.1.0',            // Model
        'olt_version' => '1.3.6.1.4.1.3320.1.1.2.0',          // Software version
        'olt_uptime' => '1.3.6.1.4.1.3320.1.1.3.0',           // Uptime
        'olt_serial' => '1.3.6.1.4.1.3320.1.1.4.0',           // Serial number
        
        // PON port info
        'pon_port_count' => '1.3.6.1.4.1.3320.101.3.1.0',      // PON port count
        'pon_status' => '1.3.6.1.4.1.3320.101.3.2.1.2',       // PON status
    ];
    
    /**
     * Status mappings
     */
    private $statusMap = [
        1 => 'Online',
        2 => 'Offline',
        3 => 'DyingGasp',
        4 => 'PowerOff',
        5 => 'AuthFailed',
        6 => 'Disabled',
    ];
    
    private $adminStateMap = [
        1 => 'Enable',
        2 => 'Disable',
    ];
    
    /**
     * Constructor
     */
    public function __construct($ip, $port = 161, $username = 'public', $password = '')
    {
        parent::__construct($ip, $port, $username, $password);
        $this->snmpVersion = '2c';
    }
    
    /**
     * Get ONU list from BDCOM OLT
     * 
     * @return array|false
     */
    public function getOnuList()
    {
        if (!$this->connected && !$this->connect()) {
            return false;
        }
        
        try {
            $onus = [];
            
            // Walk ONU table
            $onuEntries = @snmpwalk(
                $this->ip . ':' . $this->port,
                $this->community,
                $this->oids['onu_table'],
                1000000,
                3
            );
            
            if ($onuEntries === false || empty($onuEntries)) {
                _log("BDCOM: No ONUs found or SNMP walk failed", 'OLT');
                return [];
            }
            
            // Parse ONU entries
            foreach ($onuEntries as $entry) {
                $parts = explode('=', $entry);
                if (count($parts) < 2) continue;
                
                $oid = trim($parts[0]);
                $value = trim($parts[1]);
                
                // Extract ONU index
                preg_match('/\.([0-9]+\.?[0-9]*)$/', $oid, $matches);
                if (!isset($matches[1])) continue;
                
                $onuIndex = $matches[1];
                
                if (!isset($onus[$onuIndex])) {
                    $onus[$onuIndex] = [
                        'onu_id' => $onuIndex,
                        'serial_number' => '',
                        'description' => '',
                        'mac_address' => '',
                        'vendor' => '',
                        'status' => 'Unknown',
                        'admin_state' => 'Unknown',
                        'signal_level' => null,
                        'distance' => null,
                        'tx_power' => null,
                        'rx_power' => null,
                        'temperature' => null,
                        'voltage' => null,
                    ];
                }
            }
            
            // Get detailed info for each ONU
            foreach ($onus as $index => &$onu) {
                // Get MAC address (used as serial)
                $mac = $this->getOnuOidValue($this->oids['onu_mac'], $index);
                if (!empty($mac)) {
                    $onu['mac_address'] = $this->formatMacAddress($mac);
                    $onu['serial_number'] = $onu['mac_address'];
                } else {
                    // Use description if MAC not available
                    $desc = $this->getOnuOidValue($this->oids['onu_description'], $index);
                    if (!empty($desc)) {
                        $onu['description'] = $desc;
                        $onu['serial_number'] = 'BDCOM-' . $index;
                    } else {
                        $onu['serial_number'] = 'BDCOM-' . $index;
                    }
                }
                
                // Get vendor
                $vendor = $this->getOnuOidValue($this->oids['onu_vendor'], $index);
                if (!empty($vendor)) {
                    $onu['vendor'] = $vendor;
                }
                
                // Get status
                $status = $this->getOnuOidValue($this->oids['onu_status'], $index, true);
                $onu['status'] = $this->statusMap[$status] ?? 'Unknown';
                
                // Get admin state
                $adminState = $this->getOnuOidValue($this->oids['onu_admin'], $index, true);
                $onu['admin_state'] = $this->adminStateMap[$adminState] ?? 'Unknown';
                
                // Get optical info
                $rxPower = $this->getOnuOidValue($this->oids['onu_rx_power'], $index, true);
                $onu['rx_power'] = $this->formatOpticalPower($rxPower);
                
                $txPower = $this->getOnuOidValue($this->oids['onu_tx_power'], $index, true);
                $onu['tx_power'] = $this->formatOpticalPower($txPower);
                
                // Use RX power as signal level
                $onu['signal_level'] = $onu['rx_power'];
                
                // Get distance
                $distance = $this->getOnuOidValue($this->oids['onu_distance'], $index, true);
                $onu['distance'] = $distance !== null ? intval($distance) : null;
                
                // Get temperature
                $temp = $this->getOnuOidValue($this->oids['onu_temperature'], $index, true);
                $onu['temperature'] = $temp !== null ? round($temp / 10, 1) : null;
                
                // Get voltage
                $voltage = $this->getOnuOidValue($this->oids['onu_voltage'], $index, true);
                $onu['voltage'] = $voltage !== null ? round($voltage / 1000, 2) : null;
            }
            
            return array_values($onus);
            
        } catch (Throwable $e) {
            _log("BDCOM getOnuList Error: " . $e->getMessage(), 'OLT');
            return false;
        }
    }
    
    /**
     * Get ONU details
     * 
     * @param string $serialNumber
     * @return array|false
     */
    public function getOnuDetails($serialNumber)
    {
        $onus = $this->getOnuList();
        
        if ($onus === false) {
            return false;
        }
        
        foreach ($onus as $onu) {
            if (strcasecmp($onu['serial_number'], $serialNumber) == 0 ||
                $onu['serial_number'] == $serialNumber) {
                return $onu;
            }
        }
        
        return false;
    }
    
    /**
     * Activate ONU
     * 
     * @param string $serialNumber
     * @return bool
     */
    public function activateOnu($serialNumber)
    {
        if (!$this->connected && !$this->connect()) {
            return false;
        }
        
        try {
            $onuDetails = $this->getOnuDetails($serialNumber);
            
            if (!$onuDetails) {
                _log("BDCOM activateOnu: ONU {$serialNumber} not found", 'OLT');
                return false;
            }
            
            $onuIndex = $onuDetails['onu_id'];
            $oid = $this->oids['onu_admin'] . '.' . $onuIndex;
            
            // Set admin state to enable (1)
            $result = @snmpset(
                $this->ip . ':' . $this->port,
                $this->community,
                $oid,
                'i',
                1,
                1000000,
                3
            );
            
            if ($result !== false) {
                _log("BDCOM: ONU {$serialNumber} activated successfully", 'OLT');
                return true;
            }
            
            _log("BDCOM: Failed to activate ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("BDCOM activateOnu Error: " . $e->getMessage(), 'OLT');
            return false;
        }
    }
    
    /**
     * Deactivate ONU
     * 
     * @param string $serialNumber
     * @return bool
     */
    public function deactivateOnu($serialNumber)
    {
        if (!$this->connected && !$this->connect()) {
            return false;
        }
        
        try {
            $onuDetails = $this->getOnuDetails($serialNumber);
            
            if (!$onuDetails) {
                _log("BDCOM deactivateOnu: ONU {$serialNumber} not found", 'OLT');
                return false;
            }
            
            $onuIndex = $onuDetails['onu_id'];
            $oid = $this->oids['onu_admin'] . '.' . $onuIndex;
            
            // Set admin state to disable (2)
            $result = @snmpset(
                $this->ip . ':' . $this->port,
                $this->community,
                $oid,
                'i',
                2,
                1000000,
                3
            );
            
            if ($result !== false) {
                _log("BDCOM: ONU {$serialNumber} deactivated successfully", 'OLT');
                return true;
            }
            
            _log("BDCOM: Failed to deactivate ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("BDCOM deactivateOnu Error: " . $e->getMessage(), 'OLT');
            return false;
        }
    }
    
    /**
     * Reset ONU
     * 
     * @param string $serialNumber
     * @return bool
     */
    public function resetOnu($serialNumber)
    {
        if (!$this->connected && !$this->connect()) {
            return false;
        }
        
        try {
            $onuDetails = $this->getOnuDetails($serialNumber);
            
            if (!$onuDetails) {
                _log("BDCOM resetOnu: ONU {$serialNumber} not found", 'OLT');
                return false;
            }
            
            $onuIndex = $onuDetails['onu_id'];
            $oid = $this->oids['onu_reset'] . '.' . $onuIndex;
            
            // Set reset value to 1
            $result = @snmpset(
                $this->ip . ':' . $this->port,
                $this->community,
                $oid,
                'i',
                1,
                1000000,
                3
            );
            
            if ($result !== false) {
                _log("BDCOM: ONU {$serialNumber} reset successfully", 'OLT');
                return true;
            }
            
            _log("BDCOM: Failed to reset ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("BDCOM resetOnu Error: " . $e->getMessage(), 'OLT');
            return false;
        }
    }
    
    /**
     * Set ONU profile
     * 
     * @param string $serialNumber
     * @param string $profileName
     * @return bool
     */
    public function setOnuProfile($serialNumber, $profileName)
    {
        _log("BDCOM: setOnuProfile requires command-line configuration", 'OLT');
        return false;
    }
    
    /**
     * Get OLT system information
     * 
     * @return array|false
     */
    public function getSystemInfo()
    {
        if (!$this->connected && !$this->connect()) {
            return false;
        }
        
        try {
            $info = parent::getSystemInfo();
            
            $model = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['olt_model']);
            if ($model !== false) {
                $info['model'] = trim($model, '"');
            }
            
            $version = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['olt_version']);
            if ($version !== false) {
                $info['firmware'] = trim($version, '"');
            }
            
            $uptime = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['olt_uptime']);
            if ($uptime !== false) {
                $info['uptime_raw'] = $uptime;
            }
            
            $serial = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['olt_serial']);
            if ($serial !== false) {
                $info['serial_number'] = trim($serial, '"');
            }
            
            $ponCount = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['pon_port_count']);
            if ($ponCount !== false) {
                $info['pon_ports'] = intval($ponCount);
            }
            
            $info['brand'] = 'BDCOM';
            
            return $info;
            
        } catch (Throwable $e) {
            _log("BDCOM getSystemInfo Error: " . $e->getMessage(), 'OLT');
            return false;
        }
    }
    
    /**
     * Helper: Get OID value for specific ONU
     * 
     * @param string $baseOid
     * @param string $onuIndex
     * @param bool $asNumber
     * @return mixed
     */
    private function getOnuOidValue($baseOid, $onuIndex, $asNumber = false)
    {
        $oid = $baseOid . '.' . $onuIndex;
        
        $result = @snmpget(
            $this->ip . ':' . $this->port,
            $this->community,
            $oid,
            1000000,
            3
        );
        
        if ($result === false) {
            return null;
        }
        
        $value = trim($result, '"');
        
        if ($asNumber) {
            if (strpos($value, 'INTEGER:') !== false) {
                $value = trim(str_replace('INTEGER:', '', $value));
                return intval($value);
            }
            if (strpos($value, 'Gauge32:') !== false) {
                $value = trim(str_replace('Gauge32:', '', $value));
                return intval($value);
            }
            return is_numeric($value) ? floatval($value) : null;
        }
        
        return $value;
    }
    
    /**
     * Format MAC address
     * 
     * @param string $rawMac
     * @return string
     */
    private function formatMacAddress($rawMac)
    {
        // Remove any "Hex-STRING:" prefix
        $mac = trim(str_replace('Hex-STRING:', '', $rawMac));
        $mac = trim($mac, '"');
        
        // If it's already formatted with colons, return as-is
        if (strpos($mac, ':') !== false) {
            return strtoupper($mac);
        }
        
        // If it's a hex string without spaces, format it
        $mac = preg_replace('/[^a-fA-F0-9]/', '', $mac);
        if (strlen($mac) == 12) {
            return strtoupper(implode(':', str_split($mac, 2)));
        }
        
        return strtoupper($mac);
    }
    
    /**
     * Format optical power value
     * 
     * @param mixed $rawValue
     * @return float|null
     */
    private function formatOpticalPower($rawValue)
    {
        if ($rawValue === null || $rawValue === false) {
            return null;
        }
        
        // BDCOM reports optical power in dBm * 100
        return round($rawValue / 100, 2);
    }
}
