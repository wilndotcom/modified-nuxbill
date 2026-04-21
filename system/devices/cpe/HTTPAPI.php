<?php

/**
 * Generic HTTP API CPE Router Driver
 * 
 * Generic driver for CPE routers that support REST/HTTP API
 * Can be used for custom integrations
 */

require_once __DIR__ . '/BaseCPE.php';

class HTTPAPI extends BaseCPE
{
    private $apiKey = '';
    private $authToken = '';
    private $apiVersion = 'v1';
    private $endpoints = [];
    
    /**
     * Constructor
     * 
     * @param string $ip
     * @param string $username
     * @param string $password
     * @param int $port
     * @param string $protocol
     * @param array $options Additional options (api_key, api_version, endpoints)
     */
    public function __construct($ip, $username = '', $password = '', $port = 80, $protocol = 'http', $options = [])
    {
        parent::__construct($ip, $username, $password, $port, $protocol);
        
        if (isset($options['api_key'])) {
            $this->apiKey = $options['api_key'];
        }
        
        if (isset($options['api_version'])) {
            $this->apiVersion = $options['api_version'];
        }
        
        if (isset($options['endpoints'])) {
            $this->endpoints = $options['endpoints'];
        } else {
            // Default endpoints
            $this->endpoints = [
                'login' => '/api/' . $this->apiVersion . '/login',
                'logout' => '/api/' . $this->apiVersion . '/logout',
                'status' => '/api/' . $this->apiVersion . '/status',
                'clients' => '/api/' . $this->apiVersion . '/clients',
                'stats' => '/api/' . $this->apiVersion . '/statistics',
                'reboot' => '/api/' . $this->apiVersion . '/reboot',
            ];
        }
    }
    
    /**
     * Connect to CPE via API
     * 
     * @return bool
     */
    public function connect()
    {
        try {
            // If API key is provided, use it directly
            if (!empty($this->apiKey)) {
                $this->authToken = $this->apiKey;
                $this->connected = true;
                return true;
            }
            
            // Otherwise, perform login
            if (empty($this->username) || empty($this->password)) {
                _log("HTTPAPI: API key or username/password required", 'CPE');
                return false;
            }
            
            $result = $this->httpRequest($this->endpoints['login'], 'POST', [
                'username' => $this->username,
                'password' => $this->password,
            ], [
                'Content-Type: application/x-www-form-urlencoded',
            ]);
            
            if ($result === false || $result['code'] != 200) {
                return false;
            }
            
            $data = $this->parseJson($result['body']);
            
            if (!$data || !isset($data['success']) || !$data['success']) {
                return false;
            }
            
            // Extract auth token
            if (isset($data['token'])) {
                $this->authToken = $data['token'];
            } elseif (isset($data['data']['token'])) {
                $this->authToken = $data['data']['token'];
            } elseif (isset($data['api_key'])) {
                $this->authToken = $data['api_key'];
            }
            
            // Check for session cookie
            if (preg_match('/Set-Cookie:\s*([^;]+)/i', $result['body'], $matches)) {
                $this->sessionCookie = $matches[1];
            }
            
            $this->connected = true;
            return true;
            
        } catch (Throwable $e) {
            _log("HTTPAPI connect Error: " . $e->getMessage(), 'CPE');
            return false;
        }
    }
    
    /**
     * Disconnect from API
     */
    public function disconnect()
    {
        if ($this->connected && !empty($this->endpoints['logout'])) {
            $this->httpRequest($this->endpoints['logout'], 'POST', [], $this->getAuthHeaders());
        }
        
        $this->connected = false;
        $this->authToken = '';
        $this->sessionCookie = '';
    }
    
    /**
     * Get authentication headers
     * 
     * @return array
     */
    private function getAuthHeaders()
    {
        $headers = [
            'Content-Type: application/json',
        ];
        
        if (!empty($this->authToken)) {
            $headers[] = 'Authorization: Bearer ' . $this->authToken;
            $headers[] = 'X-API-Key: ' . $this->authToken;
        }
        
        if (!empty($this->sessionCookie)) {
            $headers[] = 'Cookie: ' . $this->sessionCookie;
        }
        
        return $headers;
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
            $result = $this->httpRequest($this->endpoints['status'], 'GET', [], $this->getAuthHeaders());
            
            if ($result === false || $result['code'] != 200) {
                return false;
            }
            
            $data = $this->parseJson($result['body']);
            
            if (!$data) {
                return false;
            }
            
            // Normalize status data
            $status = [
                'brand' => $data['brand'] ?? $data['manufacturer'] ?? 'Generic',
                'model' => $data['model'] ?? $data['device_name'] ?? 'Unknown',
                'firmware' => $data['firmware'] ?? $data['software_version'] ?? 'Unknown',
                'uptime' => $data['uptime'] ?? $data['system_uptime'] ?? 0,
                'uptime_formatted' => '',
                'wan_ip' => $data['wan_ip'] ?? $data['external_ip'] ?? $data['wan']['ip'] ?? '',
                'wan_status' => $data['wan_status'] ?? $data['connection_status'] ?? 'Unknown',
                'lan_ip' => $data['lan_ip'] ?? $data['internal_ip'] ?? $this->ip,
                'cpu_usage' => $data['cpu_usage'] ?? null,
                'memory_usage' => $data['memory_usage'] ?? $data['ram_usage'] ?? null,
            ];
            
            $status['uptime_formatted'] = $this->formatUptime($status['uptime']);
            
            return $status;
            
        } catch (Throwable $e) {
            _log("HTTPAPI getStatus Error: " . $e->getMessage(), 'CPE');
            return false;
        }
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
            $result = $this->httpRequest($this->endpoints['clients'], 'GET', [], $this->getAuthHeaders());
            
            if ($result === false || $result['code'] != 200) {
                return [];
            }
            
            $data = $this->parseJson($result['body']);
            
            if (!$data) {
                return [];
            }
            
            // Get clients array from various possible structures
            $clients = [];
            
            if (isset($data['clients']) && is_array($data['clients'])) {
                $rawClients = $data['clients'];
            } elseif (isset($data['stations']) && is_array($data['stations'])) {
                $rawClients = $data['stations'];
            } elseif (isset($data['hosts']) && is_array($data['hosts'])) {
                $rawClients = $data['hosts'];
            } elseif (isset($data['data']) && is_array($data['data'])) {
                $rawClients = $data['data'];
            } else {
                return [];
            }
            
            foreach ($rawClients as $client) {
                $clients[] = [
                    'mac' => $client['mac'] ?? $client['mac_address'] ?? '',
                    'ip' => $client['ip'] ?? $client['ip_address'] ?? '',
                    'hostname' => $client['hostname'] ?? $client['name'] ?? $client['device_name'] ?? '',
                    'connected_time' => $client['connected_time'] ?? $client['uptime'] ?? 0,
                    'signal' => $client['signal'] ?? $client['signal_strength'] ?? null,
                    'interface' => $client['interface'] ?? $client['type'] ?? '',
                ];
            }
            
            return $clients;
            
        } catch (Throwable $e) {
            _log("HTTPAPI getConnectedClients Error: " . $e->getMessage(), 'CPE');
            return [];
        }
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
            $result = $this->httpRequest($this->endpoints['stats'], 'GET', [], $this->getAuthHeaders());
            
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
            
            $data = $this->parseJson($result['body']);
            
            if (!$data) {
                return [
                    'wan_rx_bytes' => 0,
                    'wan_tx_bytes' => 0,
                    'wan_rx_packets' => 0,
                    'wan_tx_packets' => 0,
                    'wan_rx_formatted' => '0 B',
                    'wan_tx_formatted' => '0 B',
                ];
            }
            
            // Extract stats from various possible structures
            $rxBytes = $data['wan_rx_bytes'] ?? $data['bytes_received'] ?? $data['rx_bytes'] ?? 0;
            $txBytes = $data['wan_tx_bytes'] ?? $data['bytes_sent'] ?? $data['tx_bytes'] ?? 0;
            
            $stats = [
                'wan_rx_bytes' => intval($rxBytes),
                'wan_tx_bytes' => intval($txBytes),
                'wan_rx_packets' => intval($data['wan_rx_packets'] ?? $data['packets_received'] ?? 0),
                'wan_tx_packets' => intval($data['wan_tx_packets'] ?? $data['packets_sent'] ?? 0),
                'wan_rx_formatted' => $this->formatBytes($rxBytes),
                'wan_tx_formatted' => $this->formatBytes($txBytes),
                'current_rx_rate' => $data['current_rx_rate'] ?? $data['download_rate'] ?? 0,
                'current_tx_rate' => $data['current_tx_rate'] ?? $data['upload_rate'] ?? 0,
            ];
            
            return $stats;
            
        } catch (Throwable $e) {
            _log("HTTPAPI getTrafficStats Error: " . $e->getMessage(), 'CPE');
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
            $result = $this->httpRequest($this->endpoints['reboot'], 'POST', [], $this->getAuthHeaders());
            
            if ($result === false) {
                return false;
            }
            
            if ($result['code'] == 200 || $result['code'] == 202) {
                $data = $this->parseJson($result['body']);
                
                // Check for success in response
                if (!$data || (isset($data['success']) && $data['success'])) {
                    _log("HTTPAPI: Router {$this->ip} reboot initiated", 'CPE');
                    return true;
                }
            }
            
            return false;
            
        } catch (Throwable $e) {
            _log("HTTPAPI reboot Error: " . $e->getMessage(), 'CPE');
            return false;
        }
    }
    
    /**
     * Set custom endpoint
     * 
     * @param string $name
     * @param string $url
     */
    public function setEndpoint($name, $url)
    {
        $this->endpoints[$name] = $url;
    }
    
    /**
     * Get all endpoints
     * 
     * @return array
     */
    public function getEndpoints()
    {
        return $this->endpoints;
    }
}
