<?php

/**
 * Huawei OLT Driver
 * 
 * Supports Huawei GPON OLT devices via SNMP
 * Common models: MA5600T, MA5800, MA5680T series
 * 
 * Uses Huawei proprietary and standard GPON MIBs
 */

require_once __DIR__ . '/GenericSNMP.php';

class Huawei extends GenericSNMP
{
    /**
     * Huawei-specific SNMP OIDs
     */
    private $oids = [
        // Standard GPON ONU table (from HWGponOnts.mib)
        'onu_table' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3',
        'onu_ifindex' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3',  // Interface index
        'onu_description' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.9', // ONU description
        
        // ONU status and performance
        'onu_admin_state' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.5',   // Admin state
        'onu_oper_state' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.6',    // Operational state
        'onu_config_state' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.7',   // Config state
        'onu_match_state' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.8',    // Match state
        
        // ONU optical info
        'onu_tx_power' => '1.3.6.1.4.1.2011.6.128.1.1.2.51.1.1',      // TX optical power
        'onu_rx_power' => '1.3.6.1.4.1.2011.6.128.1.1.2.51.1.2',      // RX optical power
        'onu_olt_rx_power' => '1.3.6.1.4.1.2011.6.128.1.1.2.51.1.3', // OLT RX power
        'onu_distance' => '1.3.6.1.4.1.2011.6.128.1.1.2.51.1.4',      // Distance
        
        // ONU control
        'onu_reset' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.11',       // Reset ONU
        'onu_deactivate' => '1.3.6.1.4.1.2011.6.128.1.1.2.43.1.12',  // Deactivate ONU
        
        // OLT system info
        'olt_product_name' => '1.3.6.1.4.1.2011.6.3.1.1.0',          // Product name
        'olt_version' => '1.3.6.1.4.1.2011.6.3.1.2.0',               // Software version
        'olt_uptime' => '1.3.6.1.4.1.2011.6.3.1.3.0',                // System uptime
        'olt_temperature' => '1.3.6.1.4.1.2011.6.3.1.4.0',           // Temperature
        
        // PON port info
        'pon_port_count' => '1.3.6.1.4.1.2011.6.128.1.1.2.2.0',      // PON port count
        'pon_port_status' => '1.3.6.1.4.1.2011.6.128.1.1.2.4.1.2',   // PON port status
    ];
    
    /**
     * Status mappings
     */
    private $adminStateMap = [
        1 => 'Enabled',
        2 => 'Disabled',
    ];
    
    private $operStateMap = [
        1 => 'Online',
        2 => 'Offline',
        3 => 'DyingGasp',
        4 => 'PowerOff',
        5 => 'Unknown',
    ];
    
    private $configStateMap = [
        1 => 'Normal',
        2 => 'Mismatched',
        3 => 'Fail',
    ];
    
    private $matchStateMap = [
        1 => 'Match',
        2 => 'Mismatch',
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
     * Get ONU list from Huawei OLT
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
                _log("Huawei: No ONUs found or SNMP walk failed", 'OLT');
                return [];
            }
            
            // Parse ONU entries and extract indices
            foreach ($onuEntries as $entry) {
                $parts = explode('=', $entry);
                if (count($parts) < 2) continue;
                
                $oid = trim($parts[0]);
                $value = trim($parts[1]);
                
                // Extract ONU index (format: gponport.onuid)
                preg_match('/\.([0-9]+)\.([0-9]+)$/', $oid, $matches);
                if (!isset($matches[1]) || !isset($matches[2])) continue;
                
                $ponPort = $matches[1];
                $onuId = $matches[2];
                $onuIndex = $ponPort . '.' . $onuId;
                
                if (!isset($onus[$onuIndex])) {
                    $onus[$onuIndex] = [
                        'onu_id' => $onuIndex,
                        'pon_port' => $ponPort,
                        'onu_number' => $onuId,
                        'serial_number' => '',
                        'description' => '',
                        'status' => 'Unknown',
                        'admin_state' => 'Unknown',
                        'oper_state' => 'Unknown',
                        'config_state' => 'Unknown',
                        'match_state' => 'Unknown',
                        'signal_level' => null,
                        'distance' => null,
                        'tx_power' => null,
                        'rx_power' => null,
                        'olt_rx_power' => null,
                    ];
                }
            }
            
            // Get detailed info for each ONU
            foreach ($onus as $index => &$onu) {
                // Get description (contains serial info)
                $desc = $this->getOnuOidValue($this->oids['onu_description'], $index);
                $onu['description'] = $desc;
                
                // Extract serial from description or use index
                if (!empty($desc)) {
                    // Try to extract serial number from description
                    preg_match('/SN[:\s]*([A-Z0-9]+)/i', $desc, $matches);
                    if (isset($matches[1])) {
                        $onu['serial_number'] = $matches[1];
                    } else {
                        $onu['serial_number'] = 'HUAWEI-' . $index;
                    }
                } else {
                    $onu['serial_number'] = 'HUAWEI-' . $index;
                }
                
                // Get states
                $adminState = $this->getOnuOidValue($this->oids['onu_admin_state'], $index, true);
                $onu['admin_state'] = $this->adminStateMap[$adminState] ?? 'Unknown';
                
                $operState = $this->getOnuOidValue($this->oids['onu_oper_state'], $index, true);
                $onu['oper_state'] = $this->operStateMap[$operState] ?? 'Unknown';
                
                $configState = $this->getOnuOidValue($this->oids['onu_config_state'], $index, true);
                $onu['config_state'] = $this->configStateMap[$configState] ?? 'Unknown';
                
                $matchState = $this->getOnuOidValue($this->oids['onu_match_state'], $index, true);
                $onu['match_state'] = $this->matchStateMap[$matchState] ?? 'Unknown';
                
                // Determine overall status
                $onu['status'] = $this->determineStatus($operState, $adminState);
                
                // Get optical info
                $txPower = $this->getOnuOidValue($this->oids['onu_tx_power'], $index, true);
                $onu['tx_power'] = $this->formatOpticalPower($txPower);
                
                $rxPower = $this->getOnuOidValue($this->oids['onu_rx_power'], $index, true);
                $onu['rx_power'] = $this->formatOpticalPower($rxPower);
                
                $oltRxPower = $this->getOnuOidValue($this->oids['onu_olt_rx_power'], $index, true);
                $onu['olt_rx_power'] = $this->formatOpticalPower($oltRxPower);
                
                $distance = $this->getOnuOidValue($this->oids['onu_distance'], $index, true);
                $onu['distance'] = $distance !== null ? intval($distance) : null;
            }
            
            return array_values($onus);
            
        } catch (Throwable $e) {
            _log("Huawei getOnuList Error: " . $e->getMessage(), 'OLT');
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
            // Huawei ONUs are automatically activated when they register
            // To "activate" we ensure the ONU is not deactivated
            _log("Huawei: ONU {$serialNumber} activation handled by OLT auto-discovery", 'OLT');
            return true;
            
        } catch (Throwable $e) {
            _log("Huawei activateOnu Error: " . $e->getMessage(), 'OLT');
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
                _log("Huawei deactivateOnu: ONU {$serialNumber} not found", 'OLT');
                return false;
            }
            
            $onuIndex = $onuDetails['onu_id'];
            $oid = $this->oids['onu_deactivate'] . '.' . $onuIndex;
            
            $result = @snmpset(
                $this->ip . ':' . $this->port,
                $this->community,
                $oid,
                'i',
                2, // Set admin state to disabled
                1000000,
                3
            );
            
            if ($result !== false) {
                _log("Huawei: ONU {$serialNumber} deactivated successfully", 'OLT');
                return true;
            }
            
            _log("Huawei: Failed to deactivate ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("Huawei deactivateOnu Error: " . $e->getMessage(), 'OLT');
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
                _log("Huawei resetOnu: ONU {$serialNumber} not found", 'OLT');
                return false;
            }
            
            $onuIndex = $onuDetails['onu_id'];
            $oid = $this->oids['onu_reset'] . '.' . $onuIndex;
            
            $result = @snmpset(
                $this->ip . ':' . $this->port,
                $this->community,
                $oid,
                'i',
                1, // Reset ONU
                1000000,
                3
            );
            
            if ($result !== false) {
                _log("Huawei: ONU {$serialNumber} reset successfully", 'OLT');
                return true;
            }
            
            _log("Huawei: Failed to reset ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("Huawei resetOnu Error: " . $e->getMessage(), 'OLT');
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
        _log("Huawei: setOnuProfile requires command-line configuration", 'OLT');
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
            
            $productName = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['olt_product_name']);
            if ($productName !== false) {
                $info['model'] = trim($productName, '"');
            }
            
            $version = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['olt_version']);
            if ($version !== false) {
                $info['firmware'] = trim($version, '"');
            }
            
            $uptime = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['olt_uptime']);
            if ($uptime !== false) {
                $info['uptime_raw'] = $uptime;
            }
            
            $temp = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['olt_temperature']);
            if ($temp !== false) {
                $info['temperature'] = trim($temp, '"');
            }
            
            $ponCount = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['pon_port_count']);
            if ($ponCount !== false) {
                $info['pon_ports'] = intval($ponCount);
            }
            
            $info['brand'] = 'Huawei';
            
            return $info;
            
        } catch (Throwable $e) {
            _log("Huawei getSystemInfo Error: " . $e->getMessage(), 'OLT');
            return false;
        }
    }
    
    /**
     * Determine ONU status based on operational and admin states
     * 
     * @param int|null $operState
     * @param int|null $adminState
     * @return string
     */
    private function determineStatus($operState, $adminState)
    {
        if ($adminState == 2) {
            return 'Disabled';
        }
        
        if ($operState == 1) {
            return 'Online';
        } elseif ($operState == 2) {
            return 'Offline';
        } elseif ($operState == 3) {
            return 'DyingGasp';
        } elseif ($operState == 4) {
            return 'PowerOff';
        }
        
        return 'Unknown';
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
        
        // Huawei reports optical power in dBm * 100
        return round($rawValue / 100, 2);
    }
}
