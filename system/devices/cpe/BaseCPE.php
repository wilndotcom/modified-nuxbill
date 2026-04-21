<?php

/**
 * Base CPE Router Driver
 * 
 * Abstract base class for CPE router drivers.
 * All CPE drivers should extend this class.
 */

abstract class BaseCPE
{
    protected $ip;
    protected $username;
    protected $password;
    protected $port = 80;
    protected $protocol = 'http';
    protected $connected = false;
    protected $timeout = 10;
    
    /**
     * Constructor
     * 
     * @param string $ip CPE IP address
     * @param string $username Login username
     * @param string $password Login password
     * @param int $port HTTP/HTTPS port
     * @param string $protocol http or https
     */
    public function __construct($ip, $username = '', $password = '', $port = 80, $protocol = 'http')
    {
        $this->ip = $ip;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->protocol = $protocol;
    }
    
    /**
     * Connect to CPE router
     * 
     * @return bool
     */
    abstract public function connect();
    
    /**
     * Disconnect from CPE router
     */
    abstract public function disconnect();
    
    /**
     * Get CPE status information
     * 
     * @return array|false
     */
    abstract public function getStatus();
    
    /**
     * Get connected clients/WiFi stations
     * 
     * @return array|false
     */
    abstract public function getConnectedClients();
    
    /**
     * Get traffic statistics
     * 
     * @return array|false
     */
    abstract public function getTrafficStats();
    
    /**
     * Reboot the CPE router
     * 
     * @return bool
     */
    abstract public function reboot();
    
    /**
     * Check if connected
     * 
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }
    
    /**
     * Get CPE info
     * 
     * @return array
     */
    public function getInfo()
    {
        return [
            'ip' => $this->ip,
            'port' => $this->port,
            'protocol' => $this->protocol,
            'connected' => $this->connected,
        ];
    }
    
    /**
     * Helper: Make HTTP request
     * 
     * @param string $url
     * @param string $method
     * @param array $data
     * @param array $headers
     * @return array|false
     */
    protected function httpRequest($url, $method = 'GET', $data = [], $headers = [])
    {
        $ch = curl_init();
        
        $fullUrl = $this->protocol . '://' . $this->ip . ':' . $this->port . $url;
        
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($response === false) {
            _log("BaseCPE HTTP Error: " . $error, 'CPE');
            return false;
        }
        
        return [
            'code' => $httpCode,
            'body' => $response,
        ];
    }
    
    /**
     * Helper: Parse JSON response
     * 
     * @param string $response
     * @return array|false
     */
    protected function parseJson($response)
    {
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            _log("BaseCPE JSON Parse Error: " . json_last_error_msg(), 'CPE');
            return false;
        }
        return $data;
    }
    
    /**
     * Format bytes to human readable
     * 
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Format uptime to human readable
     * 
     * @param int $seconds
     * @return string
     */
    protected function formatUptime($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        $parts = [];
        if ($days > 0) $parts[] = $days . 'd';
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($minutes > 0) $parts[] = $minutes . 'm';
        
        return implode(' ', $parts) ?: '0m';
    }
}
