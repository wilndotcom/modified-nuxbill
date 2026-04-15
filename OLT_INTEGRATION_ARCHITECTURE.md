# PHPNuxBill OLT Integration - Architecture Analysis & Plan

## 1. Current PHPNuxBill Architecture Analysis

### 1.1 How Routers Are Currently Handled

**Device Driver System:**
- Device drivers are stored in `system/devices/` directory
- Each device driver is a PHP class (e.g., `MikrotikHotspot.php`, `MikrotikPppoe.php`)
- Device drivers implement standard methods:
  - `description()` - Returns metadata about the device
  - `add_customer($customer, $plan)` - Adds customer to device
  - `remove_customer($customer, $plan)` - Removes customer from device
  - `sync_customer($customer, $plan)` - Syncs customer with device
  - `add_plan($plan)` - Adds plan/profile to device
  - `update_plan($old_name, $plan)` - Updates plan on device
  - `remove_plan($plan)` - Removes plan from device
  - `online_customer($customer, $router_name)` - Checks if customer is online
  - `connect_customer()` / `disconnect_customer()` - Manual connect/disconnect

**Device Selection:**
- Plans have a `device` field in `tbl_plans` table
- `Package::getDevice($plan)` method determines which device driver to use
- Device path: `$DEVICE_PATH = system/devices/`
- Device is selected based on:
  1. Plan's `device` field (if set)
  2. Plan's `is_radius` flag (uses Radius driver)
  3. Plan's `type` field (PPPOE → MikrotikPppoe, default → MikrotikHotspot)

**Router Management:**
- Routers stored in `tbl_routers` table:
  - `id`, `name`, `ip_address`, `username`, `password`, `description`
  - `coordinates`, `coverage`, `status`, `last_seen`, `enabled`
- Router controller: `system/controllers/routers.php`
- Router UI: `ui/ui/admin/routers/` (list, add, edit templates)

**Billing Integration:**
- When customer purchases/activates plan:
  1. `Package::recharge()` is called
  2. Gets device driver via `Package::getDevice($plan)`
  3. Loads device class: `require_once $dvc; (new $p['device'])->add_customer($c, $p)`
  4. Device driver connects to router and provisions customer
- When plan expires (cron job):
  1. `system/cron.php` runs periodically
  2. Finds expired `tbl_user_recharges` records
  3. Calls `remove_customer()` on device driver
  4. Updates status to 'off'

**Customer-Router Relationship:**
- Customers are assigned to routers via custom field "Router" or router selection
- Plans are assigned to routers via `tbl_plans.routers` field
- Service type stored in `tbl_customers.service_type` (Hotspot/PPPoE/VPN/Others)

### 1.2 Database Structure

**Key Tables:**
- `tbl_routers` - Router/device configurations
- `tbl_plans` - Service plans (has `device` field, `routers` field)
- `tbl_customers` - Customer accounts (has `service_type` field)
- `tbl_user_recharges` - Active subscriptions
- `tbl_transactions` - Payment history
- `tbl_customers_fields` - Custom fields for customers

### 1.3 Admin UI Structure

**Menu System:**
- Menu items registered via `register_menu()` function
- Menu positions: `AFTER_DASHBOARD`, `AFTER_CUSTOMERS`, `AFTER_SERVICES`, `AFTER_NETWORKS`, etc.
- Network menu includes: Routers, IP Pool, Port Pool, ODP List
- Settings menu includes: Devices, Users, etc.

**Controller Pattern:**
- Controllers in `system/controllers/`
- Route structure: `?_route=controller/action/param`
- Controllers check admin permissions: `_admin()`
- Use Smarty templates in `ui/ui/admin/`

### 1.4 Cron & Automation

**Cron System:**
- `system/cron.php` - Main cron handler
- Checks for expired subscriptions
- Calls device driver `remove_customer()` on expiration
- Sends notifications
- Uses lock file to prevent concurrent execution

---

## 2. OLT Integration Architecture Plan

### 2.1 Design Philosophy

**Parallel to Router System:**
- OLTs are similar to routers but manage ONUs (Optical Network Units)
- OLTs will have their own device drivers in `system/devices/olt/` subdirectory
- OLT drivers will implement OLTInterface (similar to how routers work)
- OLTs can work alongside routers (hybrid architecture)

**Key Differences from Routers:**
- OLTs manage ONUs (not users directly)
- ONUs have serial numbers (not usernames)
- OLTs use different protocols (SNMP, Telnet, HTTP API)
- ONU provisioning is different from router user provisioning
- ONUs can be suspended/activated without removing them

### 2.2 Architecture Components

#### A. Database Tables

**`tbl_olt_devices`** - OLT Device Management
```sql
- id (INT, PRIMARY KEY)
- name (VARCHAR) - OLT name/location
- brand (ENUM) - Huawei, ZTE, BDCOM, VSOL, Generic
- ip_address (VARCHAR) - OLT management IP
- port (INT) - Management port (default varies by brand)
- username (VARCHAR) - OLT login username
- password (VARCHAR) - Encrypted password
- protocol (ENUM) - SNMP, Telnet, HTTP, SSH
- snmp_community (VARCHAR) - SNMP community string (if SNMP)
- description (TEXT)
- coordinates (VARCHAR) - GPS coordinates
- status (ENUM) - Online, Offline
- last_seen (DATETIME)
- enabled (TINYINT)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

**`tbl_olt_onu`** - ONU Management
```sql
- id (INT, PRIMARY KEY)
- customer_id (INT, FOREIGN KEY → tbl_customers.id)
- olt_id (INT, FOREIGN KEY → tbl_olt_devices.id)
- serial_number (VARCHAR) - ONU serial number (unique)
- onu_id (VARCHAR) - ONU ID on OLT (e.g., "1/1/1:1")
- mac_address (VARCHAR) - ONU MAC address
- profile_name (VARCHAR) - Current profile/service plan
- status (ENUM) - Active, Suspended, Inactive, Unknown
- last_sync (DATETIME) - Last sync with OLT
- auto_provision (TINYINT) - Auto-provision enabled
- notes (TEXT)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

**`tbl_olt_profiles`** - OLT Service Profiles
```sql
- id (INT, PRIMARY KEY)
- olt_id (INT, FOREIGN KEY → tbl_olt_devices.id)
- profile_name (VARCHAR) - Profile name on OLT
- plan_id (INT, FOREIGN KEY → tbl_plans.id) - Linked billing plan
- bandwidth_down (VARCHAR) - Download bandwidth
- bandwidth_up (VARCHAR) - Upload bandwidth
- description (TEXT)
- enabled (TINYINT)
- created_at (TIMESTAMP)
```

**`tbl_olt_logs`** - ONU Action Logging
```sql
- id (INT, PRIMARY KEY)
- olt_id (INT)
- onu_id (INT, FOREIGN KEY → tbl_olt_onu.id)
- customer_id (INT)
- action (VARCHAR) - activate, suspend, change_profile, sync
- old_value (TEXT) - Previous state
- new_value (TEXT) - New state
- admin_id (INT) - Who performed action
- status (ENUM) - Success, Failed, Error
- error_message (TEXT)
- created_at (TIMESTAMP)
```

#### B. OLT Driver Interface

**Location:** `system/devices/olt/OLTInterface.php`

```php
interface OLTInterface {
    // Connection Management
    public function connect();
    public function disconnect();
    public function isConnected();
    
    // ONU Management
    public function getOnlineONUs();
    public function getONUStatus($onu_id);
    public function getONUInfo($serial_number);
    
    // ONU Provisioning
    public function activateONU($onu_id, $profile);
    public function suspendONU($onu_id);
    public function changeProfile($onu_id, $profile);
    public function removeONU($onu_id);
    
    // Profile Management
    public function getProfiles();
    public function createProfile($profile_name, $bandwidth_down, $bandwidth_up);
    public function updateProfile($profile_name, $bandwidth_down, $bandwidth_up);
    public function deleteProfile($profile_name);
    
    // Utility
    public function description(); // Metadata
    public function testConnection(); // Test OLT connectivity
}
```

#### C. OLT Driver Structure

**Directory:** `system/devices/olt/`

**Files:**
1. `OLTInterface.php` - Interface definition
2. `BaseOLT.php` - Base abstract class with common functionality
3. `HuaweiOLT.php` - Huawei OLT driver
4. `ZTEOLT.php` - ZTE OLT driver
5. `BDCOMOLT.php` - BDCOM OLT driver
6. `VSOLOLT.php` - VSOL OLT driver
7. `GenericSNMPOLT.php` - Generic SNMP-based OLT driver

**Driver Pattern:**
- Each driver extends `BaseOLT` or implements `OLTInterface`
- Drivers handle brand-specific protocols and commands
- Drivers cache connections to avoid overload
- Drivers log all actions to `tbl_olt_logs`

#### D. Integration with Billing System

**Plan Type Extension:**
- Add new plan type: `'OLT'` to `tbl_plans.type` ENUM
- Plans can be linked to OLTs via `tbl_plans.routers` (reuse field, or add `olt_id`)
- Plans linked to OLT profiles via `tbl_olt_profiles.plan_id`

**Customer Activation Flow:**
1. Customer purchases OLT plan
2. System finds ONU by customer_id in `tbl_olt_onu`
3. System gets OLT device driver based on OLT brand
4. Driver activates ONU with linked profile
5. ONU status updated to 'Active'

**Customer Suspension Flow:**
1. Customer payment expires or admin suspends
2. System finds ONU by customer_id
3. Driver suspends ONU (doesn't remove, just disables)
4. ONU status updated to 'Suspended'

**Auto-Provisioning:**
- When ONU is added to `tbl_olt_onu` with `auto_provision=1`
- System automatically provisions ONU when customer activates plan
- ONU gets assigned profile based on purchased plan

#### E. Admin UI Structure

**New Menu: "Fiber Management"**
- Position: After Network menu (`AFTER_NETWORKS`)
- Submenu items:
  - OLT Devices (`fiber/olt-devices`)
  - ONUs (`fiber/onus`)
  - Profiles (`fiber/profiles`)
  - Monitoring (`fiber/monitoring`)

**Controllers:**
- `system/controllers/fiber.php` - Main fiber controller
  - Actions: `olt-devices`, `onus`, `profiles`, `monitoring`
  - Sub-actions: `add`, `edit`, `delete`, `sync`, `activate`, `suspend`

**Templates:**
- `ui/ui/admin/fiber/olt-devices/` - OLT device management
- `ui/ui/admin/fiber/onus/` - ONU management
- `ui/ui/admin/fiber/profiles/` - Profile management
- `ui/ui/admin/fiber/monitoring/` - Monitoring dashboard

#### F. Caching & Performance

**ONU Status Caching:**
- Cache ONU status in `tbl_olt_onu.last_sync`
- Cron job syncs ONU status every 5-15 minutes (configurable)
- Real-time sync only when admin requests or customer activates

**Connection Pooling:**
- BaseOLT class manages connection pooling
- Reuse connections within same request
- Close connections after timeout

**Rate Limiting:**
- Limit OLT API calls per minute
- Queue operations if OLT is busy
- Log rate limit violations

#### G. Security

**Credential Storage:**
- Encrypt OLT passwords in database
- Use PHP encryption functions
- Never log passwords

**Action Logging:**
- Log all ONU actions to `tbl_olt_logs`
- Include admin_id, timestamp, action, result
- Enable audit trail

**Access Control:**
- Only SuperAdmin and Admin can manage OLTs
- Agents/Sales can view but not modify
- Customers cannot access OLT management

---

## 3. File Structure

### Files to Create:

**Database:**
- `install/olt_tables.sql` - Database schema for OLT tables
- `system/updates.json` - Add migration entries

**OLT Drivers:**
- `system/devices/olt/OLTInterface.php`
- `system/devices/olt/BaseOLT.php`
- `system/devices/olt/HuaweiOLT.php`
- `system/devices/olt/ZTEOLT.php`
- `system/devices/olt/BDCOMOLT.php`
- `system/devices/olt/VSOLOLT.php`
- `system/devices/olt/GenericSNMPOLT.php`

**Controllers:**
- `system/controllers/fiber.php`

**Templates:**
- `ui/ui/admin/fiber/olt-devices/list.tpl`
- `ui/ui/admin/fiber/olt-devices/add.tpl`
- `ui/ui/admin/fiber/olt-devices/edit.tpl`
- `ui/ui/admin/fiber/onus/list.tpl`
- `ui/ui/admin/fiber/onus/add.tpl`
- `ui/ui/admin/fiber/onus/edit.tpl`
- `ui/ui/admin/fiber/profiles/list.tpl`
- `ui/ui/admin/fiber/profiles/add.tpl`
- `ui/ui/admin/fiber/monitoring/dashboard.tpl`

**Helper Classes:**
- `system/autoload/OLT.php` - OLT helper class
- `system/autoload/ONU.php` - ONU helper class

**Language Files:**
- Add translations to `system/lan/*.json` files

### Files to Modify:

**Database:**
- `install/phpnuxbill.sql` - Add OLT tables (or separate migration)
- `system/updates.json` - Add migration entries

**Billing Integration:**
- `system/autoload/Package.php` - Add OLT device detection
- `system/cron.php` - Add OLT ONU suspension on expiration

**Menu:**
- `ui/ui/admin/header.tpl` - Add Fiber Management menu

**Customer Management:**
- `system/controllers/customers.php` - Add ONU assignment when creating customer
- `ui/ui/admin/customers/add.tpl` - Add ONU serial number field
- `ui/ui/admin/customers/edit.tpl` - Add ONU management section

**Plan Management:**
- `system/controllers/services.php` - Support OLT plan type
- `ui/ui/admin/services/*.tpl` - Add OLT plan options

---

## 4. Implementation Steps

### Phase 1: Foundation (Database & Interface)
1. Create database tables
2. Create OLTInterface
3. Create BaseOLT abstract class
4. Add database migrations

### Phase 2: Core Drivers
1. Implement GenericSNMPOLT (most universal)
2. Implement HuaweiOLT
3. Implement ZTEOLT
4. Implement BDCOMOLT
5. Implement VSOLOLT

### Phase 3: Admin UI
1. Create fiber controller
2. Create OLT device management UI
3. Create ONU management UI
4. Create profile management UI
5. Create monitoring dashboard

### Phase 4: Billing Integration
1. Add OLT plan type support
2. Integrate ONU activation on plan purchase
3. Integrate ONU suspension on expiration
4. Add auto-provisioning

### Phase 5: Advanced Features
1. Add cron-based ONU sync
2. Add bulk operations
3. Add ONU search/filtering
4. Add reporting

### Phase 6: Testing & Documentation
1. Test all drivers
2. Test billing integration
3. Test cron jobs
4. Write documentation

---

## 5. Key Design Decisions

1. **Reuse Router Infrastructure:** OLTs follow similar pattern to routers but with ONU-specific methods
2. **Hybrid Architecture:** OLTs and routers can coexist - customer can have both
3. **Profile Mapping:** OLT profiles map to billing plans, enabling flexible service tiers
4. **Caching Strategy:** Cache ONU status to avoid OLT overload, sync periodically
5. **Security First:** Encrypt credentials, log all actions, restrict access
6. **Extensibility:** Interface-based design allows easy addition of new OLT brands
7. **Backward Compatibility:** Don't break existing router functionality

---

## 6. Future Enhancements (XGS-PON Ready)

- Support for XGS-PON OLTs (same interface, different drivers)
- ONU firmware management
- Remote ONU diagnostics
- Bandwidth monitoring per ONU
- Traffic graphs per ONU
- ONU alarm management
- Multi-tenant OLT support

---

This architecture maintains compatibility with existing phpNuxBill while adding comprehensive OLT support.
