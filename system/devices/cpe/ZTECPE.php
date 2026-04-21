<?php

/**
 * ZTE CPE Router Driver
 * 
 * Supports ZTE routers and access points via HTTP API
 * Common models: F660, F609, F670L, H267A, MF283+, etc.
 * 
 * Uses ZTE's web interface API
 */

require_once __DIR__ . '/BaseCPE.php';

class ZTECPE extends BaseCPE
{
    private $sessionCookie = '';
    private $token = '';
    
    /**
     * Connect to ZTE CPE
     * 
     * @return bool
     */
    public function connect()
    {
        try {
            if (empty($this->username) || empty($this->password)) {
                _log("ZTECPE: Username and password required", 'CPE');
                return false;
            }
            
            // Get login page to extract any tokens
            $result = $this->httpRequest('/', 'GET');
            
            if ($result === false) {
                return false;
            }
            
            // Try to extract any session cookies
            if (preg_match('/Set-Cookie:\s*([^;]+)/i', $result['body'], $matches)) {
                $this->sessionCookie = $matches[1];
            }
            
            // Login using form-based authentication
            $loginResult = $this->performLogin();
            
            if ($loginResult) {
                $this->connected = true;
                return true;
            }
            
            // Try alternative login method
            return $this->performAlternativeLogin();
            
        } catch (Throwable $e) {
            _log("ZTECPE connect Error: " . $e->getMessage(), 'CPE');
            return false;
        }
    }
    
    /**
     * Perform login
     * 
     * @return bool
     */
    private function performLogin()
    {
        // ZTE typically uses MD5 hashed password
        $passwordHash = md5($this->password);
        
        $data = [
            'Username' => $this->username,
            'Password' => $passwordHash,
            'action' => 'login',
        ];
        
        $result = $this->httpRequest('/login.cgi', 'POST', $data, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        
        if ($result === false) {
            return false;
        }
        
        // Check for successful login indicators
        if ($result['code'] == 200 || $result['code'] == 302) {
            // Extract session cookie if set
            if (preg_match('/Set-Cookie:\s*([^;]+)/i', $result['body'], $matches)) {
                $this->sessionCookie = $matches[1];
            }
            
            // Check for error messages
            if (strpos($result['body'], 'error') === false && 
                strpos($result['body'], 'incorrect') === false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Alternative login method
     * 
     * @return bool
     */
    private function performAlternativeLogin()
    {
        // Try RPC-style login (used in newer ZTE models)
        $data = [
            'id' => 1,
            'jsonrpc' => '2.0',
            'method' => 'login',
            'params' => [
                'Username' => $this->username,
                'Password' => base64_encode($this->password),
            ],
        ];
        
        $result = $this->httpRequest('/jsonrpc', 'POST', json_encode($data), [
            'Content-Type: application/json',
        ]);
        
        if ($result === false || $result['code'] != 200) {
            return false;
        }
        
        $response = $this->parseJson($result['body']);
        
        if ($response && isset($response['result']) && $response['result'] === true) {
            $this->connected = true;
            return true;
        }
        
        return false;
    }
    
    /**
     * Disconnect from CPE
     */
    public function disconnect()
    {
        if ($this->connected) {
            // Logout
            $this->httpRequest('/logout.cgi', 'GET');
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
            // Try JSON-RPC API first
            $result = $this->httpRequest('/jsonrpc', 'POST', json_encode([
                'id' => 2,
                'jsonrpc' => '2.0',
                'method' => 'getDeviceInfo',
                'params' => [],
            ]), [
                'Content-Type: application/json',
                'Cookie: ' . $this->sessionCookie,
            ]);
            
            if ($result !== false && $result['code'] == 200) {
                $data = $this->parseJson($result['body']);
                if ($data && isset($data['result'])) {
                    return $this->parseJsonStatus($data['result']);
                }
            }
            
            // Fall back to HTML parsing
            return $this->parseStatusFromHtml();
            
        } catch (Throwable $e) {
            _log("ZTECPE getStatus Error: " . $e->getMessage(), 'CPE');
            return $this->parseStatusFromHtml();
        }
    }
    
    /**
     * Parse status from JSON result
     * 
     * @param array $result
     * @return array
     */
    private function parseJsonStatus($result)
    {
        return [
            'brand' => 'ZTE',
            'model' => $result['DeviceName'] ?? $result['ModelName'] ?? 'Unknown',
            'firmware' => $result['SoftwareVersion'] ?? 'Unknown',
            'uptime' => $result['UpTime'] ?? 0,
            'uptime_formatted' => $this->formatUptime($result['UpTime'] ?? 0),
            'wan_ip' => $result['WANIP'] ?? $result['ExternalIPAddress'] ?? '',
            'wan_status' => $result['ConnectionStatus'] ?? 'Unknown',
            'lan_ip' => $this->ip,
            'serial_number' => $result['SerialNumber'] ?? '',
        ];
    }
    
    /**
     * Parse status from HTML
     * 
     * @return array|false
     */
    private function parseStatusFromHtml()
    {
        $result = $this->httpRequest('/getpage.lua?pid=123&nextpage=net_wan_status_lua.lua', 'GET', [], [
            'Cookie: ' . $this->sessionCookie,
        ]);
        
        if ($result === false || $result['code'] != 200) {
            // Try alternative page
            $result = $this->httpRequest('/status.html', 'GET');
        }
        
        if ($result === false || $result['code'] != 200) {
            return false;
        }
        
        $html = $result['body'];
        
        $status = [
            'brand' => 'ZTE',
            'model' => $this->parseFromHtml($html, 'Model|Product Name', '[^<]+'),
            'firmware' => $this->parseFromHtml($html, 'Software Version|Firmware Version', '[^<]+'),
            'uptime' => 0,
            'uptime_formatted' => $this->parseFromHtml($html, 'Up Time|System Time', '[^<]+'),
            'wan_ip' => $this->parseFromHtml($html, 'WAN IP Address|IP Address', '[0-9.]+'),
            'wan_status' => $this->parseFromHtml($html, 'Connection Status|Status', '[^<]+'),
            'lan_ip' => $this->ip,
        ];
        
        return $status;
    }
    
    /**
     * Get connected clients
     * 
     * @return array|false
     */
    public function getConnectedClients()
    {
        if (!$this->connected && !$this->connect()) {
            return false;
        }
        
        try {
            // Try JSON-RPC API
            $result = $this->httpRequest('/jsonrpc', 'POST', json_encode([
                'id' => 3,
                'jsonrpc' => '2.0',
                'method' => 'getLanHostInfo',
                'params' => [],
            ]), [
                'Content-Type: application/json',
                'Cookie: ' . $this->sessionCookie,
            ]);
            
            if ($result !== false && $result['code'] == 200) {
                $data = $this->parseJson($result['body']);
                if ($data && isset($data['result']['Hosts'])) {
                    return $this->parseJsonClients($data['result']['Hosts']);
                }
            }
            
            // Fall back to HTML parsing
            return $this->parseClientsFromHtml();
            
        } catch (Throwable $e) {
            _log("ZTECPE getConnectedClients Error: " . $e->getMessage(), 'CPE');
            return $this->parseClientsFromHtml();
        }
    }
    
    /**
     * Parse clients from JSON
     * 
     * @param array $hosts
     * @return array
     */
    private function parseJsonClients($hosts)
    {
        $clients = [];
        
        foreach ($hosts as $host) {
            $clients[] = [
                'mac' => $host['MacAddress'] ?? '',
                'ip' => $host['IPAddress'] ?? '',
                'hostname' => $host['HostName'] ?? '',
                'connected_time' => $host['ConnectedTime'] ?? 0,
                'interface' => $host['InterfaceType'] ?? '',
            ];
        }
        
        return $clients;
    }
    
    /**
     * Parse clients from HTML
     * 
     * @return array|false
     */
    private function parseClientsFromHtml()
    {
        $result = $this->httpRequest('/getpage.lua?pid=123&nextpage=net_dhcp_lua.lua', 'GET', [], [
            'Cookie: ' . $this->sessionCookie,
        ]);
        
        if ($result === false || $result['code'] != 200) {
            // Try alternative page
            $result = $this->httpRequest('/dhcpclient.html', 'GET');
        }
        
        if ($result === false || $result['code'] != 200) {
            return [];
        }
        
        $html = $result['body'];
        $clients = [];
        
        // Parse DHCP client table
        if (preg_match_all('/<tr[^>]*>.*?<td[^>]*>([^<]*)<\/td>.*?<td[^>]*>([0-9.]+)<\/td>.*?<td[^>]*>([0-9a-fA-F:]+)<\/td>.*?<\/tr>/s', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $clients[] = [
                    'hostname' => trim($match[1]),
                    'ip' => trim($match[2]),
                    'mac' => trim($match[3]),
                    'connected_time' => 0,
                ];
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
            // Try JSON-RPC API
            $result = $this->httpRequest('/jsonrpc', 'POST', json_encode([
                'id' => 4,
                'jsonrpc' => '2.0',
                'method' => 'getTrafficStatistics',
                'params' => [],
            ]), [
                'Content-Type: application/json',
                'Cookie: ' . $this->sessionCookie,
            ]);
            
            if ($result !== false && $result['code'] == 200) {
                $data = $this->parseJson($result['body']);
                if ($data && isset($data['result'])) {
                    return $this->parseJsonStats($data['result']);
                }
            }
            
            // Fall back to HTML parsing
            return $this->parseStatsFromHtml();
            
        } catch (Throwable $e) {
            _log("ZTECPE getTrafficStats Error: " . $e->getMessage(), 'CPE');
            return $this->parseStatsFromHtml();
        }
    }
    
    /**
     * Parse stats from JSON
     * 
     * @param array $result
     * @return array
     */
    private function parseJsonStats($result)
    {
        $rxBytes = $result['TotalBytesReceived'] ?? 0;
        $txBytes = $result['TotalBytesSent'] ?? 0;
        
        return [
            'wan_rx_bytes' => $rxBytes,
            'wan_tx_bytes' => $txBytes,
            'wan_rx_packets' => $result['TotalPacketsReceived'] ?? 0,
            'wan_tx_packets' => $result['TotalPacketsSent'] ?? 0,
            'wan_rx_formatted' => $this->formatBytes($rxBytes),
            'wan_tx_formatted' => $this->formatBytes($txBytes),
            'current_rx_rate' => $result['CurrentDownloadRate'] ?? 0,
            'current_tx_rate' => $result['CurrentUploadRate'] ?? 0,
        ];
    }
    
    /**
     * Parse stats from HTML
     * 
     * @return array|false
     */
    private function parseStatsFromHtml()
    {
        $result = $this->httpRequest('/getpage.lua?pid=123&nextpage=net_traffic_lua.lua', 'GET', [], [
            'Cookie: ' . $this->sessionCookie,
        ]);
        
        if ($result === false || $result['code'] != 200) {
            return [
                'wan_rx_bytes' => 0,
                'wan_tx_bytes' => 0,
                'wan_rx_packets' => 0,
                'wan_tx_packets' => 0,
                'wan_rx_formatted' => '0 B',
                'wan_tx_formatted' => '0 B',
            ];
        }
        
        $html = $result['body'];
        
        $stats = [
            'wan_rx_bytes' => 0,
            'wan_tx_bytes' => 0,
            'wan_rx_packets' => 0,
            'wan_tx_packets' => 0,
            'wan_rx_formatted' => '0 B',
            'wan_tx_formatted' => '0 B',
        ];
        
        // Parse traffic values from HTML
        $rxBytes = $this->parseFromHtml($html, 'Bytes Received|RX Bytes', '[\d,]+');
        if (!empty($rxBytes)) {
            $stats['wan_rx_bytes'] = intval(str_replace(',', '', $rxBytes));
        }
        
        $txBytes = $this->parseFromHtml($html, 'Bytes Sent|TX Bytes', '[\d,]+');
        if (!empty($txBytes)) {
            $stats['wan_tx_bytes'] = intval(str_replace(',', '', $txBytes));
        }
        
        $stats['wan_rx_formatted'] = $this->formatBytes($stats['wan_rx_bytes']);
        $stats['wan_tx_formatted'] = $this->formatBytes($stats['wan_tx_bytes']);
        
        return $stats;
    }
    
    /**
     * Reboot the CPE
     * 
     * @return bool
     */
    public function reboot()
    {
        if (!$this->connected && !$this->connect()) {
            return false;
        }
        
        try {
            // Try JSON-RPC first
            $result = $this->httpRequest('/jsonrpc', 'POST', json_encode([
                'id' => 5,
                'jsonrpc' => '2.0',
                'method' => 'reboot',
                'params' => [],
            ]), [
                'Content-Type: application/json',
                'Cookie: ' . $this->sessionCookie,
            ]);
            
            if ($result !== false && $result['code'] == 200) {
                $data = $this->parseJson($result['body']);
                if ($data && isset($data['result']) && $data['result'] === true) {
                    _log("ZTECPE: Router {$this->ip} reboot initiated via JSON-RPC", 'CPE');
                    return true;
                }
            }
            
            // Fall back to CGI reboot
            $result = $this->httpRequest('/reboot.cgi', 'POST', [
                'action' => 'reboot',
            ], [
                'Content-Type: application/x-www-form-urlencoded',
                'Cookie: ' . $this->sessionCookie,
            ]);
            
            if ($result !== false && ($result['code'] == 200 || $result['code'] == 302)) {
                _log("ZTECPE: Router {$this->ip} reboot initiated via CGI", 'CPE');
                return true;
            }
            
            return false;
            
        } catch (Throwable $e) {
            _log("ZTECPE reboot Error: " . $e->getMessage(), 'CPE');
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
