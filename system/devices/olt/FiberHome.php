<?php

/**
 * FiberHome OLT Driver
 * 
 * Supports FiberHome GPON OLT devices via SNMP
 * Common models: AN5516-01, AN5516-04, AN5516-06, AN5506 series
 * 
 * Uses FiberHome proprietary MIBs
 */

require_once __DIR__ . '/GenericSNMP.php';

class FiberHome extends GenericSNMP
{
    /**
     * FiberHome-specific SNMP OIDs
     */
    private $oids = [
        // ONU table - FiberHome AN5516 series
        'onu_table' => '1.3.6.1.4.1.5875.800.3.9.3.3',
        'onu_index' => '1.3.6.1.4.1.5875.800.3.9.3.3.1.1',      // ONU index
        'onu_name' => '1.3.6.1.4.1.5875.800.3.9.3.3.1.2',        // ONU name/description
        'onu_type' => '1.3.6.1.4.1.5875.800.3.9.3.3.1.3',        // ONU type
        'onu_mac' => '1.3.6.1.4.1.5875.800.3.9.3.3.1.4',         // MAC address
        'onu_ip' => '1.3.6.1.4.1.5875.800.3.9.3.3.1.5',           // IP address
        'onu_status' => '1.3.6.1.4.1.5875.800.3.9.3.3.1.6',       // Status
        'onu_admin' => '1.3.6.1.4.1.5875.800.3.9.3.3.1.7',       // Admin state
        'onu_serial' => '1.3.6.1.4.1.5875.800.3.9.3.3.1.8',       // Serial number
        
        // ONU optical and performance
        'onu_rx_power' => '1.3.6.1.4.1.5875.800.3.9.3.4.1.1',     // RX optical power
        'onu_tx_power' => '1.3.6.1.4.1.5875.800.3.9.3.4.1.2',     // TX optical power
        'onu_olt_rx_power' => '1.3.6.1.4.1.5875.800.3.9.3.4.1.3', // OLT RX power
        'onu_temperature' => '1.3.6.1.4.1.5875.800.3.9.3.4.1.4',   // Temperature
        'onu_voltage' => '1.3.6.1.4.1.5875.800.3.9.3.4.1.5',     // Voltage
        'onu_distance' => '1.3.6.1.4.1.5875.800.3.9.3.4.1.6',     // Distance
        'onu_uptime' => '1.3.6.1.4.1.5875.800.3.9.3.4.1.7',       // Uptime
        
        // ONU control
        'onu_admin_set' => '1.3.6.1.4.1.5875.800.3.9.3.3.1.7',   // Admin control
        'onu_reset' => '1.3.6.1.4.1.5875.800.3.9.3.5.1.1',       // Reset ONU
        
        // OLT system info
        'olt_model' => '1.3.6.1.4.1.5875.800.3.1.1.1.0',          // OLT model
        'olt_version' => '1.3.6.1.4.1.5875.800.3.1.1.2.0',        // Software version
        'olt_uptime' => '1.3.6.1.4.1.5875.800.3.1.1.3.0',         // System uptime
        'olt_serial' => '1.3.6.1.4.1.5875.800.3.1.1.4.0',         // Serial number
        
        // PON port info
        'pon_port_count' => '1.3.6.1.4.1.5875.800.3.2.1.0',        // PON port count
        'pon_status' => '1.3.6.1.4.1.5875.800.3.2.2.1.2',         // PON status
        'pon_onu_count' => '1.3.6.1.4.1.5875.800.3.2.2.1.3',      // PON ONU count
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
        7 => 'LOS',
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
     * Get ONU list from FiberHome OLT
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
                _log("FiberHome: No ONUs found or SNMP walk failed", 'OLT');
                return [];
            }
            
            // Parse ONU entries
            foreach ($onuEntries as $entry) {
                $parts = explode('=', $entry);
                if (count($parts) < 2) continue;
                
                $oid = trim($parts[0]);
                $value = trim($parts[1]);
                
                // Extract ONU index (FiberHome uses format: shelf.slot.port.onuId)
                preg_match('/\.([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)$/', $oid, $matches);
                if (!isset($matches[1])) {
                    // Try simpler format
                    preg_match('/\.([0-9]+\.[0-9]+)$/', $oid, $matches);
                }
                
                if (!isset($matches[1])) continue;
                
                $onuIndex = $matches[1];
                
                if (!isset($onus[$onuIndex])) {
                    $onus[$onuIndex] = [
                        'onu_id' => $onuIndex,
                        'serial_number' => '',
                        'description' => '',
                        'mac_address' => '',
                        'ip_address' => '',
                        'status' => 'Unknown',
                        'admin_state' => 'Unknown',
                        'signal_level' => null,
                        'distance' => null,
                        'tx_power' => null,
                        'rx_power' => null,
                        'temperature' => null,
                        'voltage' => null,
                        'uptime' => null,
                    ];
                }
            }
            
            // Get detailed info for each ONU
            foreach ($onus as $index => &$onu) {
                // Get serial number
                $serial = $this->getOnuOidValue($this->oids['onu_serial'], $index);
                if (!empty($serial)) {
                    $onu['serial_number'] = $serial;
                } else {
                    // Use MAC as fallback
                    $mac = $this->getOnuOidValue($this->oids['onu_mac'], $index);
                    if (!empty($mac)) {
                        $onu['mac_address'] = $this->formatMacAddress($mac);
                        $onu['serial_number'] = $onu['mac_address'];
                    } else {
                        $onu['serial_number'] = 'FH-' . str_replace('.', '-', $index);
                    }
                }
                
                // Get name/description
                $onu['description'] = $this->getOnuOidValue($this->oids['onu_name'], $index);
                
                // Get IP address
                $ip = $this->getOnuOidValue($this->oids['onu_ip'], $index);
                if (!empty($ip)) {
                    $onu['ip_address'] = $this->formatIpAddress($ip);
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
                
                // Get temperature
                $temp = $this->getOnuOidValue($this->oids['onu_temperature'], $index, true);
                $onu['temperature'] = $temp !== null ? round($temp / 10, 1) : null;
                
                // Get voltage
                $voltage = $this->getOnuOidValue($this->oids['onu_voltage'], $index, true);
                $onu['voltage'] = $voltage !== null ? round($voltage / 1000, 2) : null;
                
                // Get distance
                $distance = $this->getOnuOidValue($this->oids['onu_distance'], $index, true);
                $onu['distance'] = $distance !== null ? intval($distance) : null;
                
                // Get uptime
                $onu['uptime'] = $this->getOnuOidValue($this->oids['onu_uptime'], $index);
            }
            
            return array_values($onus);
            
        } catch (Throwable $e) {
            _log("FiberHome getOnuList Error: " . $e->getMessage(), 'OLT');
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
                _log("FiberHome activateOnu: ONU {$serialNumber} not found", 'OLT');
                return false;
            }
            
            $onuIndex = $onuDetails['onu_id'];
            $oid = $this->oids['onu_admin_set'] . '.' . $onuIndex;
            
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
                _log("FiberHome: ONU {$serialNumber} activated successfully", 'OLT');
                return true;
            }
            
            _log("FiberHome: Failed to activate ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("FiberHome activateOnu Error: " . $e->getMessage(), 'OLT');
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
                _log("FiberHome deactivateOnu: ONU {$serialNumber} not found", 'OLT');
                return false;
            }
            
            $onuIndex = $onuDetails['onu_id'];
            $oid = $this->oids['onu_admin_set'] . '.' . $onuIndex;
            
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
                _log("FiberHome: ONU {$serialNumber} deactivated successfully", 'OLT');
                return true;
            }
            
            _log("FiberHome: Failed to deactivate ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("FiberHome deactivateOnu Error: " . $e->getMessage(), 'OLT');
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
                _log("FiberHome resetOnu: ONU {$serialNumber} not found", 'OLT');
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
                _log("FiberHome: ONU {$serialNumber} reset successfully", 'OLT');
                return true;
            }
            
            _log("FiberHome: Failed to reset ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("FiberHome resetOnu Error: " . $e->getMessage(), 'OLT');
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
        _log("FiberHome: setOnuProfile requires command-line configuration", 'OLT');
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
            
            $info['brand'] = 'FiberHome';
            
            return $info;
            
        } catch (Throwable $e) {
            _log("FiberHome getSystemInfo Error: " . $e->getMessage(), 'OLT');
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
     * Format IP address
     * 
     * @param string $rawIp
     * @return string
     */
    private function formatIpAddress($rawIp)
    {
        // Remove "IpAddress:" prefix
        $ip = trim(str_replace('IpAddress:', '', $rawIp));
        return trim($ip, '"');
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
        
        // FiberHome reports optical power in dBm * 100
        return round($rawValue / 100, 2);
    }
}
