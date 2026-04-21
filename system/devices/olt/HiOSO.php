<?php

/**
 * HiOSO OLT Driver
 * 
 * Supports HiOSO GPON OLT devices via SNMP
 * Common models: HA5440, HA5500, HA5600 series
 * 
 * Uses HiOSO proprietary MIBs based on common GPON standards
 */

require_once __DIR__ . '/GenericSNMP.php';

class HiOSO extends GenericSNMP
{
    /**
     * HiOSO-specific SNMP OIDs
     * Based on standard GPON MIB structure with HiOSO vendor extensions
     */
    private $oids = [
        // ONU table
        'onu_table' => '1.3.6.1.4.1.36183.1.1.1.1',
        'onu_index' => '1.3.6.1.4.1.36183.1.1.1.1.1',      // ONU index
        'onu_serial' => '1.3.6.1.4.1.36183.1.1.1.1.2',        // Serial number
        'onu_description' => '1.3.6.1.4.1.36183.1.1.1.1.3',    // Description
        'onu_password' => '1.3.6.1.4.1.36183.1.1.1.1.4',      // ONU password
        'onu_type' => '1.3.6.1.4.1.36183.1.1.1.1.5',          // ONU type
        'onu_status' => '1.3.6.1.4.1.36183.1.1.1.1.6',        // Status
        'onu_admin_state' => '1.3.6.1.4.1.36183.1.1.1.1.7',  // Admin state
        
        // ONU optical and performance
        'onu_rx_power' => '1.3.6.1.4.1.36183.1.1.2.1.3',      // RX optical power
        'onu_tx_power' => '1.3.6.1.4.1.36183.1.1.2.1.4',      // TX optical power
        'onu_distance' => '1.3.6.1.4.1.36183.1.1.2.1.5',      // Distance
        'onu_temperature' => '1.3.6.1.4.1.36183.1.1.2.1.6',     // Temperature
        'onu_voltage' => '1.3.6.1.4.1.36183.1.1.2.1.7',       // Voltage
        'onu_current' => '1.3.6.1.4.1.36183.1.1.2.1.8',       // Current
        'onu_uptime' => '1.3.6.1.4.1.36183.1.1.2.1.9',         // Uptime
        
        // ONU control
        'onu_activate' => '1.3.6.1.4.1.36183.1.1.3.1',         // Activate
        'onu_deactivate' => '1.3.6.1.4.1.36183.1.1.3.2',       // Deactivate
        'onu_reset' => '1.3.6.1.4.1.36183.1.1.3.3',            // Reset ONU
        
        // OLT system info
        'olt_model' => '1.3.6.1.4.1.36183.1.2.1.0',            // Model
        'olt_version' => '1.3.6.1.4.1.36183.1.2.2.0',          // Software version
        'olt_uptime' => '1.3.6.1.4.1.36183.1.2.3.0',           // System uptime
        'olt_serial' => '1.3.6.1.4.1.36183.1.2.4.0',           // Serial number
        'olt_temperature' => '1.3.6.1.4.1.36183.1.2.5.0',      // Temperature
        
        // PON port info
        'pon_port_count' => '1.3.6.1.4.1.36183.1.3.1.0',       // PON port count
        'pon_port_status' => '1.3.6.1.4.1.36183.1.3.2.1.2',    // PON status
        'pon_port_onu_count' => '1.3.6.1.4.1.36183.1.3.2.1.3', // PON ONU count
    ];
    
    /**
     * Status mappings
     */
    private $statusMap = [
        1 => 'Offline',
        2 => 'Online',
        3 => 'Pending',
        4 => 'DyingGasp',
        5 => 'AuthFailed',
        6 => 'Offline',
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
     * Get ONU list from HiOSO OLT
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
                _log("HiOSO: No ONUs found or SNMP walk failed", 'OLT');
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
                        'password' => '',
                        'onu_type' => '',
                        'status' => 'Unknown',
                        'admin_state' => 'Unknown',
                        'signal_level' => null,
                        'distance' => null,
                        'tx_power' => null,
                        'rx_power' => null,
                        'temperature' => null,
                        'voltage' => null,
                        'current' => null,
                        'uptime' => null,
                    ];
                }
            }
            
            // Get detailed info for each ONU
            foreach ($onus as $index => &$onu) {
                // Get serial number
                $onu['serial_number'] = $this->getOnuOidValue($this->oids['onu_serial'], $index);
                if (empty($onu['serial_number'])) {
                    $onu['serial_number'] = 'HiOSO-' . $index;
                }
                
                // Get description
                $onu['description'] = $this->getOnuOidValue($this->oids['onu_description'], $index);
                
                // Get ONU password
                $onu['password'] = $this->getOnuOidValue($this->oids['onu_password'], $index);
                
                // Get ONU type
                $onu['onu_type'] = $this->getOnuOidValue($this->oids['onu_type'], $index);
                
                // Get status
                $status = $this->getOnuOidValue($this->oids['onu_status'], $index, true);
                $onu['status'] = $this->statusMap[$status] ?? 'Unknown';
                
                // Get admin state
                $adminState = $this->getOnuOidValue($this->oids['onu_admin_state'], $index, true);
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
                
                // Get current
                $current = $this->getOnuOidValue($this->oids['onu_current'], $index, true);
                $onu['current'] = $current !== null ? round($current / 1000, 3) : null;
                
                // Get uptime
                $onu['uptime'] = $this->getOnuOidValue($this->oids['onu_uptime'], $index);
            }
            
            return array_values($onus);
            
        } catch (Throwable $e) {
            _log("HiOSO getOnuList Error: " . $e->getMessage(), 'OLT');
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
                _log("HiOSO activateOnu: ONU {$serialNumber} not found", 'OLT');
                return false;
            }
            
            $onuIndex = $onuDetails['onu_id'];
            $oid = $this->oids['onu_activate'] . '.' . $onuIndex;
            
            // Set activate to 1
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
                _log("HiOSO: ONU {$serialNumber} activated successfully", 'OLT');
                return true;
            }
            
            _log("HiOSO: Failed to activate ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("HiOSO activateOnu Error: " . $e->getMessage(), 'OLT');
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
                _log("HiOSO deactivateOnu: ONU {$serialNumber} not found", 'OLT');
                return false;
            }
            
            $onuIndex = $onuDetails['onu_id'];
            $oid = $this->oids['onu_deactivate'] . '.' . $onuIndex;
            
            // Set deactivate to 1
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
                _log("HiOSO: ONU {$serialNumber} deactivated successfully", 'OLT');
                return true;
            }
            
            _log("HiOSO: Failed to deactivate ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("HiOSO deactivateOnu Error: " . $e->getMessage(), 'OLT');
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
                _log("HiOSO resetOnu: ONU {$serialNumber} not found", 'OLT');
                return false;
            }
            
            $onuIndex = $onuDetails['onu_id'];
            $oid = $this->oids['onu_reset'] . '.' . $onuIndex;
            
            // Set reset to 1
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
                _log("HiOSO: ONU {$serialNumber} reset successfully", 'OLT');
                return true;
            }
            
            _log("HiOSO: Failed to reset ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("HiOSO resetOnu Error: " . $e->getMessage(), 'OLT');
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
        _log("HiOSO: setOnuProfile requires command-line configuration", 'OLT');
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
            
            $temp = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['olt_temperature']);
            if ($temp !== false) {
                $info['temperature'] = $temp;
            }
            
            $ponCount = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['pon_port_count']);
            if ($ponCount !== false) {
                $info['pon_ports'] = intval($ponCount);
            }
            
            $info['brand'] = 'HiOSO';
            
            return $info;
            
        } catch (Throwable $e) {
            _log("HiOSO getSystemInfo Error: " . $e->getMessage(), 'OLT');
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
        
        // HiOSO reports optical power in dBm * 100
        return round($rawValue / 100, 2);
    }
}
