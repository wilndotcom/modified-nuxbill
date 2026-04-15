# Customer Management Enhancement Plan - Router Assignment & Service Type Organization

## Current System Analysis

### Current Issues:
1. **No Router Assignment**: Customers not assigned to specific routers during creation
2. **No Service Type Filtering**: Customers see all plans regardless of service type
3. **Poor Organization**: Member portal shows all plans, not service-specific
4. **Mixed Service Types**: PPPoE customers can see Hotspot plans, etc.

## Proposed Enhancements:

### 1. Router Assignment in Customer Creation

**Add Router Selection to Customer Form:**
- Add router dropdown to customer creation form
- Assign customer to specific router during creation
- Store router assignment in customer record
- Filter plans by assigned router

**Database Schema Updates:**
```sql
-- Add router_id to tbl_customers
ALTER TABLE tbl_customers ADD COLUMN router_id INT(11) DEFAULT NULL;

-- Add foreign key relationship
ALTER TABLE tbl_customers ADD CONSTRAINT fk_customer_router 
FOREIGN KEY (router_id) REFERENCES tbl_routers(id);
```

**Form Enhancement:**
```html
<!-- Add to add.tpl -->
<div class="form-group">
    <label class="col-md-3 control-label">Router</label>
    <div class="col-md-9">
        <select class="form-control" name="router_id" id="router_id">
            <option value="">Select Router</option>
            {foreach $routers as $router}
                <option value="{$router['id']}" {if $d['router_id'] == $router['id']}selected="selected"{/if}>
                    {$router['name']} ({$router['ip_address']})
                </option>
            {/foreach}
        </select>
    </div>
</div>
```

### 2. Service Type Filtering in Member Portal

**Filter Plans by Customer Service Type:**
- Only show plans compatible with customer's service type
- Hide incompatible service types
- Better organization and user experience

**Implementation:**
```php
// In orderPlan.tpl - filter plans by service type
$plans = ORM::for_table('tbl_plans')
    ->where('enabled', 1)
    ->where('type', $_user['service_type'])  // Filter by customer service type
    ->find_many();
```

### 3. Enhanced Service Type Validation

**Service Type Restrictions:**
- PPPoE customers should only see PPPoE plans
- Hotspot customers should only see Hotspot plans
- VPN customers should only see VPN plans
- Prevent cross-service type plan selection

**Implementation:**
```php
// Enhanced validation in orderPlan controller
$validServiceTypes = [
    'PPPoE' => ['PPPoE'],
    'Hotspot' => ['Hotspot'],
    'VPN' => ['VPN']
];

if (!isset($validServiceTypes[$_user['service_type']])) {
    r2(getUrl('home'), 'e', 'Invalid service type for plan ordering');
}

// Filter plans by service type AND router compatibility
$plans = ORM::for_table('tbl_plans')
    ->where('enabled', 1)
    ->where('type', $_user['service_type'])
    ->where_raw("routers LIKE '%{$customer['router_id']}%' OR routers = ''")  // Plans compatible with assigned router
    ->find_many();
```

### 4. Member Portal Organization

**Service Type Sections:**
- Separate plan sections by service type
- Clear labeling and organization
- Better user experience

**Template Structure:**
```html
<!-- Enhanced orderPlan.tpl -->
{if $_user['service_type'] == 'PPPoE'}
    <div class="box-header">
        <h3>PPPoE Internet Plans</h3>
        <p class="text-muted">Available plans for your PPPoE service</p>
    </div>
{elseif $_user['service_type'] == 'Hotspot'}
    <div class="box-header">
        <h3>Hotspot Internet Plans</h3>
        <p class="text-muted">Available plans for your Hotspot service</p>
    </div>
{elseif $_user['service_type'] == 'VPN'}
    <div class="box-header">
        <h3>VPN Internet Plans</h3>
        <p class="text-muted">Available plans for your VPN service</p>
    </div>
{else}
    <div class="box-header">
        <h3>Internet Plans</h3>
        <p class="text-muted">Available internet plans</p>
    </div>
{/if}
```

### 5. Router Management Integration

**Router-Plan Compatibility:**
- Link plans to specific routers
- Router-based plan filtering
- Automatic router assignment based on plan

**Database Enhancement:**
```sql
-- Add router compatibility to plans
ALTER TABLE tbl_plans ADD COLUMN routers VARCHAR(255) DEFAULT NULL;

-- Update existing plans with router information
UPDATE tbl_plans SET routers = 'Router1,Router2' WHERE type = 'PPPoE';
UPDATE tbl_plans SET routers = 'Router3' WHERE type = 'Hotspot';
```

### 6. Enhanced Customer Management

**Router-Based Customer Lists:**
- Filter customers by router
- Router-specific customer management
- Better organization for multi-router deployments

**Implementation:**
```php
// In customers controller
$router_id = _req('router');
if ($router_id) {
    $customers = ORM::for_table('tbl_customers')
        ->select('tbl_customers.*', 'tbl_routers.name as router_name')
        ->left_outer_join('tbl_routers', 'tbl_customers.router_id', '=', 'tbl_routers.id')
        ->where('tbl_customers.router_id', $router_id)
        ->find_many();
}
```

## Implementation Steps:

### Phase 1: Database Updates
1. Add router_id to tbl_customers
2. Add foreign key constraint
3. Add routers field to tbl_plans
4. Update existing plans with router compatibility

### Phase 2: Backend Updates
1. Update customer creation controller
2. Update plan filtering logic
3. Add service type validation
4. Update member portal templates

### Phase 3: Frontend Updates
1. Add router selection to customer forms
2. Update member portal templates
3. Add service type sections
4. Update customer list filtering

### Phase 4: Testing & Validation
1. Test router assignment functionality
2. Test service type filtering
3. Test member portal organization
4. Validate cross-service type restrictions

## Benefits:

### For Administrators:
- **Better Organization**: Customers organized by router and service type
- **Improved Control**: Router-based customer management
- **Enhanced Security**: Service type restrictions enforced
- **Simplified Management**: Clear separation of service types

### For Customers:
- **Relevant Plans**: Only see plans compatible with their service type
- **Better UX**: Clear service type organization
- **Faster Selection**: Filtered plan options
- **Service Clarity**: Understanding of their service type limitations

### For System:
- **Scalability**: Better multi-router support
- **Data Integrity**: Foreign key relationships
- **Performance**: Optimized queries with proper indexing
- **Maintainability**: Clear service type separation

This enhancement will significantly improve the organization and usability of the PHPNuxBill system for both administrators and customers.
