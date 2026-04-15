# Customer Router & Service Type Requirements

## Business Rules

### When Adding Customer:
1. **Router Selection is REQUIRED** - Customer must be assigned to a specific router
2. **Service Type is REQUIRED** - Customer must have a service type (Hotspot/PPPoE/VPN/Others)
3. These selections determine what plans the customer can see in the portal

### Customer Portal Plan Filtering:
When a customer logs into the portal (`order/package`), they should ONLY see:
- Plans that match their assigned **router**
- Plans that match their assigned **service type**
- Plans that match their **account type** (Personal/Business)
- Only **enabled** plans
- Only **prepaid** plans

**Example:**
- Customer assigned to Router A + Service Type Hotspot
- Portal shows: Only Hotspot plans configured for Router A
- Portal does NOT show: PPPoE plans, plans from Router B, plans from Router C

## Implementation Requirements

### 1. Database Storage
- Store customer's assigned router ID in custom field: `"Router"` 
- OR add new column `assigned_router_id` to `tbl_customers` table
- `service_type` already exists in `tbl_customers` table ✅

### 2. Customer Add Form
- Router selection MUST be required
- Service Type selection MUST be required
- Show router selection prominently (Step 2 in wizard)
- Filter routers by service type if needed
- Validate that router selection is not empty

### 3. Customer Login
- When customer logs in, set `$_SESSION['nux-router']` based on their assigned router
- This ensures portal filtering works correctly

### 4. Customer Portal Plan Filtering (`order.php`)
- Update `order/package` case to:
  - Get customer's assigned router from database
  - Get customer's service_type from database
  - Filter plans by BOTH router AND service_type
  - Only show matching plans

### 5. Backward Compatibility
- For existing customers without assigned router:
  - Show all plans (current behavior)
  - OR prompt admin to assign router
  - OR use default router from settings

## Code Changes Needed

### 1. Customer Add/Edit (`customers.php`)
- Add router selection field (required)
- Store router assignment in custom field or new column
- Validate router is selected

### 2. Customer Login (`login.php` or `init.php`)
- After successful login, check customer's assigned router
- Set `$_SESSION['nux-router'] = $customer['assigned_router_id']`
- This ensures portal shows correct plans

### 3. Customer Portal (`order.php`)
- Update plan filtering logic:
  ```php
  // Get customer's assigned router and service type
  $assigned_router_id = User::getAttribute("Router", $user['id']);
  $service_type = $user['service_type'];
  
  // Filter plans by router AND service type
  $plans_hotspot = ORM::for_table('tbl_plans')
      ->where('plan_type', $account_type)
      ->where('enabled', '1')
      ->where('type', 'Hotspot')  // Filter by service type
      ->where('routers', $router_name)  // Filter by router
      ->where('prepaid', 'yes')
      ->find_many();
  ```

### 4. Customer Add Template (`add.tpl`)
- Make router selection required
- Show router selection prominently
- Add validation JavaScript

## Testing Checklist

- [ ] Add customer with Router A + Hotspot → Portal shows only Router A Hotspot plans
- [ ] Add customer with Router B + PPPoE → Portal shows only Router B PPPoE plans  
- [ ] Customer cannot see plans from other routers
- [ ] Customer cannot see plans of different service type
- [ ] Router selection is required when adding customer
- [ ] Service type selection is required when adding customer
- [ ] Login sets correct session router
- [ ] Backward compatibility for existing customers
