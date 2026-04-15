# Fiber Management - User Guide

## Table of Contents
1. [Overview](#overview)
2. [Getting Started](#getting-started)
3. [OLT Devices Management](#olt-devices-management)
4. [ONU Management](#onu-management)
5. [Profile Management](#profile-management)
6. [CPE Router Management](#cpe-router-management)
7. [Monitoring Dashboard](#monitoring-dashboard)
8. [Common Workflows](#common-workflows)
9. [Troubleshooting](#troubleshooting)

---

## Overview

Fiber Management is a comprehensive system for managing Fiber-to-the-Home (FTTH) networks. It allows you to:

- **Manage OLT Devices** - Configure and monitor Optical Line Terminals
- **Manage ONUs** - Track and configure Optical Network Units
- **Create Profiles** - Define bandwidth profiles for customers
- **Monitor Network** - View real-time statistics and status
- **Manage CPE Routers** - Configure customer routers remotely

---

## Getting Started

### Prerequisites

1. **Database Setup**
   - Run OLT tables migration: `http://localhost/phpnuxbill/create_olt_tables.php`
   - Run CPE tables migration: `http://localhost/phpnuxbill/create_cpe_tables.php`

2. **Access Requirements**
   - Admin or SuperAdmin account
   - Navigate to: **Fiber Management** menu in admin panel

### Accessing Fiber Management

1. Login to phpNuxBill admin panel
2. Click on **"Fiber Management"** in the left sidebar menu
3. You'll see sub-menus:
   - OLT Devices
   - ONUs
   - Profiles
   - Monitoring
   - CPE Routers

---

## OLT Devices Management

### Purpose
Manage your Optical Line Terminal (OLT) devices - the central equipment that connects to multiple ONUs.

### Adding an OLT Device

**Step 1: Navigate to OLT Devices**
- Go to **Fiber Management > OLT Devices**

**Step 2: Click "New OLT Device"**

**Step 3: Fill in the Form**

| Field | Description | Example |
|-------|-------------|---------|
| **Name** | Friendly name for the OLT | "Main Office OLT" |
| **Brand** | OLT manufacturer | Huawei, ZTE, BDCOM, VSOL |
| **IP Address** | Management IP address | 192.168.1.10 |
| **Port** | Telnet/SSH port | 23 (Telnet) or 22 (SSH) |
| **Username** | Login username | admin |
| **Password** | Login password | (will be encrypted) |
| **Protocol** | Connection protocol | Telnet, SSH, SNMP |
| **SNMP Community** | SNMP community string | public |
| **SNMP Version** | SNMP version | 2c |
| **Description** | Optional notes | "Primary OLT for downtown area" |
| **Coordinates** | GPS coordinates (optional) | -6.2088, 106.8456 |
| **Enabled** | Enable/disable OLT | ✓ Enabled |

**Step 4: Click "Save"**

**Example:**
```
Name: Main Office OLT
Brand: Huawei
IP Address: 192.168.1.10
Port: 23
Username: admin
Password: ********
Protocol: Telnet
Description: Primary OLT serving 500 customers
Enabled: ✓
```

### Editing an OLT Device

1. Go to **Fiber Management > OLT Devices**
2. Find the OLT in the list
3. Click the **Edit** button (pencil icon)
4. Modify the fields
5. Click **"Update"**

### Testing OLT Connection

1. Go to **OLT Devices** list
2. Click **"Test Connection"** button (info icon)
3. System will attempt to connect and show status

### Deleting an OLT Device

1. Go to **OLT Devices** list
2. Click **"Delete"** button (trash icon)
3. Confirm deletion

**⚠️ Warning:** Deleting an OLT will remove all associated ONUs and profiles!

---

## ONU Management

### Purpose
Manage Optical Network Units (ONUs) - the devices installed at customer premises.

### Adding an ONU

**Step 1: Navigate to ONUs**
- Go to **Fiber Management > ONUs**

**Step 2: Click "New ONU"**

**Step 3: Fill in the Form**

| Field | Description | Example |
|-------|-------------|---------|
| **OLT** | Select OLT device | Main Office OLT |
| **Serial Number** | ONU serial number | HWTC12345678 |
| **ONU ID** | ONU identifier | 0/1/2 |
| **PON Port** | PON port number | 0/1 |
| **Customer** | Link to customer | John Doe |
| **Plan** | Billing plan | Fiber 100Mbps |
| **Status** | ONU status | Active, Inactive, Suspended |
| **Notes** | Optional notes | "Installed on 2024-01-15" |

**Step 4: Click "Save"**

**Example:**
```
OLT: Main Office OLT
Serial Number: HWTC12345678
ONU ID: 0/1/2
PON Port: 0/1
Customer: John Doe (ID: 123)
Plan: Fiber 100Mbps
Status: Active
```

### Linking ONU to Customer

**Method 1: During ONU Creation**
1. When adding ONU, select customer from dropdown
2. Select billing plan
3. Save

**Method 2: Edit Existing ONU**
1. Go to **ONUs** list
2. Click **Edit** on the ONU
3. Select customer and plan
4. Update

### ONU Actions

**Sync Status**
- Click **"Sync"** button (refresh icon)
- System queries OLT for current ONU status
- Updates signal strength, uptime, etc.

**Activate ONU**
- Click **"Activate"** button (checkmark icon)
- Activates ONU on the OLT
- Links to customer's billing plan

**Suspend ONU**
- Click **"Suspend"** button (pause icon)
- Suspends ONU service
- Customer loses internet access

**Change Profile**
1. Click **"Edit"** button
2. Select new plan/profile
3. Click **"Update"**
4. System automatically changes ONU profile on OLT

### ONU Status Indicators

| Status | Meaning | Icon Color |
|--------|---------|------------|
| **Active** | ONU is online and active | Green |
| **Inactive** | ONU is offline | Red |
| **Suspended** | Service suspended | Yellow |
| **Unknown** | Status not determined | Gray |

---

## Profile Management

### Purpose
Create bandwidth profiles that define speed limits for customers. Profiles are linked to billing plans.

### Creating a Profile

**Step 1: Navigate to Profiles**
- Go to **Fiber Management > Profiles**

**Step 2: Click "New Profile"**

**Step 3: Fill in the Form**

| Field | Description | Example |
|-------|-------------|---------|
| **Profile Name** | Unique profile name | "100Mbps-Down-50Mbps-Up" |
| **OLT** | Select OLT device | Main Office OLT |
| **Download Speed** | Download speed in Mbps | 100 |
| **Upload Speed** | Upload speed in Mbps | 50 |
| **Plan** | Link to billing plan | Fiber 100Mbps Plan |
| **Description** | Optional description | "Standard residential plan" |

**Step 4: Click "Save"**

**Example:**
```
Profile Name: 100Mbps-Down-50Mbps-Up
OLT: Main Office OLT
Download Speed: 100 Mbps
Upload Speed: 50 Mbps
Plan: Fiber 100Mbps Plan
Description: Standard residential fiber plan
```

### Linking Profile to Billing Plan

1. Create or edit a billing plan
2. Set plan type to **"OLT"**
3. Create a profile with matching speeds
4. Link profile to plan in profile settings

**Workflow:**
```
1. Create Plan: "Fiber 100Mbps Plan" (Type: OLT)
2. Create Profile: "100Mbps-Down-50Mbps-Up"
3. Link Profile to Plan
4. Assign Plan to Customer
5. System automatically applies profile to ONU
```

### Editing a Profile

1. Go to **Profiles** list
2. Click **"Edit"** button
3. Modify speeds or plan link
4. Click **"Update"**

**⚠️ Note:** Changing a profile will affect all ONUs using that profile!

### Deleting a Profile

1. Go to **Profiles** list
2. Click **"Delete"** button
3. Confirm deletion

**⚠️ Warning:** Cannot delete profile if ONUs are using it!

---

## CPE Router Management

### Purpose
Manage customer routers (CPE - Customer Premises Equipment) remotely. Reset, reboot, configure WiFi, DHCP, and more.

### Adding a Customer Router

**Step 1: Navigate to CPE Routers**
- Go to **Fiber Management > CPE Routers**

**Step 2: Click "New CPE Router"**

**Step 3: Fill in the Form**

| Field | Description | Example |
|-------|-------------|---------|
| **Customer** | Select customer | John Doe |
| **ONU** | Link to ONU (optional) | HWTC12345678 |
| **Router MAC Address** | Router MAC (unique) | 00:11:22:33:44:55 |
| **Router IP Address** | Router LAN IP | 192.168.1.1 |
| **Router Brand** | Router manufacturer | TP-Link |
| **Router Model** | Router model | Archer C6 |
| **Management Protocol** | How to connect | HTTP, SNMP, SSH |
| **HTTP Port** | HTTP port (if HTTP) | 80 |
| **SSH Port** | SSH port (if SSH) | 22 |
| **SNMP Community** | SNMP community | public |
| **Username** | Router admin username | admin |
| **Password** | Router admin password | (encrypted) |
| **Notes** | Optional notes | "Router installed 2024-01-15" |

**Step 4: Click "Save"**

**Example:**
```
Customer: John Doe
Router MAC: 00:11:22:33:44:55
Router IP: 192.168.1.1
Brand: TP-Link
Model: Archer C6
Protocol: HTTP
HTTP Port: 80
Username: admin
Password: ********
```

### Supported Router Brands

The system supports these router brands:
- **TP-Link** (Archer, Deco, TL series)
- **Huawei** (HG, EchoLife series)
- **ZTE** (ZXHN series)
- **Fiberhome** (AN series)
- **D-Link** (DIR series)
- **Tenda** (AC series)
- **Netgear**
- **Linksys**
- **Mikrotik**
- **Generic** (Unbranded routers)

### Router Actions

**View Status**
1. Click **"Status"** button (info icon)
2. View router information:
   - Online/Offline status
   - Uptime
   - WAN IP address
   - LAN IP address
   - Firmware version

**Configure Router**
1. Click **"Configure"** button (gear icon)
2. Configure sections:
   - **WiFi Settings** - SSID, password, 2.4GHz/5GHz
   - **DHCP Settings** - Enable/disable, IP range
   - **LAN Settings** - IP address, subnet mask

**Reboot Router**
1. Click **"Reboot"** button (power icon)
2. Confirm reboot
3. Router will restart remotely

**Factory Reset**
1. Click **"Reset"** button (refresh icon)
2. Confirm reset
3. ⚠️ **Warning:** This will erase all router settings!

**Edit Router**
1. Click **"Edit"** button (pencil icon)
2. Modify router information
3. Update credentials if needed
4. Click **"Update"**

**Delete Router**
1. Click **"Delete"** button (trash icon)
2. Confirm deletion

### Configuring WiFi

**Step 1: Go to Router Configuration**
- Click **"Configure"** button on router

**Step 2: WiFi Settings**
- **SSID:** Network name (e.g., "CustomerName-WiFi")
- **Password:** WiFi password (minimum 8 characters)
- **Band:** 2.4GHz, 5GHz, or Both
- Click **"Save WiFi Settings"**

**Example:**
```
SSID: JohnDoe-WiFi
Password: SecurePass123
Band: Both (2.4GHz + 5GHz)
```

### Configuring DHCP

**Step 1: Go to Router Configuration**
- Click **"Configure"** button

**Step 2: DHCP Settings**
- **Enable DHCP:** ✓ Yes / ✗ No
- **Range Start:** First IP (e.g., 192.168.1.100)
- **Range End:** Last IP (e.g., 192.168.1.200)
- Click **"Save DHCP Settings"**

**Example:**
```
Enable DHCP: ✓ Yes
Range Start: 192.168.1.100
Range End: 192.168.1.200
```

### Configuring LAN

**Step 1: Go to Router Configuration**
- Click **"Configure"** button

**Step 2: LAN Settings**
- **LAN IP:** Router IP address (e.g., 192.168.1.1)
- **Subnet Mask:** Network mask (e.g., 255.255.255.0)
- Click **"Save LAN Settings"**

**Example:**
```
LAN IP: 192.168.1.1
Subnet Mask: 255.255.255.0
```

---

## Monitoring Dashboard

### Purpose
View real-time statistics and status of your fiber network.

### Accessing Monitoring

1. Go to **Fiber Management > Monitoring**
2. View dashboard with:
   - Total OLTs
   - Online OLTs
   - Total ONUs
   - Active ONUs
   - Suspended ONUs
   - OLT status overview

### Dashboard Sections

**OLT Status Overview**
- List of all OLTs
- Connection status (Online/Offline)
- Number of ONUs per OLT
- Quick actions (View ONUs, Test Connection)

**ONU Statistics**
- Total ONUs
- Active ONUs
- Inactive ONUs
- Suspended ONUs

**Quick Actions**
- View ONUs by OLT
- Test OLT connections
- View detailed statistics

---

## Common Workflows

### Workflow 1: Adding a New Customer

**Complete Setup Process:**

1. **Add OLT Device** (if not exists)
   - Fiber Management > OLT Devices > New OLT Device
   - Configure OLT connection details

2. **Create Profile**
   - Fiber Management > Profiles > New Profile
   - Set download/upload speeds
   - Link to billing plan

3. **Create Billing Plan**
   - Plans > New Plan
   - Set type to **"OLT"**
   - Set pricing

4. **Add ONU**
   - Fiber Management > ONUs > New ONU
   - Enter serial number
   - Select OLT and PON port
   - Link to customer and plan

5. **Activate ONU**
   - Click **"Activate"** button on ONU
   - System configures ONU on OLT
   - Customer gets internet access

6. **Add Customer Router** (optional)
   - Fiber Management > CPE Routers > New CPE Router
   - Enter router MAC and IP
   - Configure management credentials
   - Link to customer/ONU

### Workflow 2: Suspending a Customer

**When customer doesn't pay:**

1. **Suspend ONU**
   - Go to ONUs list
   - Find customer's ONU
   - Click **"Suspend"** button
   - ONU is deactivated on OLT
   - Customer loses internet

2. **Optional: Suspend Router**
   - Go to CPE Routers
   - Find customer's router
   - Click **"Reboot"** or **"Reset"**

### Workflow 3: Changing Customer Plan

**Upgrade/Downgrade Process:**

1. **Change Customer Plan**
   - Customers > Edit Customer
   - Select new plan
   - Save

2. **Update ONU Profile**
   - Fiber Management > ONUs
   - Find customer's ONU
   - Click **"Edit"**
   - Select new profile
   - Click **"Update"**
   - System automatically changes ONU profile on OLT

### Workflow 4: Troubleshooting Customer Connection

**Diagnostic Steps:**

1. **Check ONU Status**
   - Fiber Management > ONUs
   - Find customer's ONU
   - Check status (Active/Inactive)
   - Click **"Sync"** to refresh status
   - Check signal strength

2. **Check Router Status**
   - Fiber Management > CPE Routers
   - Find customer's router
   - Click **"Status"** button
   - Check if router is online

3. **Test OLT Connection**
   - Fiber Management > OLT Devices
   - Click **"Test Connection"**
   - Verify OLT is reachable

4. **Reboot Router** (if needed)
   - CPE Routers > Click **"Reboot"**
   - Wait 2-3 minutes
   - Check status again

---

## Troubleshooting

### Problem: "OLT tables not found" Error

**Solution:**
1. Run database migration: `http://localhost/phpnuxbill/create_olt_tables.php`
2. Refresh the page

### Problem: "CPE router tables not found" Error

**Solution:**
1. Run database migration: `http://localhost/phpnuxbill/create_cpe_tables.php`
2. Refresh the page

### Problem: Cannot Connect to OLT

**Checklist:**
- ✓ OLT IP address is correct
- ✓ Port number is correct (23 for Telnet, 22 for SSH)
- ✓ Username and password are correct
- ✓ OLT is powered on and connected to network
- ✓ Firewall allows connection from phpNuxBill server
- ✓ OLT management interface is enabled

**Solution:**
1. Test connection manually via Telnet/SSH
2. Verify credentials
3. Check network connectivity
4. Review OLT device settings

### Problem: ONU Not Activating

**Checklist:**
- ✓ ONU serial number is correct
- ✓ ONU ID format is correct (e.g., 0/1/2)
- ✓ PON port is correct
- ✓ Profile exists and is linked to plan
- ✓ OLT connection is working

**Solution:**
1. Verify ONU serial number on physical device
2. Check ONU ID format matches OLT requirements
3. Ensure profile is created and linked
4. Try manual activation on OLT CLI

### Problem: Router Actions Not Working

**Checklist:**
- ✓ Router IP address is correct
- ✓ Management protocol matches router capabilities
- ✓ Credentials are correct
- ✓ Router is online and reachable
- ✓ Router supports the management protocol

**Solution:**
1. Ping router IP address
2. Try accessing router web interface manually
3. Verify router brand/model supports selected protocol
4. Check router firmware version
5. Some routers require brand-specific drivers (may need implementation)

### Problem: Profile Not Applying to ONU

**Checklist:**
- ✓ Profile is linked to billing plan
- ✓ Customer has the plan assigned
- ✓ ONU is linked to customer
- ✓ ONU status is Active

**Solution:**
1. Verify profile-plan link
2. Check customer plan assignment
3. Ensure ONU is activated
4. Try manual profile change on OLT

### Problem: Cannot See CPE Routers Menu

**Solution:**
1. Ensure you're logged in as Admin or SuperAdmin
2. Run CPE tables migration
3. Clear browser cache
4. Logout and login again

### Problem: Router Reset Not Working

**Note:** Router reset/reboot functionality requires brand-specific driver implementation. Currently, drivers are skeleton implementations.

**Solution:**
1. Implement brand-specific driver for your router
2. See `OLT_DRIVER_IMPLEMENTATION_GUIDE.md` for details
3. Or use router's web interface manually

---

## Tips & Best Practices

### OLT Management
- ✅ Use descriptive names for OLTs (e.g., "Main Office OLT", "Branch Office OLT")
- ✅ Keep OLT credentials secure and encrypted
- ✅ Test OLT connections regularly
- ✅ Document OLT locations with coordinates

### ONU Management
- ✅ Always verify serial numbers before adding ONUs
- ✅ Link ONUs to customers immediately after installation
- ✅ Use consistent ONU ID format (check OLT documentation)
- ✅ Document installation dates in notes field

### Profile Management
- ✅ Create profiles before creating plans
- ✅ Use descriptive profile names (e.g., "100Mbps-Down-50Mbps-Up")
- ✅ Match profile speeds to plan speeds
- ✅ Don't delete profiles that are in use

### CPE Router Management
- ✅ Always record router MAC address (unique identifier)
- ✅ Use strong router admin passwords
- ✅ Link routers to customers and ONUs for easy tracking
- ✅ Document router models for troubleshooting
- ✅ Test router connectivity before adding

### Security
- ✅ All passwords are encrypted in database
- ✅ Use strong passwords for OLT and router access
- ✅ Regularly update router firmware
- ✅ Limit router management access to trusted networks

---

## Quick Reference

### Menu Locations

| Feature | Menu Path |
|---------|-----------|
| OLT Devices | Fiber Management > OLT Devices |
| ONUs | Fiber Management > ONUs |
| Profiles | Fiber Management > Profiles |
| Monitoring | Fiber Management > Monitoring |
| CPE Routers | Fiber Management > CPE Routers |

### Common Actions

| Action | Location | Button |
|--------|----------|--------|
| Add OLT | OLT Devices | "New OLT Device" |
| Test OLT | OLT Devices | Info icon |
| Add ONU | ONUs | "New ONU" |
| Activate ONU | ONUs | Checkmark icon |
| Suspend ONU | ONUs | Pause icon |
| Sync ONU | ONUs | Refresh icon |
| Create Profile | Profiles | "New Profile" |
| Add Router | CPE Routers | "New CPE Router" |
| Reboot Router | CPE Routers | Power icon |
| Configure Router | CPE Routers | Gear icon |

### Status Colors

| Color | Meaning |
|-------|---------|
| 🟢 Green | Active/Online |
| 🔴 Red | Inactive/Offline |
| 🟡 Yellow | Suspended/Warning |
| ⚪ Gray | Unknown |

---

## Support

For technical issues or feature requests:
- Check documentation files in project root
- Review `OLT_INTEGRATION_IMPLEMENTATION.md`
- Review `CPE_MANAGEMENT_SUMMARY.md`
- Review `OLT_DRIVER_IMPLEMENTATION_GUIDE.md`

---

**Last Updated:** 2025-02-20
**Version:** 1.0
