<?php
/**
 * PHPNuxBill Configuration File
 * 
 * Copy this file to config.php and update the values according to your environment.
 * 
 * IMPORTANT: Never commit config.php to version control!
 */

// Detect protocol (HTTP/HTTPS)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
             (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";

// Check if HTTP_HOST is set, otherwise use a default value or SERVER_NAME
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost');

$baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('APP_URL', $protocol . $host . $baseDir);

/**
 * Application Stage
 * Options: 'Live', 'Dev', 'Demo'
 * 
 * Live: Production mode - errors hidden, logging enabled
 * Dev: Development mode - errors shown, detailed logging
 * Demo: Demo mode - similar to Live but may have restrictions
 */
$_app_stage = 'Live'; // Do not change this unless you know what you're doing

/**
 * Database Configuration - PHPNuxBill Main Database
 */
$db_host    = "localhost";     // Database Host (e.g., localhost, 127.0.0.1, or remote IP)
$db_port    = "";             // Database Port (leave blank for default MySQL port 3306)
$db_user    = "root";         // Database Username
$db_pass    = "";             // Database Password
$db_name    = "phpnuxbill";   // Database Name

/**
 * Optional: Radius Database Configuration
 * Uncomment and configure if you're using FreeRadius with MySQL
 */
/*
$radius_host    = "localhost";     // Radius Database Host
$radius_user    = "root";          // Radius Database Username
$radius_pass    = "";              // Radius Database Password
$radius_name    = "radius";        // Radius Database Name
*/

/**
 * Error Reporting Configuration
 * Automatically configured based on $_app_stage
 */
if($_app_stage != 'Live'){
    // Development/Demo mode - show errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
} else {
    // Production mode - hide errors
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
}

/**
 * Optional: HTTP Proxy Configuration
 * Uncomment if you need to use a proxy for external API calls
 */
/*
$http_proxy     = "proxy.example.com:8080";  // Proxy server:port
$http_proxyauth = "username:password";       // Proxy authentication (if required)
*/

/**
 * Optional: API Secret Key
 * If not set, will be auto-generated on first run
 */
// $api_secret = "your-secret-key-here";
