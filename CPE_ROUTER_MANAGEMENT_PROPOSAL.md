# CPE Router Management Extension Proposal

## Current Implementation vs Your Requirement

### What's Currently Implemented (OLT/ONU Management)

The current Fiber Management system manages:
- ✅ **OLT Devices** - The central fiber equipment
- ✅ **ONUs** - Fiber modems at customer premises
- ✅ **ONU Activation/Suspension** - Enable/disable fiber connection
- ✅ **ONU Profiles** - Bandwidth profiles on ONU

**What this controls:**
- Whether customer's fiber line is active or suspended
- Bandwidth limits on the fiber connection
- ONU status monitoring

**What this DOESN'T control:**
- ❌ Customer's router behind the ONU
- ❌ Router configuration (WiFi, DHCP, firewall, etc.)
- ❌ Router reset/reboot
- ❌ Router firmware updates

---

## Your Requirement: Customer Router Management

You want to manage **customer routers/CPE** (Customer Premises Equipment) that are connected **behind** the ONU:

```
OLT → ONU → Customer Router → Customer Devices
         ↑                    ↑
    Currently Managed    You Want This
```

### What You Need:
1. **Reach customer routers** through the OLT/network
2. **Reset customer routers** remotely
3. **Configure customer routers** (WiFi, DHCP, firewall, etc.)
4. **Monitor customer routers** (status, uptime, etc.)
5. **Update router firmware** remotely

---

## Solution: Add CPE Management Layer

### Architecture Overview

```
┌─────────────────────────────────────────┐
│  phpNuxBill Admin Panel                 │
│  ┌───────────────────────────────────┐  │
│  │ Fiber Management (Current)       │  │
│  │ - OLT Devices                     │  │
│  │ - ONUs                            │  │
│  │ - Profiles                        │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ CPE Management (New)              │  │
│  │ - Customer Routers                │  │
│  │ - Router Configuration            │  │
│  │ - Router Reset/Reboot             │  │
│  │ - Router Monitoring               │  │
│  └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
           │                    │
           ▼                    ▼
    ┌──────────┐          ┌──────────┐
    │   OLT    │          │ Customer │
    │          │─────────▶│  Router  │
    │  (ONU)   │  Fiber   │   (CPE)  │
    └──────────┘          └──────────┘
```

---

## Implementation Options

### Option 1: TR-069/TR-098 Protocol (Recommended for ISPs)

**What it is:**
- Standard protocol for remote CPE management
- Used by most ISPs worldwide
- Supports configuration, monitoring, firmware updates

**How it works:**
- CPE (router) connects to ACS (Auto Configuration Server)
- ACS can push configurations, reset, update firmware
- CPE reports status, events to ACS

**Implementation:**
```php
// New table: tbl_cpe_routers
CREATE TABLE tbl_cpe_routers (
    id INT PRIMARY KEY,
    customer_id INT,
    onu_id INT,
    router_mac VARCHAR(17),
    router_ip VARCHAR(45),
    router_model VARCHAR(128),
    firmware_version VARCHAR(64),
    tr069_enabled TINYINT(1),
    acs_url VARCHAR(255),
    connection_request_url VARCHAR(255),
    last_contact DATETIME,
    status ENUM('Online','Offline','Unknown')
);

// TR-069 ACS Server (separate service)
// Uses libraries like:
// - genieacs (Node.js)
// - freeacs (Java)
// - or PHP TR-069 library
```

**Pros:**
- ✅ Industry standard
- ✅ Works with most modern routers
- ✅ Supports all management features
- ✅ Secure (HTTPS)

**Cons:**
- ❌ Requires TR-069 ACS server
- ❌ Routers must support TR-069
- ❌ More complex setup

---

### Option 2: SNMP Management

**What it is:**
- Simple Network Management Protocol
- Most routers support SNMP
- Read/write router configuration

**Implementation:**
```php
// SNMP-based CPE management
class CPERouterSNMP {
    public function resetRouter($router_ip, $community) {
        // SNMP SET command to reboot router
        snmpset($router_ip, $community, '1.3.6.1.4.1.xxx.1.1.0', 'i', '1');
    }
    
    public function getRouterStatus($router_ip, $community) {
        // SNMP GET router status
        return snmpget($router_ip, $community, '1.3.6.1.2.1.1.3.0');
    }
    
    public function configureWifi($router_ip, $ssid, $password) {
        // SNMP SET WiFi configuration
        snmpset($router_ip, $community, 'wifi.ssid.oid', 's', $ssid);
        snmpset($router_ip, $community, 'wifi.password.oid', 's', $password);
    }
}
```

**Pros:**
- ✅ Simple to implement
- ✅ Most routers support it
- ✅ No additional server needed

**Cons:**
- ❌ Limited configuration options
- ❌ Router-specific OIDs needed
- ❌ Security concerns (community strings)

---

### Option 3: Router Web API/SSH

**What it is:**
- Direct HTTP API or SSH access to router
- Router-specific implementation
- Most flexible but brand-specific

**Implementation:**
```php
// Router-specific API management
class CPERouterAPI {
    public function resetRouter($router_ip, $username, $password) {
        // HTTP API call
        $ch = curl_init("http://$router_ip/api/reboot");
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_exec($ch);
    }
    
    public function configureWifi($router_ip, $ssid, $password) {
        // Router-specific API
        $data = [
            'wifi_ssid' => $ssid,
            'wifi_password' => $password
        ];
        // POST to router API
    }
}
```

**Pros:**
- ✅ Full control
- ✅ Router-specific features
- ✅ Direct access

**Cons:**
- ❌ Each router brand needs separate implementation
- ❌ Routers must be accessible from your network
- ❌ Security concerns

---

### Option 4: Hybrid Approach (Recommended)

Combine multiple methods:

1. **TR-069** for routers that support it (best option)
2. **SNMP** for basic operations (reset, status)
3. **HTTP API** for router-specific features
4. **SSH** for advanced configuration

---

## Proposed Implementation

### Phase 1: Database Extension

```sql
-- Customer Router/CPE Table
CREATE TABLE `tbl_cpe_routers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `onu_id` int(11) DEFAULT NULL,
  `router_mac` varchar(17) NOT NULL,
  `router_ip` varchar(45) DEFAULT NULL,
  `router_model` varchar(128) DEFAULT NULL,
  `router_brand` varchar(64) DEFAULT NULL,
  `firmware_version` varchar(64) DEFAULT NULL,
  `management_protocol` enum('TR069','SNMP','HTTP','SSH','None') DEFAULT 'None',
  `management_credentials` text DEFAULT NULL COMMENT 'Encrypted credentials',
  `tr069_enabled` tinyint(1) DEFAULT '0',
  `acs_url` varchar(255) DEFAULT NULL,
  `snmp_community` varchar(64) DEFAULT NULL,
  `last_contact` datetime DEFAULT NULL,
  `status` enum('Online','Offline','Unknown') DEFAULT 'Unknown',
  `uptime` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `onu_id` (`onu_id`),
  KEY `router_mac` (`router_mac`),
  FOREIGN KEY (`customer_id`) REFERENCES `tbl_customers` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`onu_id`) REFERENCES `tbl_olt_onu` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Router Configuration Templates
CREATE TABLE `tbl_cpe_config_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `router_brand` varchar(64) DEFAULT NULL,
  `router_model` varchar(128) DEFAULT NULL,
  `config_json` text NOT NULL COMMENT 'JSON configuration',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Router Actions Log
CREATE TABLE `tbl_cpe_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cpe_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `action` varchar(32) NOT NULL COMMENT 'reset, configure, update_firmware, etc.',
  `action_data` text DEFAULT NULL COMMENT 'JSON data',
  `status` enum('Success','Failed','Pending') DEFAULT 'Pending',
  `error_message` text DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cpe_id` (`cpe_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### Phase 2: CPE Management Interface

Add to Fiber Management menu:
- **CPE Routers** - List all customer routers
- **Router Configuration** - Configure routers
- **Router Actions** - Reset, reboot, update firmware
- **Router Monitoring** - Status, uptime, diagnostics

---

### Phase 3: CPE Driver System

Similar to OLT drivers, create CPE drivers:

```
system/devices/cpe/
├── CPEInterface.php
├── BaseCPE.php
├── TR069CPE.php
├── SNMPCPE.php
├── HTTPAPICPE.php
└── RouterBrands/
    ├── TP-LinkCPE.php
    ├── HuaweiCPE.php
    ├── ZTE-CPECPE.php
    └── GenericCPE.php
```

---

## Quick Implementation Guide

### Step 1: Add CPE Router to ONU

When adding ONU, also add router info:

```php
// In fiber controller - onus/add-post
$onu_data = [
    'customer_id' => $customer_id,
    'olt_id' => $olt_id,
    'serial_number' => $serial_number,
    // ... other ONU fields
];

$onu = ONU::create($onu_data);

// Also create CPE router entry
$cpe = ORM::for_table('tbl_cpe_routers')->create();
$cpe->customer_id = $customer_id;
$cpe->onu_id = $onu->id();
$cpe->router_mac = _post('router_mac');
$cpe->router_ip = _post('router_ip');
$cpe->router_model = _post('router_model');
$cpe->management_protocol = _post('management_protocol');
$cpe->save();
```

### Step 2: Router Management Methods

```php
class CPEManager {
    public static function resetRouter($cpe_id) {
        $cpe = ORM::for_table('tbl_cpe_routers')->find_one($cpe_id);
        $driver = self::getDriver($cpe);
        return $driver->reset();
    }
    
    public static function configureRouter($cpe_id, $config) {
        $cpe = ORM::for_table('tbl_cpe_routers')->find_one($cpe_id);
        $driver = self::getDriver($cpe);
        return $driver->configure($config);
    }
    
    public static function getRouterStatus($cpe_id) {
        $cpe = ORM::for_table('tbl_cpe_routers')->find_one($cpe_id);
        $driver = self::getDriver($cpe);
        return $driver->getStatus();
    }
}
```

---

## Recommendation

**For your use case, I recommend:**

1. **Start with SNMP** - Easiest to implement, works with most routers
2. **Add HTTP API support** - For router-specific features
3. **Consider TR-069 later** - If you have many routers and want enterprise-grade management

**Would you like me to:**
1. ✅ Add CPE Router Management to the Fiber Management system?
2. ✅ Implement SNMP-based router management first?
3. ✅ Create router configuration templates?
4. ✅ Add router reset/reboot functionality?

Let me know which approach you prefer, and I'll implement it!
