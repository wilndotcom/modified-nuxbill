<?php

/**
 * Huawei CPE Router Driver
 * 
 * Supports Huawei routers and access points via HTTP API
 * Common models: HG8546M, HG8245, HG8245H, B315, B525, etc.
 * 
 * Uses Huawei's web interface API
 */

require_once __DIR__ . '/BaseCPE.php';

class HuaweiCPE extends BaseCPE
{
    private $sessionCookie = '';
    private $csrfToken = '';
    
    /**
     * Connect to Huawei CPE
     * 
     * @return bool
     */
    public function connect()
    {
        try {
            if (empty($this->username) || empty($this->password)) {
                _log("HuaweiCPE: Username and password required", 'CPE');
                return false;
            }
            
            // Get CSRF token first
            $result = $this->httpRequest('/api/webserver/SesTokInfo', 'GET');
            
            if ($result === false || $result['code'] != 200) {
                // Try alternative login path
                return $this->connectAlternative();
            }
            
            // Parse session info from XML
            if (preg_match('/<SesInfo>([^<]+)<\/SesInfo>/', $result['body'], $matches)) {
                $this->sessionCookie = $matches[1];
            }
            
            if (preg_match('/<TokInfo>([^<]+)<\/TokInfo>/', $result['body'], $matches)) {
                $this->csrfToken = $matches[1];
            }
            
            // Login
            $loginData = $this->encryptPassword($this->username, $this->password, $this->csrfToken);
            
            $result = $this->httpRequest('/api/user/login', 'POST', [
                'Username' => $this->username,
                'Password' => $loginData['password'],
                'password_type' => $loginData['type'],
            ], [
                'Cookie: ' . $this->sessionCookie,
                'Content-Type: application/x-www-form-urlencoded',
            ]);
            
            if ($result === false || $result['code'] != 200) {
                return $this->connectAlternative();
            }
            
            // Check login success
            if (strpos($result['body'], 'OK') !== false || strpos($result['body'], '<code>0</code>') !== false) {
                $this->connected = true;
                return true;
            }
            
            // Try alternative if first method failed
            return $this->connectAlternative();
            
        } catch (Throwable $e) {
            _log("HuaweiCPE connect Error: " . $e->getMessage(), 'CPE');
            return false;
        }
    }
    
    /**
     * Alternative connection method for older/newer models
     * 
     * @return bool
     */
    private function connectAlternative()
    {
        try {
            // Try standard form-based login
            $result = $this->httpRequest('/html/advance.html', 'GET');
            
            if ($result === false || $result['code'] != 200) {
                // Try index page
                $result = $this->httpRequest('/index.html', 'GET');
            }
            
            if ($result === false || $result['code'] != 200) {
                return false;
            }
            
            // Try to extract session info from cookies
            if (preg_match('/Set-Cookie:\s*([^;]+)/i', $result['body'], $matches)) {
                $this->sessionCookie = $matches[1];
            }
            
            // Attempt login with standard credentials
            $result = $this->httpRequest('/api/ntwk/Diary', 'POST', [
                'username' => base64_encode($this->username),
                'password' => base64_encode($this->password),
            ], [
                'Content-Type: application/x-www-form-urlencoded',
            ]);
            
            if ($result !== false && $result['code'] == 200) {
                $this->connected = true;
                return true;
            }
            
            return false;
            
        } catch (Throwable $e) {
            _log("HuaweiCPE connectAlternative Error: " . $e->getMessage(), 'CPE');
            return false;
        }
    }
    
    /**
     * Encrypt password for Huawei login
     * 
     * @param string $username
     * @param string $password
     * @param string $csrfToken
     * @return array
     */
    private function encryptPassword($username, $password, $csrfToken)
    {
        // Huawei uses Base64 encoding with CSRF token
        $encrypted = base64_encode($username . ':' . $password);
        
        return [
            'password' => $encrypted,
            'type' => 4, // Base64 encoding type
        ];
    }
    
    /**
     * Disconnect from CPE
     */
    public function disconnect()
    {
        if ($this->connected) {
            // Logout
            $this->httpRequest('/api/user/logout', 'POST', [], [
                'Cookie: ' . $this->sessionCookie,
            ]);
        }
        
        $this->connected = false;
        $this->sessionCookie = '';
        $this->csrfToken = '';
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
            $result = $this->httpRequest('/api/device/information', 'GET', [], [
                'Cookie: ' . $this->sessionCookie,
            ]);
            
            if ($result === false || $result['code'] != 200) {
                // Try alternative API
                $result = $this->httpRequest('/api/monitoring/status', 'GET', [], [
                    'Cookie: ' . $this->sessionCookie,
                ]);
            }
            
            if ($result === false || $result['code'] != 200) {
                return $this->parseStatusFromHtml();
            }
            
            // Parse XML response
            $status = $this->parseStatusXml($result['body']);
            
            return $status;
            
        } catch (Throwable $e) {
            _log("HuaweiCPE getStatus Error: " . $e->getMessage(), 'CPE');
            return $this->parseStatusFromHtml();
        }
    }
    
    /**
     * Parse status from XML
     * 
     * @param string $xml
     * @return array
     */
    private function parseStatusXml($xml)
    {
        $status = [
            'brand' => 'Huawei',
            'model' => 'Unknown',
            'firmware' => 'Unknown',
            'uptime' => 0,
            'uptime_formatted' => '',
            'wan_ip' => '',
            'wan_status' => 'Unknown',
            'lan_ip' => $this->ip,
            'signal_strength' => null,
            'connection_type' => '',
        ];
        
        // Extract device info
        if (preg_match('/<DeviceName>([^<]+)<\/DeviceName>/', $xml, $matches)) {
            $status['model'] = $matches[1];
        }
        
        if (preg_match('/<SoftwareVersion>([^<]+)<\/SoftwareVersion>/', $xml, $matches)) {
            $status['firmware'] = $matches[1];
        }
        
        if (preg_match('/<UpTime>(\d+)<\/UpTime>/', $xml, $matches)) {
            $status['uptime'] = intval($matches[1]);
            $status['uptime_formatted'] = $this->formatUptime($status['uptime']);
        }
        
        if (preg_match('/<WanIPAddress>([^<]+)<\/WanIPAddress>/', $xml, $matches)) {
            $status['wan_ip'] = $matches[1];
        }
        
        if (preg_match('/<ConnectionStatus>([^<]+)<\/ConnectionStatus>/', $xml, $matches)) {
            $status['wan_status'] = $matches[1];
        }
        
        if (preg_match('/<SignalStrength>([^<]+)<\/SignalStrength>/', $xml, $matches)) {
            $status['signal_strength'] = $matches[1];
        }
        
        return $status;
    }
    
    /**
     * Parse status from HTML (fallback)
     * 
     * @return array|false
     */
    private function parseStatusFromHtml()
    {
        $result = $this->httpRequest('/html/advance.html', 'GET');
        
        if ($result === false || $result['code'] != 200) {
            $result = $this->httpRequest('/content.html', 'GET');
        }
        
        if ($result === false || $result['code'] != 200) {
            return false;
        }
        
        $html = $result['body'];
        
        $status = [
            'brand' => 'Huawei',
            'model' => $this->parseFromHtml($html, 'Product|Model', '[^<]+'),
            'firmware' => $this->parseFromHtml($html, 'Software Version|Firmware', '[^<]+'),
            'uptime' => 0,
            'uptime_formatted' => $this->parseFromHtml($html, 'Uptime|System Time', '[^<]+'),
            'wan_ip' => $this->parseFromHtml($html, 'WAN IP|IP Address', '[0-9.]+'),
            'wan_status' => 'Unknown',
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
            $result = $this->httpRequest('/api/wlan/host-list', 'GET', [], [
                'Cookie: ' . $this->sessionCookie,
            ]);
            
            if ($result === false || $result['code'] != 200) {
                // Try alternative endpoint
                $result = $this->httpRequest('/api/dhcp/user-host', 'GET', [], [
                    'Cookie: ' . $this->sessionCookie,
                ]);
            }
            
            if ($result === false || $result['code'] != 200) {
                return [];
            }
            
            return $this->parseClientsXml($result['body']);
            
        } catch (Throwable $e) {
            _log("HuaweiCPE getConnectedClients Error: " . $e->getMessage(), 'CPE');
            return [];
        }
    }
    
    /**
     * Parse clients from XML
     * 
     * @param string $xml
     * @return array
     */
    private function parseClientsXml($xml)
    {
        $clients = [];
        
        // Match all host entries
        preg_match_all('/<Hosts>(.*?)<\/Hosts>/s', $xml, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $hostXml) {
                $client = [
                    'mac' => '',
                    'ip' => '',
                    'hostname' => '',
                    'connected_time' => 0,
                    'signal' => null,
                ];
                
                if (preg_match('/<MacAddress>([^<]+)<\/MacAddress>/', $hostXml, $m)) {
                    $client['mac'] = $m[1];
                }
                
                if (preg_match('/<IpAddress>([^<]+)<\/IpAddress>/', $hostXml, $m)) {
                    $client['ip'] = $m[1];
                }
                
                if (preg_match('/<HostName>([^<]+)<\/HostName>/', $hostXml, $m)) {
                    $client['hostname'] = $m[1];
                }
                
                if (preg_match('/<AssociatedTime>(\d+)<\/AssociatedTime>/', $hostXml, $m)) {
                    $client['connected_time'] = intval($m[1]);
                }
                
                if (!empty($client['mac'])) {
                    $clients[] = $client;
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
            $result = $this->httpRequest('/api/monitoring/traffic-statistics', 'GET', [], [
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
            
            $stats = [
                'wan_rx_bytes' => 0,
                'wan_tx_bytes' => 0,
                'wan_rx_packets' => 0,
                'wan_tx_packets' => 0,
                'wan_rx_formatted' => '0 B',
                'wan_tx_formatted' => '0 B',
            ];
            
            $xml = $result['body'];
            
            if (preg_match('/<CurrentDownloadRate>(\d+)<\/CurrentDownloadRate>/', $xml, $matches)) {
                $stats['wan_rx_bytes'] = intval($matches[1]);
            }
            
            if (preg_match('/<CurrentUploadRate>(\d+)<\/CurrentUploadRate>/', $xml, $matches)) {
                $stats['wan_tx_bytes'] = intval($matches[1]);
            }
            
            if (preg_match('/<TotalDownload>(\d+)<\/TotalDownload>/', $xml, $matches)) {
                $stats['wan_rx_bytes'] = intval($matches[1]);
            }
            
            if (preg_match('/<TotalUpload>(\d+)<\/TotalUpload>/', $xml, $matches)) {
                $stats['wan_tx_bytes'] = intval($matches[1]);
            }
            
            $stats['wan_rx_formatted'] = $this->formatBytes($stats['wan_rx_bytes']);
            $stats['wan_tx_formatted'] = $this->formatBytes($stats['wan_tx_bytes']);
            
            return $stats;
            
        } catch (Throwable $e) {
            _log("HuaweiCPE getTrafficStats Error: " . $e->getMessage(), 'CPE');
            return false;
        }
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
            $result = $this->httpRequest('/api/device/control', 'POST', [
                'Control' => 1, // Reboot
            ], [
                'Cookie: ' . $this->sessionCookie,
                'Content-Type: application/x-www-form-urlencoded',
            ]);
            
            if ($result === false || $result['code'] != 200) {
                // Try alternative endpoint
                $result = $this->httpRequest('/api/system/Reboot', 'POST', [], [
                    'Cookie: ' . $this->sessionCookie,
                ]);
            }
            
            if ($result !== false && $result['code'] == 200) {
                _log("HuaweiCPE: Router {$this->ip} reboot initiated", 'CPE');
                return true;
            }
            
            return false;
            
        } catch (Throwable $e) {
            _log("HuaweiCPE reboot Error: " . $e->getMessage(), 'CPE');
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
