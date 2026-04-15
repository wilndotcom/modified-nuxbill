# Customer Add Wizard Implementation Guide

## Overview
This document outlines the implementation of an advanced step-by-step wizard for adding customers, with clear separation of:
1. Customer Type & Basic Information
2. Router Selection (filtered by service type)
3. Plan Selection (filtered by router and service type)
4. Additional Details & Review

## Implementation Strategy

### Phase 1: Backend Updates ✅ COMPLETED
- Added router and plan loading in `customers.php` controller
- Created AJAX endpoints:
  - `get-routers-by-service` - Filter routers by service type
  - `get-plans-by-router` - Filter plans by router and service type

### Phase 2: Frontend Wizard Structure

#### Step 1: Customer Type & Basic Info
**Location:** First panel, prominently displayed
**Fields:**
- Account Type (Personal/Business) - Radio buttons or cards for better UX
- Service Type (Hotspot/PPPoE/VPN/Others) - Visual selection
- Username, Full Name, Email, Phone, Password, Address

**Visual Design:**
- Use card-based selection for Account Type and Service Type
- Clear visual indicators
- Required field markers

#### Step 2: Router Selection
**Location:** Second panel, shown after Step 1
**Features:**
- Dynamic loading based on service type
- Router cards showing:
  - Router name
  - IP address
  - Description
  - Status indicator
- Option to skip (for Radius/Balance plans)
- "None" option for customers without router assignment

**AJAX Integration:**
- Load routers when service type changes
- Show loading indicator during fetch
- Display "No routers available" if empty

#### Step 3: Plan Selection
**Location:** Third panel, shown after router selection
**Features:**
- Filtered by:
  - Selected router
  - Selected service type
  - Enabled plans only
- Plan cards showing:
  - Plan name
  - Price
  - Bandwidth
  - Validity period
  - Plan type (Prepaid/Postpaid)
- Option to skip (create customer without plan)
- Search/filter functionality

**AJAX Integration:**
- Load plans when router or service type changes
- Real-time filtering
- Plan details modal on click

#### Step 4: Additional Details & Review
**Location:** Fourth panel, collapsible
**Features:**
- PPPoE credentials (if service type is PPPoE)
- Custom attributes
- Additional information (city, district, state, zip)
- Coordinates/map
- Welcome message options
- Review summary showing all selections

## Key Features

### 1. Progress Indicator
```html
<div class="wizard-progress">
  <div class="step active">1. Customer Info</div>
  <div class="step">2. Router</div>
  <div class="step">3. Plan</div>
  <div class="step">4. Details</div>
</div>
```

### 2. Dynamic Filtering
- Service Type → Filters Router options
- Router Selection → Filters Plan options
- Real-time updates without page refresh

### 3. Validation
- Step-by-step validation
- Cannot proceed to next step without required fields
- Clear error messages

### 4. Navigation
- Previous/Next buttons
- Save button (creates customer)
- Cancel button
- Step indicators are clickable (if previous steps completed)

## JavaScript Structure

```javascript
// Wizard state management
const wizardState = {
  currentStep: 1,
  totalSteps: 4,
  data: {
    accountType: '',
    serviceType: '',
    router: '',
    plan: '',
    // ... other fields
  }
};

// Step navigation
function goToStep(step) {
  // Validate current step
  // Update UI
  // Load data for next step if needed
}

// Dynamic filtering
function loadRouters(serviceType) {
  // AJAX call to get-routers-by-service
  // Update router selection UI
}

function loadPlans(router, serviceType) {
  // AJAX call to get-plans-by-router
  // Update plan selection UI
}
```

## CSS Styling

### Wizard Progress Bar
- Horizontal progress indicator
- Active step highlighted
- Completed steps marked with checkmark
- Responsive design

### Step Panels
- Each step in its own panel
- Smooth transitions between steps
- Clear visual separation

### Selection Cards
- Card-based selection for Account Type, Service Type, Routers, Plans
- Hover effects
- Selected state clearly visible
- Responsive grid layout

## Backend Integration

### Updated add-post Handler
```php
// Handle router assignment
$router_name = _post('router_name');
if (!empty($router_name)) {
    // Store router assignment
}

// Handle plan assignment (optional)
$plan_id = _post('plan_id');
if (!empty($plan_id)) {
    // Optionally assign plan during creation
    // Or create customer first, then assign plan
}
```

## Benefits

1. **Better Organization**: Clear separation of concerns
2. **Reduced Errors**: Filtered options prevent invalid selections  
3. **Improved UX**: Step-by-step process is more intuitive
4. **Time Saving**: Less scrolling, faster data entry
5. **Professional Look**: Modern wizard interface
6. **Flexibility**: Can create customer with or without router/plan

## Migration Path

1. Keep existing template as backup
2. Implement new wizard template
3. Test thoroughly
4. Option to toggle between old/new via config (optional)
5. Gradually migrate users

## Next Steps

1. Create the wizard template HTML structure
2. Implement JavaScript for step navigation
3. Add AJAX calls for dynamic filtering
4. Style with CSS
5. Test all scenarios
6. Update backend to handle new fields
7. Documentation and user guide
