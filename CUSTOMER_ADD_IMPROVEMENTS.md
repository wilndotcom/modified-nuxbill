# Customer Add Page Improvements

## Overview
Enhanced the customer addition page (`?_route=customers/add`) with a modern step-by-step wizard that separates:
1. **Customer Type & Basic Information**
2. **Router Selection** (filtered by service type)
3. **Plan Selection** (filtered by router and service type)
4. **Additional Details & Review**

## Key Improvements

### 1. Step-by-Step Wizard Interface
- **Step 1: Customer Type & Basic Info**
  - Clear separation of Account Type (Personal/Business)
  - Service Type selection (Hotspot/PPPoE/VPN/Others)
  - Basic customer information fields
  - Visual indicators for required fields

- **Step 2: Router Selection**
  - Dynamic router list filtered by selected service type
  - Router information display (IP, description)
  - Option to skip router selection (for Radius/Balance plans)
  - Visual router cards with status indicators

- **Step 3: Plan Selection**
  - Plans filtered by:
    - Selected router
    - Selected service type
    - Plan status (enabled only)
  - Plan details display (price, bandwidth, validity)
  - Plan type indicators (Prepaid/Postpaid)
  - Option to skip plan assignment (create customer without plan)

- **Step 4: Additional Details & Review**
  - PPPoE credentials (if applicable)
  - Custom attributes
  - Additional information (city, district, state, zip)
  - Coordinates/map
  - Welcome message options
  - Review summary before submission

### 2. Dynamic Filtering
- **AJAX Endpoints:**
  - `/customers/get-routers-by-service` - Get routers filtered by service type
  - `/customers/get-plans-by-router` - Get plans filtered by router and service type
  - Real-time updates without page refresh

### 3. Enhanced UX Features
- Progress indicator showing current step
- Navigation buttons (Previous/Next/Save)
- Form validation at each step
- Auto-save draft functionality (optional)
- Clear visual separation of sections
- Responsive design for mobile devices

### 4. Backend Enhancements
- Router assignment during customer creation
- Plan assignment option during customer creation
- Validation for router-plan compatibility
- Better error handling and user feedback

## Implementation Files

### Frontend
- `ui/ui/admin/customers/add.tpl` - Enhanced wizard template
- JavaScript for step navigation and AJAX calls
- CSS for wizard styling

### Backend
- `system/controllers/customers.php` - Updated add/add-post handlers
- New AJAX endpoints for filtering
- Enhanced validation logic

## Benefits

1. **Better Organization**: Clear separation of concerns
2. **Reduced Errors**: Filtered options prevent invalid selections
3. **Improved UX**: Step-by-step process is more intuitive
4. **Time Saving**: Less scrolling, faster data entry
5. **Flexibility**: Can create customer with or without router/plan
6. **Professional Look**: Modern wizard interface

## Migration Notes

- Existing customer add functionality remains backward compatible
- Old form fields are preserved but organized in steps
- No database schema changes required
- Can be toggled via configuration if needed
