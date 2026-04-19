<?php

/**
 * OLT Synchronization Cron Job
 * 
 * This script synchronizes ONU status from OLT devices.
 * It should be run periodically (e.g., every 10 minutes) via cron:
 * 0,10,20,30,40,50 * * * * /usr/bin/php /path/to/system/cron_olt_sync.php >> /var/log/olt_sync.log 2>&1
 * Or integrated into the main cron.php
 */

include "../init.php";

$lockFile = "$CACHE_PATH/olt_sync.lock";

if (!is_dir($CACHE_PATH)) {
    echo "Directory '$CACHE_PATH' does not exist. Exiting...\n";
    exit;
}

$lock = fopen($lockFile, 'c');

if ($lock === false) {
    echo "Failed to open lock file. Exiting...\n";
    exit;
}

if (!flock($lock, LOCK_EX | LOCK_NB)) {
    echo "OLT sync is already running. Exiting...\n";
    fclose($lock);
    exit;
}

$isCli = true;
if (php_sapi_name() !== 'cli') {
    $isCli = false;
    echo "<pre>";
}

echo "OLT Sync Started\t" . date('Y-m-d H:i:s') . "\n";

// Get all active OLT devices
$olts = ORM::for_table('tbl_olt_devices')
    ->where('status', 'Active')
    ->find_many();

if (!$olts || count($olts) == 0) {
    echo "No active OLT devices found.\n";
    flock($lock, LOCK_UN);
    fclose($lock);
    unlink($lockFile);
    exit;
}

echo "Found " . count($olts) . " active OLT device(s)\n";

$updatedCount = 0;
$errorCount = 0;

foreach ($olts as $olt) {
    echo "\nProcessing OLT: {$olt->name} ({$olt->ip_address})\n";
    
    try {
        // Load OLT driver based on brand
        $driverFile = 'system/devices/olt/' . strtolower($olt->brand) . '.php';
        $driverClass = $olt->brand;
        
        if (!file_exists($driverFile)) {
            // Try generic SNMP driver
            $driverFile = 'system/devices/olt/generic_snmp.php';
            $driverClass = 'GenericSNMP';
            
            if (!file_exists($driverFile)) {
                echo "  Error: No driver found for OLT brand '{$olt->brand}'\n";
                $errorCount++;
                continue;
            }
        }
        
        require_once $driverFile;
        
        if (!class_exists($driverClass)) {
            echo "  Error: Driver class '{$driverClass}' not found\n";
            $errorCount++;
            continue;
        }
        
        // Connect to OLT
        $oltDriver = new $driverClass($olt->ip_address, $olt->port, $olt->username, $olt->password);
        
        if (!$oltDriver->connect()) {
            echo "  Error: Failed to connect to OLT\n";
            
            // Update OLT status to offline
            $olt->status = 'Offline';
            $olt->save();
            
            $errorCount++;
            continue;
        }
        
        // Update OLT status to online
        $olt->status = 'Active';
        $olt->last_seen = date('Y-m-d H:i:s');
        $olt->save();
        
        // Get all ONUs for this OLT
        $onus = ORM::for_table('tbl_onus')
            ->where('olt_id', $olt->id)
            ->find_many();
        
        echo "  Found " . count($onus) . " ONUs\n";
        
        // Get ONU list from OLT
        $oltOnus = $oltDriver->getOnuList();
        
        if ($oltOnus === false) {
            echo "  Warning: Failed to get ONU list from OLT\n";
            continue;
        }
        
        // Update each ONU status
        foreach ($onus as $onu) {
            $onuFound = false;
            
            foreach ($oltOnus as $oltOnu) {
                // Match by serial number or ONU ID
                if ($oltOnu['serial_number'] == $onu->serial_number || 
                    $oltOnu['onu_id'] == $onu->onu_id) {
                    
                    $onuFound = true;
                    
                    // Update ONU status
                    $oldStatus = $onu->status;
                    $newStatus = $oltOnu['status']; // 'Active', 'Inactive', 'Offline', etc.
                    
                    $onu->status = $newStatus;
                    $onu->signal_level = $oltOnu['signal_level'] ?? null;
                    $onu->distance = $oltOnu['distance'] ?? null;
                    $onu->uptime = $oltOnu['uptime'] ?? null;
                    $onu->last_seen = date('Y-m-d H:i:s');
                    $onu->save();
                    
                    // Log status change
                    if ($oldStatus != $newStatus) {
                        _log("ONU {$onu->serial_number} status changed from {$oldStatus} to {$newStatus} on OLT {$olt->name}", 'ONU');
                        echo "  ONU {$onu->serial_number}: {$oldStatus} -> {$newStatus}\n";
                    } else {
                        echo "  ONU {$onu->serial_number}: {$newStatus} (unchanged)\n";
                    }
                    
                    $updatedCount++;
                    break;
                }
            }
            
            // If ONU not found on OLT, mark as offline
            if (!$onuFound) {
                if ($onu->status != 'Offline') {
                    _log("ONU {$onu->serial_number} not found on OLT {$olt->name}, marking as Offline", 'ONU');
                    echo "  ONU {$onu->serial_number}: Not found on OLT, marking as Offline\n";
                }
                
                $onu->status = 'Offline';
                $onu->save();
                $updatedCount++;
            }
        }
        
        // Disconnect from OLT
        $oltDriver->disconnect();
        
        echo "  OLT sync completed successfully\n";
        
    } catch (Throwable $e) {
        echo "  Error: " . $e->getMessage() . "\n";
        _log("OLT Sync Error for {$olt->name}: " . $e->getMessage(), 'OLT');
        $errorCount++;
    }
}

echo "\n----------------------------------------\n";
echo "OLT Sync Summary:\n";
echo "  OLTs processed: " . count($olts) . "\n";
echo "  ONUs updated: {$updatedCount}\n";
echo "  Errors: {$errorCount}\n";
echo "  Finished: " . date('Y-m-d H:i:s') . "\n";

flock($lock, LOCK_UN);
fclose($lock);
unlink($lockFile);

exit;
