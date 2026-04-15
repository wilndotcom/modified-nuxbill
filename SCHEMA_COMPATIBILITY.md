# Schema Compatibility - New Features

## Overview
All new features implemented work with the **existing database schema** - **no SQL migrations required**. They use existing tables and columns.

---

## Features Added (No Schema Changes Required)

### 1. Auto-Generate Username & Password
- **Storage**: Uses existing `tbl_customers.username` and `tbl_customers.password` columns
- **Logic**: PHP function `generateNextCustomerUsername()` - no database changes
- **Format**: `{3 letters of company}{year}{4-digit sequence}` (e.g., `Net20250001`)
- **Status**: ✅ Works with existing schema

### 2. Balance Adjustment & Allow Debt
- **Balance Column**: Already exists in `tbl_customers`:
  ```sql
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'For Money Deposit'
  ```
- **Allow Debt Flag**: Stored in existing `tbl_customers_fields` table:
  - `field_name` = `'Allow Debt'`
  - `field_value` = `'1'` (enabled) or `'0'` (disabled)
- **Status**: ✅ Works with existing schema

### 3. Router Assignment
- **Storage**: Uses existing `tbl_customers_fields` table:
  - `field_name` = `'Router'`
  - `field_value` = router ID (integer) or `'radius'`
- **Status**: ✅ Works with existing schema

---

## Existing Tables Used

### `tbl_customers`
- ✅ `username` - Used for auto-generated usernames
- ✅ `password` - Used for auto-generated passwords (same as username)
- ✅ `pppoe_username` - Set same as username when auto-generate enabled
- ✅ `pppoe_password` - Set same as password when auto-generate enabled
- ✅ `balance` - Used for balance adjustment and debt tracking
- ✅ `service_type` - Already exists, used for filtering plans

### `tbl_customers_fields`
- ✅ Used for storing:
  - `'Router'` - Assigned router ID
  - `'Allow Debt'` - Whether customer can run with negative balance

---

## Installation & Migration

### Fresh Installation
- ✅ **No changes needed** - Use `install/phpnuxbill.sql` as-is
- ✅ All features work immediately after installation

### Existing Installation
- ✅ **No migration needed** - Features work automatically
- ✅ Custom fields (`Router`, `Allow Debt`) are created on-demand when admin sets them
- ✅ Existing customers continue to work normally

---

## Database Structure (Already Exists)

```sql
-- Customers table (already exists)
CREATE TABLE `tbl_customers` (
  `id` int NOT NULL,
  `username` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `service_type` enum('Hotspot','PPPoE','Others') DEFAULT 'Others',
  -- ... other columns
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Custom fields table (already exists)
CREATE TABLE `tbl_customers_fields` (
  `id` int NOT NULL,
  `customer_id` int NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## How It Works

### Auto-Generate Username
1. Function queries existing usernames matching pattern `{prefix}{year}####`
2. Finds highest sequence number
3. Generates next username (e.g., `Net20250001`, `Net20250002`)
4. Saves to `tbl_customers.username` (existing column)

### Balance & Allow Debt
1. Admin edits customer → Balance section
2. Adjusts balance (Set/Add/Subtract) → Updates `tbl_customers.balance`
3. Checks "Allow debt" → Creates/updates `tbl_customers_fields` record:
   - `field_name` = `'Allow Debt'`
   - `field_value` = `'1'` or `'0'`
4. When customer recharges/buys:
   - If `Allow Debt` = `'1'`, bypasses balance check
   - Deducts from balance (can go negative)
   - When customer pays, balance increases (debt reduces automatically)

### Router Assignment
1. Admin assigns router → Creates/updates `tbl_customers_fields`:
   - `field_name` = `'Router'`
   - `field_value` = router ID or `'radius'`
2. Portal filters plans by assigned router

---

## Summary

✅ **All features work with existing schema**  
✅ **No SQL migrations required**  
✅ **No database structure changes needed**  
✅ **Compatible with fresh and existing installations**  
✅ **Uses existing `tbl_customers` and `tbl_customers_fields` tables**

The implementation is **backward compatible** and **schema-safe**. All new functionality uses existing database structures, so it works immediately without any database modifications.
