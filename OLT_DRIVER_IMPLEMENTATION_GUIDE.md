# OLT Driver Implementation Guide

## Understanding "Skeleton Implementation"

### What is a Skeleton?

A **skeleton implementation** is like a building framework - it has:
- ✅ **Structure** (all methods defined)
- ✅ **Interface compliance** (implements required methods)
- ✅ **Error handling** (basic try-catch blocks)
- ❌ **NO actual functionality** (methods return empty/fake data)
- ❌ **NO real OLT communication** (no commands sent to OLT)

Think of it as a **template** that shows you WHERE to put code, but doesn't actually DO anything yet.

---

## Current State: What's There vs What's Missing

### ✅ What EXISTS (Structure)

```php
// HuaweiOLT.php - Current State
public function activateONU($onu_id, $profile)
{
    if (!$this->isConnected()) {
        if (!$this->connect()) {
            return ['success' => false, 'message' => $this->getLastError()];
        }
    }

    // TODO: Execute commands:
    // 1. "config"
    // 2. "interface gpon X/X"
    // 3. "ont add X/X/X X sn-auth XXXXXXXXXX"
    // 4. "ont port native-vlan X/X/X X profile-id X"
    // 5. "quit"
    // 6. "commit"

    $this->setError('Huawei OLT activation not fully implemented yet');
    return ['success' => false, 'message' => $this->getLastError()];
}
```

**What this does:**
- ✅ Checks connection
- ✅ Has proper structure
- ✅ Returns error format
- ❌ Doesn't actually connect to OLT
- ❌ Doesn't send any commands
- ❌ Always returns failure

---

## What NEEDS to be Implemented

### 1. Connection Method (Telnet/SSH)

**Current (Skeleton):**
```php
public function connect()
{
    // TODO: Implement Telnet/SSH connection
    $this->setError('Huawei OLT driver not fully implemented yet');
    return false;
}
```

**What it SHOULD do:**
- Connect to OLT via Telnet/SSH
- Login with username/password
- Handle authentication
- Keep connection alive
- Return true on success

**Example Implementation Needed:**
```php
public function connect()
{
    try {
        $host = $this->olt_config['ip_address'];
        $port = $this->olt_config['port'] ?? 23;
        $username = $this->olt_config['username'];
        $password = $this->getPassword();
        
        // Option 1: Using phpseclib for SSH
        require_once 'vendor/autoload.php';
        $ssh = new \phpseclib3\Net\SSH2($host, $port);
        if (!$ssh->login($username, $password)) {
            $this->setError('SSH login failed');
            return false;
        }
        $this->connection = $ssh;
        $this->connected = true;
        return true;
        
        // Option 2: Using Telnet (socket)
        // $socket = fsockopen($host, $port, $errno, $errstr, 10);
        // ... handle telnet login ...
    } catch (Exception $e) {
        $this->setError('Connection Error: ' . $e->getMessage());
        return false;
    }
}
```

---

### 2. Command Execution Method

**Current (Skeleton):**
```php
private function sendCommand($command, $timeout = 5)
{
    // TODO: Implement command sending via Telnet/SSH
    return '';
}
```

**What it SHOULD do:**
- Send command to OLT
- Wait for response
- Parse output
- Handle errors
- Return response text

**Example Implementation Needed:**
```php
private function sendCommand($command, $timeout = 5)
{
    if (!$this->isConnected()) {
        return false;
    }
    
    // For SSH (phpseclib)
    $output = $this->connection->exec($command);
    
    // For Telnet (socket)
    // fwrite($this->connection, $command . "\r\n");
    // $output = '';
    // $start = time();
    // while ((time() - $start) < $timeout) {
    //     $output .= fread($this->connection, 4096);
    //     if (strpos($output, $this->prompt) !== false) {
    //         break;
    //     }
    // }
    
    return trim($output);
}
```

---

### 3. ONU Activation Method

**Current (Skeleton):**
```php
public function activateONU($onu_id, $profile)
{
    // TODO: Execute commands:
    // 1. "config"
    // 2. "interface gpon X/X"
    // 3. "ont add X/X/X X sn-auth XXXXXXXXXX"
    // 4. "ont port native-vlan X/X/X X profile-id X"
    // 5. "quit"
    // 6. "commit"
    
    $this->setError('Huawei OLT activation not fully implemented yet');
    return ['success' => false, 'message' => $this->getLastError()];
}
```

**What it SHOULD do:**
- Parse ONU ID (e.g., "1/1/1:1" → slot=1, port=1, pon=1, onu=1)
- Get ONU serial number from database
- Send Huawei CLI commands in sequence
- Handle errors at each step
- Return success/failure

**Example Implementation Needed:**
```php
public function activateONU($onu_id, $profile)
{
    if (!$this->isConnected()) {
        if (!$this->connect()) {
            return ['success' => false, 'message' => $this->getLastError()];
        }
    }
    
    try {
        // Parse ONU ID: "1/1/1:1" → slot=1, port=1, pon=1, onu=1
        $parts = $this->parseONUID($onu_id);
        if (!$parts) {
            return ['success' => false, 'message' => 'Invalid ONU ID format'];
        }
        
        // Get ONU serial number from database
        $onu = $this->findONUByID($onu_id);
        if (!$onu || empty($onu->serial_number)) {
            return ['success' => false, 'message' => 'ONU serial number not found'];
        }
        
        // Get profile ID from database
        $profile_obj = ORM::for_table('tbl_olt_profiles')
            ->where('olt_id', $this->olt_config['id'])
            ->where('profile_name', $profile)
            ->find_one();
        
        if (!$profile_obj) {
            return ['success' => false, 'message' => 'Profile not found'];
        }
        
        // Huawei CLI Commands Sequence
        $commands = [
            'config',  // Enter config mode
            "interface gpon {$parts['slot']}/{$parts['port']}",  // Enter GPON interface
            "ont add {$parts['slot']}/{$parts['port']}/{$parts['pon']} {$parts['onu']} sn-auth {$onu->serial_number}",  // Add ONU
            "ont port native-vlan {$parts['slot']}/{$parts['port']}/{$parts['pon']} {$parts['onu']} eth 1 profile-id {$profile_obj->profile_name}",  // Set profile
            'quit',  // Exit interface
            'quit',  // Exit config mode
            'commit'  // Commit changes
        ];
        
        foreach ($commands as $cmd) {
            $output = $this->sendCommand($cmd);
            
            // Check for errors in output
            if (preg_match('/Error|Failed|Invalid/i', $output)) {
                $this->setError("Command failed: $cmd - $output");
                $this->logAction(null, null, 'activate', null, $onu_id . ':' . $profile, 'Failed', $this->getLastError());
                return ['success' => false, 'message' => $this->getLastError()];
            }
            
            // Small delay between commands
            usleep(100000); // 0.1 second
        }
        
        // Success!
        $this->logAction(null, null, 'activate', null, $onu_id . ':' . $profile, 'Success');
        return ['success' => true, 'message' => 'ONU activated successfully'];
        
    } catch (Exception $e) {
        $this->setError('Activation Error: ' . $e->getMessage());
        $this->logAction(null, null, 'activate', null, $onu_id . ':' . $profile, 'Error', $this->getLastError());
        return ['success' => false, 'message' => $this->getLastError()];
    }
}
```

---

## Comparison: Mikrotik (Working) vs OLT (Skeleton)

### Mikrotik Driver (WORKING Example)

```php
// MikrotikPppoe.php - REAL Implementation
function add_customer($customer, $plan)
{
    $mikrotik = $this->info($plan['routers']);
    $client = $this->getClient($mikrotik['ip_address'], $mikrotik['username'], $mikrotik['password']);
    
    // REAL: Creates RouterOS API client
    $setRequest = new RouterOS\Request('/ppp/secret/add');
    $setRequest->setArgument('name', $customer['username']);
    $setRequest->setArgument('password', $customer['password']);
    $setRequest->setArgument('profile', $plan['name_plan']);
    
    // REAL: Sends actual command to Mikrotik
    $client->sendSync($setRequest);
}
```

**What makes it WORK:**
- ✅ Uses PEAR2\Net\RouterOS library (real API)
- ✅ Actually connects to router
- ✅ Sends real commands
- ✅ Gets real responses

---

### OLT Driver (SKELETON - Not Working)

```php
// HuaweiOLT.php - SKELETON Implementation
public function activateONU($onu_id, $profile)
{
    // TODO: Execute commands:
    // 1. "config"
    // 2. "interface gpon X/X"
    
    $this->setError('Huawei OLT activation not fully implemented yet');
    return ['success' => false, 'message' => $this->getLastError()];
}
```

**What makes it NOT WORK:**
- ❌ No library for Telnet/SSH
- ❌ No actual connection
- ❌ No commands sent
- ❌ Always returns error

---

## What You Need to Complete Implementation

### 1. Choose Connection Library

**Option A: phpseclib (Recommended for SSH)**
```bash
composer require phpseclib/phpseclib
```

**Option B: Native PHP Sockets (For Telnet)**
- No library needed, uses `fsockopen()`

**Option C: SSH2 Extension**
```bash
# Install PHP SSH2 extension
# Then use ssh2_connect()
```

---

### 2. Get OLT Command Documentation

You need the **CLI command reference** for your OLT brand:

**Huawei MA5600/MA5800:**
- `display ont info summary` - List ONUs
- `ont add X/X/X X sn-auth XXXXXXXX` - Add ONU
- `ont deactivate X/X/X X` - Suspend ONU
- `ont port native-vlan X/X/X X eth 1 profile-id X` - Set profile

**ZTE C300/C320:**
- Different command syntax
- Need ZTE documentation

**BDCOM:**
- Different command syntax
- Need BDCOM documentation

---

### 3. Implement Each Method

For each method (`activateONU`, `suspendONU`, etc.), you need to:

1. **Parse inputs** (ONU ID, profile name, etc.)
2. **Build command sequence** (based on OLT CLI)
3. **Send commands** (via Telnet/SSH)
4. **Parse responses** (check for errors)
5. **Handle errors** (log and return)
6. **Return results** (success/failure)

---

## Step-by-Step Implementation Example

### Step 1: Install Library

```bash
cd c:\xampp\htdocs\phpnuxbill
composer require phpseclib/phpseclib
```

### Step 2: Update connect() Method

```php
public function connect()
{
    global $_app_stage;
    if ($_app_stage == 'demo') {
        $this->connected = true;
        return true;
    }

    try {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $host = $this->olt_config['ip_address'];
        $port = $this->olt_config['port'] ?? 23;
        $username = $this->olt_config['username'];
        $password = $this->getPassword();
        $protocol = $this->olt_config['protocol'] ?? 'Telnet';
        
        if ($protocol == 'SSH') {
            $ssh = new \phpseclib3\Net\SSH2($host, $port);
            if (!$ssh->login($username, $password)) {
                $this->setError('SSH login failed');
                return false;
            }
            $this->connection = $ssh;
        } else {
            // Telnet via socket
            $socket = @fsockopen($host, $port, $errno, $errstr, 10);
            if (!$socket) {
                $this->setError("Telnet connection failed: $errstr");
                return false;
            }
            stream_set_timeout($socket, 10);
            $this->connection = $socket;
            
            // Handle telnet login sequence
            $this->telnetLogin($username, $password);
        }
        
        $this->connected = true;
        $this->updateStatus('Online');
        return true;
        
    } catch (Exception $e) {
        $this->setError('Connection Error: ' . $e->getMessage());
        $this->updateStatus('Offline');
        return false;
    }
}
```

### Step 3: Implement sendCommand() Method

```php
private function sendCommand($command, $timeout = 5)
{
    if (!$this->isConnected() || !$this->connection) {
        return false;
    }
    
    $protocol = $this->olt_config['protocol'] ?? 'Telnet';
    
    if ($protocol == 'SSH') {
        // SSH via phpseclib
        $output = $this->connection->exec($command);
        return trim($output);
    } else {
        // Telnet via socket
        fwrite($this->connection, $command . "\r\n");
        $output = '';
        $start = time();
        
        while ((time() - $start) < $timeout) {
            $data = fread($this->connection, 4096);
            if ($data === false || empty($data)) {
                break;
            }
            $output .= $data;
            
            // Check for prompt (e.g., ">" or "#")
            if (preg_match('/[>#]\s*$/', $output)) {
                break;
            }
        }
        
        // Remove command echo and prompt
        $output = str_replace($command, '', $output);
        $output = preg_replace('/[>#]\s*$/', '', $output);
        return trim($output);
    }
}
```

### Step 4: Implement activateONU() Method

```php
public function activateONU($onu_id, $profile)
{
    if (!$this->isConnected()) {
        if (!$this->connect()) {
            return ['success' => false, 'message' => $this->getLastError()];
        }
    }
    
    try {
        // Parse ONU ID
        $parts = $this->parseONUID($onu_id);
        if (!$parts) {
            return ['success' => false, 'message' => 'Invalid ONU ID format'];
        }
        
        // Get ONU from database
        $onu = $this->findONUByID($onu_id);
        if (!$onu || empty($onu->serial_number)) {
            return ['success' => false, 'message' => 'ONU serial number not found'];
        }
        
        // Huawei command sequence
        $commands = [
            'config',
            "interface gpon {$parts['slot']}/{$parts['port']}",
            "ont add {$parts['slot']}/{$parts['port']}/{$parts['pon']} {$parts['onu']} sn-auth {$onu->serial_number}",
            "ont port native-vlan {$parts['slot']}/{$parts['port']}/{$parts['pon']} {$parts['onu']} eth 1 profile-id {$profile}",
            'quit',
            'quit',
            'commit'
        ];
        
        foreach ($commands as $cmd) {
            $output = $this->sendCommand($cmd);
            
            if (preg_match('/Error|Failed|Invalid|already exists/i', $output)) {
                $this->setError("Command failed: $cmd\nOutput: $output");
                $this->logAction($onu->id(), $onu->customer_id, 'activate', null, $onu_id . ':' . $profile, 'Failed', $this->getLastError());
                return ['success' => false, 'message' => $this->getLastError()];
            }
            
            usleep(200000); // 0.2 second delay
        }
        
        $this->logAction($onu->id(), $onu->customer_id, 'activate', null, $onu_id . ':' . $profile, 'Success');
        return ['success' => true, 'message' => 'ONU activated successfully'];
        
    } catch (Exception $e) {
        $this->setError('Activation Error: ' . $e->getMessage());
        return ['success' => false, 'message' => $this->getLastError()];
    }
}
```

---

## Summary

### Current State (Skeleton)
- ✅ **Structure**: All methods defined
- ✅ **Interface**: Implements OLTInterface
- ✅ **Error Handling**: Basic try-catch
- ❌ **Functionality**: No real OLT communication
- ❌ **Commands**: No actual commands sent

### What You Need to Do
1. **Install library** (phpseclib for SSH or use sockets for Telnet)
2. **Get OLT documentation** (CLI command reference)
3. **Implement connect()** (Telnet/SSH login)
4. **Implement sendCommand()** (Send commands, get responses)
5. **Implement each method** (activateONU, suspendONU, etc.)
6. **Test with real OLT** (Verify commands work)

### Why Skeletons Exist
- **Framework ready**: Structure is there, just needs filling
- **Easy to extend**: Add one brand at a time
- **No breaking changes**: System works even with skeletons
- **Clear path**: Comments show what needs to be done

---

## Next Steps

1. **Start with one brand** (e.g., Huawei if you have Huawei OLT)
2. **Get OLT CLI documentation** for that brand
3. **Test commands manually** via Telnet/SSH first
4. **Implement connect()** and test connection
5. **Implement one method** (e.g., activateONU) and test
6. **Repeat** for other methods

The skeleton gives you the **structure** - you just need to fill in the **actual OLT commands**!
