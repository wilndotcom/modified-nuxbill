<?php

/**
 * TP-Link CPE Router Driver
 * 
 * Supports TP-Link routers and access points via HTTP API
 * Common models: Archer C6, C60, C80, TL-WR841N, etc.
 * 
 * Uses TP-Link's web interface API
 */

require_once __DIR__ . '/BaseCPE.php';

class TPLink extends BaseCPE
{
    private $sessionCookie = '';
    private $token = '';
    private $isNewInterface = false; // true for newer TP-Link models
    
    /**
     * Connect to TP-Link router
     * 
     * @return bool
     */
    public function connect()
    {
        try {
            // Try new interface first (newer models)
            if ($this->loginNewInterface()) {
                $this->isNewInterface = true;
                $this->connected = true;
                return true;
            }
            
            // Fall back to old interface
            if ($this->loginOldInterface()) {
                $this->isNewInterface = false;
                $this->connected = true;
                return true;
            }
            
            _log("TPLink: Failed to login to {$this->ip}", 'CPE');
            return false;
            
        } catch (Throwable $e) {
            _log("TPLink connect Error: " . $e->getMessage(), 'CPE');
            return false;
        }
    }
    
    /**
     * Login using old TP-Link interface
     * 
     * @return bool
     */
    private function loginOldInterface()
    {
        $data = [
            'username' => $this->username,
            'password' => $this->password,
        ];
        
        $result = $this->httpRequest('/userRpm/LoginRpm.htm', 'GET', $data);
        
        if ($result === false || $result['code'] != 200) {
            return false;
        }
        
        // Check for successful login (redirects to main page)
        if (strpos($result['body'], 'Error') !== false || 
            strpos($result['body'], 'incorrect') !== false) {
            return false;
        }
        
        // Extract session cookie if present
        if (preg_match('/Set-Cookie:\s*([^;]+)/i', $result['body'], $matches)) {
            $this->sessionCookie = $matches[1];
        }
        
        return true;
    }
    
    /**
     * Login using new TP-Link interface
     * 
     * @return bool
     */
    private function loginNewInterface()
    {
        $data = [
            'username' => base64_encode($this->username),
            'password' => base64_encode($this->password),
        ];
        
        $result = $this->httpRequest('/cgi-bin/luci/;stok=/login?form=login', 'POST', $data, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        
        if ($result === false || $result['code'] != 200) {
            return false;
        }
        
        $response = $this->parseJson($result['body']);
        
        if (!$response || !isset($response['success']) || !$response['success']) {
            return false;
        }
        
        // Extract stok token
        if (isset($response['data']['stok'])) {
            $this->token = $response['data']['stok'];
        }
        
        return true;
    }
    
    /**
     * Disconnect from router
     */
    public function disconnect()
    {
        if ($this->isNewInterface && !empty($this->token)) {
            // Logout from new interface
            $this->httpRequest('/cgi-bin/luci/;stok=' . $this->token . '/admin/system?form=logout', 'POST');
        }
        
        $this->connected = false;
        $this->sessionCookie = '';
        $this->token = '';
    }
    
    /**
     * Get CPE status
     * 
     * @return array|false
     */
    public function getStatus()
    {
        if (!$this->connected && !$this->connect()) {
            return false;
        }
        
        try {
            if ($this->isNewInterface) {
                return $this->getStatusNewInterface();
            } else {
                return $this->getStatusOldInterface();
            }
            
        } catch (Throwable $e) {
            _log("TPLink getStatus Error: " . $e->getMessage(), 'CPE');
            return false;
        }
    }
    
    /**
     * Get status from new interface
     * 
     * @return array|false
     */
    private function getStatusNewInterface()
    {
        $result = $this->httpRequest('/cgi-bin/luci/;stok=' . $this->token . '/admin/status?form=all', 'GET', [], [
            'X-Requested-With: XMLHttpRequest',
        ]);
        
        if ($result === false || $result['code'] != 200) {
            return false;
        }
        
        $data = $this->parseJson($result['body']);
        
        if (!$data || !isset($data['success']) || !$data['success']) {
            return false;
        }
        
        $status = [
            'brand' => 'TP-Link',
            'model' => $data['data']['model'] ?? 'Unknown',
            'firmware' => $data['data']['firmware_version'] ?? 'Unknown',
            'uptime' => $data['data']['uptime'] ?? 0,
            'uptime_formatted' => $this->formatUptime($data['data']['uptime'] ?? 0),
            'wan_ip' => $data['data']['wan_ip'] ?? '',
            'wan_status' => $data['data']['wan_status'] ?? 'Unknown',
            'lan_ip' => $data['data']['lan_ip'] ?? $this->ip,
            'cpu_usage' => $data['data']['cpu_usage'] ?? null,
            'memory_usage' => $data['data']['mem_usage'] ?? null,
        ];
        
        return $status;
    }
    
    /**
     * Get status from old interface
     * 
     * @return array|false
     */
    private function getStatusOldInterface()
    {
        $result = $this->httpRequest('/userRpm/StatusRpm.htm', 'GET');
        
        if ($result === false || $result['code'] != 200) {
            return false;
        }
        
        $html = $result['body'];
        
        // Parse status from HTML
        $status = [
            'brand' => 'TP-Link',
            'model' => $this->parseFromHtml($html, 'Model|Product', '[^<]+'),
            'firmware' => $this->parseFromHtml($html, 'Firmware', '[^<]+'),
            'uptime' => 0,
            'uptime_formatted' => $this->parseFromHtml($html, 'System Up Time|Uptime', '[^<]+'),
            'wan_ip' => $this->parseFromHtml($html, 'WAN IP Address|IP Address', '[0-9.]+'),
            'wan_status' => 'Unknown',
            'lan_ip' => $this->ip,
        ];
        
        return $status;
    }
    
    /**
     * Get connected WiFi clients
     * 
     * @return array|false
     */
    public function getConnectedClients()
    {
        if (!$this->connected && !$this->connect()) {
            return false;
        }
        
        try {
            if ($this->isNewInterface) {
                return $this->getClientsNewInterface();
            } else {
                return $this->getClientsOldInterface();
            }
            
        } catch (Throwable $e) {
            _log("TPLink getConnectedClients Error: " . $e->getMessage(), 'CPE');
            return false;
        }
    }
    
    /**
     * Get clients from new interface
     * 
     * @return array|false
     */
    private function getClientsNewInterface()
    {
        $result = $this->httpRequest('/cgi-bin/luci/;stok=' . $this->token . '/admin/wireless?form=stations', 'GET', [], [
            'X-Requested-With: XMLHttpRequest',
        ]);
        
        if ($result === false || $result['code'] != 200) {
            return false;
        }
        
        $data = $this->parseJson($result['body']);
        
        if (!$data || !isset($data['data']['stations'])) {
            return [];
        }
        
        $clients = [];
        foreach ($data['data']['stations'] as $station) {
            $clients[] = [
                'mac' => $station['mac'] ?? '',
                'ip' => $station['ip'] ?? '',
                'signal' => $station['signal'] ?? null,
                'connected_time' => $station['connected_time'] ?? 0,
                'rx_rate' => $station['rx_rate'] ?? 0,
                'tx_rate' => $station['tx_rate'] ?? 0,
            ];
        }
        
        return $clients;
    }
    
    /**
     * Get clients from old interface
     * 
     * @return array|false
     */
    private function getClientsOldInterface()
    {
        $result = $this->httpRequest('/userRpm/WlanStationRpm.htm', 'GET');
        
        if ($result === false || $result['code'] != 200) {
            return false;
        }
        
        $html = $result['body'];
        $clients = [];
        
        // Parse wireless stations from HTML table
        if (preg_match_all('/var\s+hostList\s*=\s*new\s+Array\(([^)]+)\)/i', $html, $matches)) {
            // Parse MAC addresses from array
            $macList = explode(',', $matches[1][0]);
            foreach ($macList as $mac) {
                $mac = trim($mac, "'\" ");
                if (!empty($mac)) {
                    $clients[] = [
                        'mac' => $mac,
                        'ip' => '',
                        'signal' => null,
                        'connected_time' => 0,
                        'rx_rate' => 0,
                        'tx_rate' => 0,
                    ];
                }
            }
        }
        
        return $clients;
    }
    
    /**
     * Get traffic statistics
     * 
     * @return array|false
     */
    public function getTrafficStats()
    {
        if (!$this->connected && !$this->connect()) {
            return false;
        }
        
        try {
            $status = $this->getStatus();
            
            if (!$status) {
                return false;
            }
            
            // Get detailed stats
            if ($this->isNewInterface) {
                $result = $this->httpRequest('/cgi-bin/luci/;stok=' . $this->token . '/admin/status?form=statistics', 'GET', [], [
                    'X-Requested-With: XMLHttpRequest',
                ]);
                
                if ($result !== false && $result['code'] == 200) {
                    $data = $this->parseJson($result['body']);
                    if ($data && isset($data['data'])) {
                        return [
                            'wan_rx_bytes' => $data['data']['wan_rx_bytes'] ?? 0,
                            'wan_tx_bytes' => $data['data']['wan_tx_bytes'] ?? 0,
                            'wan_rx_packets' => $data['data']['wan_rx_packets'] ?? 0,
                            'wan_tx_packets' => $data['data']['wan_tx_packets'] ?? 0,
                            'lan_rx_bytes' => $data['data']['lan_rx_bytes'] ?? 0,
                            'lan_tx_bytes' => $data['data']['lan_tx_bytes'] ?? 0,
                            'wan_rx_formatted' => $this->formatBytes($data['data']['wan_rx_bytes'] ?? 0),
                            'wan_tx_formatted' => $this->formatBytes($data['data']['wan_tx_bytes'] ?? 0),
                        ];
                    }
                }
            }
            
            // Return basic stats if detailed not available
            return [
                'wan_rx_bytes' => 0,
                'wan_tx_bytes' => 0,
                'wan_rx_packets' => 0,
                'wan_tx_packets' => 0,
                'lan_rx_bytes' => 0,
                'lan_tx_bytes' => 0,
                'wan_rx_formatted' => '0 B',
                'wan_tx_formatted' => '0 B',
            ];
            
        } catch (Throwable $e) {
            _log("TPLink getTrafficStats Error: " . $e->getMessage(), 'CPE');
            return false;
        }
    }
    
    /**
     * Reboot the router
     * 
     * @return bool
     */
    public function reboot()
    {
        if (!$this->connected && !$this->connect()) {
            return false;
        }
        
        try {
            if ($this->isNewInterface) {
                $result = $this->httpRequest('/cgi-bin/luci/;stok=' . $this->token . '/admin/system?form=reboot', 'POST', [
                    'operation' => 'reboot',
                ], [
                    'X-Requested-With: XMLHttpRequest',
                ]);
            } else {
                $result = $this->httpRequest('/userRpm/SysRebootRpm.htm?Reboot=Reboot', 'GET');
            }
            
            if ($result === false) {
                return false;
            }
            
            _log("TPLink: Router {$this->ip} reboot initiated", 'CPE');
            return true;
            
        } catch (Throwable $e) {
            _log("TPLink reboot Error: " . $e->getMessage(), 'CPE');
            return false;
        }
    }
    
    /**
     * Parse value from HTML
     * 
     * @param string $html
     * @param string $label
     * @param string $pattern
     * @return string
     */
    private function parseFromHtml($html, $label, $pattern)
    {
        if (preg_match('/' . $label . '\s*[:：]?\s*<[^>]*>\s*(' . $pattern . ')/i', $html, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }
}
