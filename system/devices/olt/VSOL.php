<?php

/**
 * VSOL OLT Driver
 * 
 * Supports VSOL GPON OLT devices via SNMP
 * Common models: V1600D, V1600G, V1600A, etc.
 * 
 * VSOL uses standard GPON OIDs with vendor-specific extensions
 */

require_once __DIR__ . '/GenericSNMP.php';

class VSOL extends GenericSNMP
{
    /**
     * VSOL-specific SNMP OIDs
     */
    private $oids = [
        // ONU table and registration
        'onu_table' => '1.3.6.1.4.1.40339.1.1.101.1.1.1',
        'onu_serial' => '1.3.6.1.4.1.40339.1.1.101.1.1.1.2',  // Serial number
        'onu_status' => '1.3.6.1.4.1.40339.1.1.101.1.1.1.5',  // Online status
        'onu_signal' => '1.3.6.1.4.1.40339.1.1.101.1.1.1.6',  // Signal level (dBm)
        'onu_distance' => '1.3.6.1.4.1.40339.1.1.101.1.1.1.7', // Distance (m)
        'onu_uptime' => '1.3.6.1.4.1.40339.1.1.101.1.1.1.8',  // Uptime
        'onu_description' => '1.3.6.1.4.1.40339.1.1.101.1.1.1.3', // Description
        
        // ONU control OIDs
        'onu_activate' => '1.3.6.1.4.1.40339.1.1.101.2.1.1.3',   // Activate ONU
        'onu_deactivate' => '1.3.6.1.4.1.40339.1.1.101.2.1.1.4', // Deactivate ONU
        'onu_reset' => '1.3.6.1.4.1.40339.1.1.101.2.1.1.5',      // Reset ONU
        
        // OLT system info
        'olt_model' => '1.3.6.1.4.1.40339.1.1.1.1.0',            // Model name
        'olt_firmware' => '1.3.6.1.4.1.40339.1.1.1.2.0',         // Firmware version
        'olt_pon_ports' => '1.3.6.1.4.1.40339.1.1.1.4.0',        // Number of PON ports
        
        // Performance counters
        'onu_tx_power' => '1.3.6.1.4.1.40339.1.1.101.1.1.1.9',   // TX optical power
        'onu_rx_power' => '1.3.6.1.4.1.40339.1.1.101.1.1.1.10',  // RX optical power
        'onu_temperature' => '1.3.6.1.4.1.40339.1.1.101.1.1.1.11', // Temperature
        'onu_voltage' => '1.3.6.1.4.1.40339.1.1.101.1.1.1.12',    // Voltage
    ];
    
    /**
     * ONU status mapping
     */
    private $statusMap = [
        1 => 'Offline',
        2 => 'Online',
        3 => 'Pending',
        4 => 'DyingGasp',
        5 => 'AuthFailed',
        6 => 'Blocked',
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
     * Get ONU list from VSOL OLT
     * 
     * @return array|false Array of ONUs or false on failure
     */
    public function getOnuList()
    {
        if (!$this->connected && !$this->connect()) {
            return false;
        }
        
        try {
            $onus = [];
            
            // Walk ONU table to get all registered ONUs
            $onuEntries = @snmpwalk(
                $this->ip . ':' . $this->port,
                $this->community,
                $this->oids['onu_table'],
                1000000,
                3
            );
            
            if ($onuEntries === false || empty($onuEntries)) {
                _log("VSOL: No ONUs found or SNMP walk failed", 'OLT');
                return [];
            }
            
            // Parse ONU entries
            foreach ($onuEntries as $entry) {
                $parts = explode('=', $entry);
                if (count($parts) < 2) continue;
                
                $oid = trim($parts[0]);
                $value = trim($parts[1]);
                
                // Extract ONU index from OID
                preg_match('/\.([0-9]+)$/', $oid, $matches);
                if (!isset($matches[1])) continue;
                
                $onuIndex = $matches[1];
                
                if (!isset($onus[$onuIndex])) {
                    $onus[$onuIndex] = [
                        'onu_id' => $onuIndex,
                        'serial_number' => '',
                        'status' => 'Unknown',
                        'signal_level' => null,
                        'distance' => null,
                        'uptime' => null,
                        'description' => '',
                        'tx_power' => null,
                        'rx_power' => null,
                        'temperature' => null,
                        'voltage' => null,
                    ];
                }
            }
            
            // Get detailed info for each ONU
            foreach ($onus as $index => &$onu) {
                $onu['serial_number'] = $this->getOnuOidValue($this->oids['onu_serial'], $index);
                
                $statusRaw = $this->getOnuOidValue($this->oids['onu_status'], $index, true);
                $onu['status'] = $this->mapStatus($statusRaw);
                
                $signalRaw = $this->getOnuOidValue($this->oids['onu_signal'], $index, true);
                $onu['signal_level'] = $this->formatSignalLevel($signalRaw);
                
                $distanceRaw = $this->getOnuOidValue($this->oids['onu_distance'], $index, true);
                $onu['distance'] = $this->formatDistance($distanceRaw);
                
                $onu['uptime'] = $this->getOnuOidValue($this->oids['onu_uptime'], $index);
                $onu['description'] = $this->getOnuOidValue($this->oids['onu_description'], $index);
                
                // Additional performance metrics
                $txPowerRaw = $this->getOnuOidValue($this->oids['onu_tx_power'], $index, true);
                $onu['tx_power'] = $this->formatSignalLevel($txPowerRaw);
                
                $rxPowerRaw = $this->getOnuOidValue($this->oids['onu_rx_power'], $index, true);
                $onu['rx_power'] = $this->formatSignalLevel($rxPowerRaw);
                
                $tempRaw = $this->getOnuOidValue($this->oids['onu_temperature'], $index, true);
                $onu['temperature'] = $tempRaw !== null ? round($tempRaw / 10, 1) : null;
                
                $voltageRaw = $this->getOnuOidValue($this->oids['onu_voltage'], $index, true);
                $onu['voltage'] = $voltageRaw !== null ? round($voltageRaw / 1000, 2) : null;
            }
            
            return array_values($onus);
            
        } catch (Throwable $e) {
            _log("VSOL getOnuList Error: " . $e->getMessage(), 'OLT');
            return false;
        }
    }
    
    /**
     * Get specific ONU details by serial number
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
            if ($onu['serial_number'] == $serialNumber) {
                return $onu;
            }
        }
        
        return false;
    }
    
    /**
     * Activate ONU on VSOL OLT
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
                _log("VSOL activateOnu: ONU {$serialNumber} not found", 'OLT');
                return false;
            }
            
            $onuIndex = $onuDetails['onu_id'];
            $oid = $this->oids['onu_activate'] . '.' . $onuIndex;
            
            // Set value to 1 to activate
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
                _log("VSOL: ONU {$serialNumber} activated successfully", 'OLT');
                return true;
            }
            
            _log("VSOL: Failed to activate ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("VSOL activateOnu Error: " . $e->getMessage(), 'OLT');
            return false;
        }
    }
    
    /**
     * Deactivate ONU on VSOL OLT
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
                _log("VSOL deactivateOnu: ONU {$serialNumber} not found", 'OLT');
                return false;
            }
            
            $onuIndex = $onuDetails['onu_id'];
            $oid = $this->oids['onu_deactivate'] . '.' . $onuIndex;
            
            // Set value to 1 to deactivate
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
                _log("VSOL: ONU {$serialNumber} deactivated successfully", 'OLT');
                return true;
            }
            
            _log("VSOL: Failed to deactivate ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("VSOL deactivateOnu Error: " . $e->getMessage(), 'OLT');
            return false;
        }
    }
    
    /**
     * Reset ONU on VSOL OLT
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
                _log("VSOL resetOnu: ONU {$serialNumber} not found", 'OLT');
                return false;
            }
            
            $onuIndex = $onuDetails['onu_id'];
            $oid = $this->oids['onu_reset'] . '.' . $onuIndex;
            
            // Set value to 1 to reset
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
                _log("VSOL: ONU {$serialNumber} reset successfully", 'OLT');
                return true;
            }
            
            _log("VSOL: Failed to reset ONU {$serialNumber}", 'OLT');
            return false;
            
        } catch (Throwable $e) {
            _log("VSOL resetOnu Error: " . $e->getMessage(), 'OLT');
            return false;
        }
    }
    
    /**
     * Set ONU profile on VSOL OLT
     * 
     * @param string $serialNumber
     * @param string $profileName
     * @return bool
     */
    public function setOnuProfile($serialNumber, $profileName)
    {
        _log("VSOL: setOnuProfile not yet implemented for VSOL", 'OLT');
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
            
            // Add VSOL-specific info
            $model = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['olt_model']);
            if ($model !== false) {
                $info['model'] = trim($model, '"');
            }
            
            $firmware = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['olt_firmware']);
            if ($firmware !== false) {
                $info['firmware'] = trim($firmware, '"');
            }
            
            $ponPorts = @snmpget($this->ip . ':' . $this->port, $this->community, $this->oids['olt_pon_ports']);
            if ($ponPorts !== false) {
                $info['pon_ports'] = intval($ponPorts);
            }
            
            $info['brand'] = 'VSOL';
            
            return $info;
            
        } catch (Throwable $e) {
            _log("VSOL getSystemInfo Error: " . $e->getMessage(), 'OLT');
            return false;
        }
    }
    
    /**
     * Helper: Get OID value for specific ONU
     * 
     * @param string $baseOid
     * @param int $onuIndex
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
     * Map raw status to readable status
     * 
     * @param int|null $statusRaw
     * @return string
     */
    private function mapStatus($statusRaw)
    {
        if ($statusRaw === null) {
            return 'Unknown';
        }
        
        return $this->statusMap[$statusRaw] ?? 'Unknown';
    }
    
    /**
     * Format signal level value
     * 
     * @param mixed $rawValue
     * @return float|null
     */
    private function formatSignalLevel($rawValue)
    {
        if ($rawValue === null || $rawValue === false) {
            return null;
        }
        
        // VSOL reports signal in dBm * 100
        return round($rawValue / 100, 2);
    }
    
    /**
     * Format distance value
     * 
     * @param mixed $rawValue
     * @return int|null
     */
    private function formatDistance($rawValue)
    {
        if ($rawValue === null || $rawValue === false) {
            return null;
        }
        
        // VSOL reports distance in meters
        return intval($rawValue);
    }
}
