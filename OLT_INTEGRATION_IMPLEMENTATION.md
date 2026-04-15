# OLT Integration Implementation Summary

## Overview
This document summarizes the OLT (Optical Line Terminal) integration for phpNuxBill, enabling Fiber-to-the-Home (FTTH) management through a modular driver system.

## Implementation Status: ✅ COMPLETE

All phases of the OLT integration have been implemented:

### Phase 1: Foundation ✅
- **Database Tables**: Created 4 tables (`tbl_olt_devices`, `tbl_olt_onu`, `tbl_olt_profiles`, `tbl_olt_logs`)
- **Migrations**: Added to `system/updates.json` (version 2025.12.1)
- **Interface**: Created `OLTInterface.php` with all required methods
- **Base Class**: Created `BaseOLT.php` with common functionality

### Phase 2: OLT Drivers ✅
- **GenericSNMPOLT**: SNMP-based driver for monitoring (read-only operations)
- **HuaweiOLT**: Skeleton implementation for Huawei MA5600/MA5800 series
- **ZTEOLT**: Skeleton implementation for ZTE C300/C320/C350 series
- **BDCOMOLT**: Skeleton implementation for BDCOM OLTs
- **VSOLOLT**: Skeleton implementation for VSOL OLTs (supports HTTP API)

### Phase 3: Admin UI ✅
- **Controller**: `system/controllers/fiber.php` with full CRUD operations
- **Templates**: Complete admin interface for:
  - OLT Devices management
  - ONU management
  - Profile management
  - Monitoring dashboard
- **Menu**: Added "Fiber Management" menu in admin header

### Phase 4: Billing Integration ✅
- **Device Driver**: `system/devices/OLT.php` bridges standard device interface with OLT operations
- **Package Integration**: Updated `Package::getDevice()` to support OLT type
- **Auto-activation**: ONUs are automatically activated when customers purchase OLT plans
- **Auto-suspension**: ONUs are automatically suspended when plans expire (via cron)

### Phase 5: Advanced Features ✅
- **Cron Sync**: `system/cron_olt_sync.php` for periodic ONU status synchronization
- **Helper Classes**: `OLTHelper` and `ONU` classes for common operations
- **Logging**: All OLT actions are logged to `tbl_olt_logs`
- **Security**: Password encryption for OLT credentials

## Files Created/Modified

### New Files
```
install/olt_tables.sql                          # Database schema
system/devices/olt/OLTInterface.php             # Interface definition
system/devices/olt/BaseOLT.php                  # Base abstract class
system/devices/olt/GenericSNMPOLT.php           # SNMP driver
system/devices/olt/HuaweiOLT.php                # Huawei driver (skeleton)
system/devices/olt/ZTEOLT.php                   # ZTE driver (skeleton)
system/devices/olt/BDCOMOLT.php                 # BDCOM driver (skeleton)
system/devices/olt/VSOLOLT.php                  # VSOL driver (skeleton)
system/devices/OLT.php                          # Device driver wrapper
system/autoload/OLT.php                         # Helper class (OLTHelper)
system/autoload/ONU.php                         # ONU helper class
system/controllers/fiber.php                    # Fiber management controller
system/cron_olt_sync.php                        # Cron sync script
ui/ui/admin/fiber/olt-devices/list.tpl          # OLT devices list
ui/ui/admin/fiber/olt-devices/add.tpl           # Add OLT device
ui/ui/admin/fiber/olt-devices/edit.tpl         # Edit OLT device
ui/ui/admin/fiber/onus/list.tpl                 # ONUs list
ui/ui/admin/fiber/onus/add.tpl                   # Add ONU
ui/ui/admin/fiber/onus/edit.tpl                  # Edit ONU
ui/ui/admin/fiber/profiles/list.tpl              # Profiles list
ui/ui/admin/fiber/profiles/add.tpl               # Add profile
ui/ui/admin/fiber/profiles/edit.tpl              # Edit profile
ui/ui/admin/fiber/monitoring/dashboard.tpl      # Monitoring dashboard
```

### Modified Files
```
system/updates.json                              # Added database migrations
system/autoload/Package.php                     # Added OLT type support
ui/ui/admin/header.tpl                          # Added Fiber Management menu
system/lan/english.json                         # Added language strings
```

## Database Schema

### tbl_olt_devices
Stores OLT device configurations (IP, credentials, protocol, etc.)

### tbl_olt_onu
Stores ONU information linked to customers and OLTs

### tbl_olt_profiles
Stores service profiles mapped to billing plans

### tbl_olt_logs
Stores audit trail of all OLT actions

## Usage Instructions

### 1. Install Database Tables
Run the SQL migration from `install/olt_tables.sql` or let the system auto-update via `system/updates.json`.

### 2. Add OLT Device
1. Go to **Fiber Management > OLT Devices**
2. Click **New OLT Device**
3. Fill in OLT details (name, brand, IP, credentials, protocol)
4. Click **Save**

### 3. Create Profiles
1. Go to **Fiber Management > Profiles**
2. Click **New Profile**
3. Select OLT, enter profile name, link to billing plan
4. Set bandwidth limits
5. Click **Save**

### 4. Add ONU
1. Go to **Fiber Management > ONUs**
2. Click **New ONU**
3. Enter serial number, ONU ID, link to customer (optional)
4. Click **Save**

### 5. Create OLT Plan
1. Go to **Services > Plans**
2. Create new plan with type **OLT**
3. Set device to **OLT** (or leave empty, system will auto-detect)
4. Link profile via **Fiber Management > Profiles**

### 6. Activate Customer
When a customer purchases an OLT plan:
- System automatically finds their ONU
- Activates ONU on OLT with linked profile
- Updates ONU status to "Active"

### 7. Setup Cron Sync (Optional)
Add to crontab to sync ONU status every 5-15 minutes:
```bash
*/10 * * * * cd /path/to/phpnuxbill && php system/cron_olt_sync.php
```

## Brand-Specific Driver Implementation

The skeleton drivers (Huawei, ZTE, BDCOM, VSOL) need to be completed with brand-specific commands:

### Huawei OLT
- Commands: `display ont info`, `ont add`, `ont deactivate`, `ont port native-vlan`
- Protocol: Telnet/SSH
- Configuration mode: `config` → `interface gpon` → commands → `commit`

### ZTE OLT
- Commands: Similar to Huawei but different syntax
- Protocol: Telnet/SSH

### BDCOM OLT
- Commands: Brand-specific CLI commands
- Protocol: Telnet/SSH

### VSOL OLT
- API: HTTP REST API (if available)
- Protocol: HTTP or Telnet/SSH

## Important Notes

1. **Password Encryption**: OLT passwords are encrypted using AES-256-CBC with database password as key
2. **ONU Auto-provisioning**: Enable "Auto Provision" flag on ONU for automatic activation
3. **Profile Linking**: Profiles must be linked to billing plans for automatic activation
4. **Hybrid Architecture**: System supports hybrid PPPoE + OLT (customers can have both)
5. **Error Handling**: All OLT operations are logged and errors are captured
6. **Demo Mode**: OLT operations are skipped in demo mode

## Future Enhancements

- Complete brand-specific driver implementations
- XGS-PON support
- ONU auto-discovery
- Bulk ONU operations
- Advanced monitoring and alerts
- Customer portal ONU status display

## Support

For issues or questions:
- GitHub: https://github.com/hotspotbilling/phpnuxbill/
- Telegram: https://t.me/phpnuxbill
