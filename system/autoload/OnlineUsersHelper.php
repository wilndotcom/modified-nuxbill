<?php

/**
 * Online Users Helper Class
 * Fetches online users from MikroTik RouterOS with caching
 */

class OnlineUsersHelper
{
    private static $cache_time = 30; // seconds
    
    /**
     * Get online users from all routers
     * @return array
     */
    public static function getOnlineUsers()
    {
        $cache_key = 'online_users_all';
        $cached = self::getCache($cache_key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $results = [
            'hotspot' => [],
            'pppoe' => [],
            'static' => [],
            'total' => 0,
            'routers' => []
        ];
        
        $routers = ORM::for_table('tbl_routers')
            ->where('enabled', '1')
            ->find_many();
        
        foreach ($routers as $router) {
            try {
                $router_data = self::getRouterOnlineUsers($router);
                $results['hotspot'] = array_merge($results['hotspot'], $router_data['hotspot']);
                $results['pppoe'] = array_merge($results['pppoe'], $router_data['pppoe']);
                $results['static'] = array_merge($results['static'], $router_data['static']);
                $results['routers'][$router['name']] = $router_data;
            } catch (Exception $e) {
                // Log error but continue with other routers
                _log("Failed to get online users from router {$router['name']}: " . $e->getMessage(), 'Router');
            }
        }
        
        $results['total'] = count($results['hotspot']) + count($results['pppoe']) + count($results['static']);
        
        self::setCache($cache_key, $results, self::$cache_time);
        
        return $results;
    }
    
    /**
     * Get online users from a specific router
     * @param array $router
     * @return array
     */
    public static function getRouterOnlineUsers($router)
    {
        $results = [
            'hotspot' => [],
            'pppoe' => [],
            'static' => [],
            'total' => 0
        ];
        
        try {
            $client = Mikrotik::getClient($router['ip_address'], $router['username'], $router['password']);
            
            // Get Hotspot active users
            $hotspot = $client->sendSync(new RouterOS\Request('/ip/hotspot/active/print'));
            foreach ($hotspot as $user) {
                $results['hotspot'][] = [
                    'username' => $user->getProperty('user'),
                    'ip' => $user->getProperty('address'),
                    'mac' => $user->getProperty('mac-address'),
                    'uptime' => $user->getProperty('uptime'),
                    'router' => $router['name']
                ];
            }
            
            // Get PPPoE active users
            $pppoe = $client->sendSync(new RouterOS\Request('/ppp/active/print'));
            foreach ($pppoe as $user) {
                if ($user->getProperty('service') == 'pppoe') {
                    $results['pppoe'][] = [
                        'username' => $user->getProperty('name'),
                        'ip' => $user->getProperty('address'),
                        'uptime' => $user->getProperty('uptime'),
                        'router' => $router['name']
                    ];
                }
            }
            
        } catch (Exception $e) {
            throw $e;
        }
        
        $results['total'] = count($results['hotspot']) + count($results['pppoe']) + count($results['static']);
        
        return $results;
    }
    
    /**
     * Get cache
     * @param string $key
     * @return mixed
     */
    private static function getCache($key)
    {
        $cache_file = sys_get_temp_dir() . '/phpnuxbill_' . $key . '.cache';
        
        if (file_exists($cache_file)) {
            $data = unserialize(file_get_contents($cache_file));
            if ($data['time'] > time() - self::$cache_time) {
                return $data['data'];
            }
        }
        
        return null;
    }
    
    /**
     * Set cache
     * @param string $key
     * @param mixed $data
     * @param int $ttl
     */
    private static function setCache($key, $data, $ttl)
    {
        $cache_file = sys_get_temp_dir() . '/phpnuxbill_' . $key . '.cache';
        file_put_contents($cache_file, serialize(['time' => time(), 'data' => $data]));
    }
    
    /**
     * Clear cache
     * @param string $key
     */
    public static function clearCache($key = null)
    {
        if ($key) {
            $cache_file = sys_get_temp_dir() . '/phpnuxbill_' . $key . '.cache';
            if (file_exists($cache_file)) {
                unlink($cache_file);
            }
        } else {
            // Clear all phpnuxbill caches
            foreach (glob(sys_get_temp_dir() . '/phpnuxbill_*.cache') as $file) {
                unlink($file);
            }
        }
    }
}
