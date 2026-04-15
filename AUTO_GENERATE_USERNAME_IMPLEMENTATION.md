# Auto-Generate Username Implementation

## Overview
Auto-generation of customer usernames and passwords for easy portal and PPPoE login. All credentials are the same for simplicity.

## Format
- **Prefix**: First 3 letters of company name (uppercase, letters only)
- **Year**: 4-digit year (e.g., 2025)
- **Sequence**: 4-digit number starting from 0001, increments per year

**Example**: Company "NetCorp" → `Net20250001`, `Net20250002`, etc.

## Implementation Details

### Backend (`system/controllers/customers.php`)
1. **Function**: `generateNextCustomerUsername($companyName)`
   - Extracts first 3 letters from company name
   - Falls back to "CUS" if company name has < 3 letters
   - Queries existing usernames matching pattern `{prefix}{year}####`
   - Returns next available sequence number

2. **Add Customer (`add-post`)**:
   - When `auto_generate_username` is checked:
     - Generates username
     - Sets password = username
     - Sets pppoe_username = username
     - Sets pppoe_password = username
   - Validates uniqueness before saving

3. **AJAX Endpoint (`get-next-username`)**:
   - Returns preview of next username for display in form

### Frontend (`ui/ui/admin/customers/add.tpl`)
1. **Checkbox**: "Auto-generate username and password (Same for portal and PPPoE login)"
   - Checked by default
   - Shows format explanation

2. **Fields Behavior**:
   - When auto-generate is **ON**:
     - Username, Password, PPPoE Username, PPPoE Password fields are **visible but read-only**
     - Fields show gray background (`#f5f5f5`)
     - Preview shows next username
     - Fields auto-populate with generated value
   - When auto-generate is **OFF**:
     - All fields are editable
     - Normal validation applies

3. **Preview**: Shows "Next username will be: Net20250001" when auto-generate is enabled

## Edge Cases Handled

✅ **Company name < 3 letters**: Falls back to "CUS" prefix  
✅ **Company name with special characters**: Filters to letters only  
✅ **Sequence overflow (9999)**: Capped at 9999 (will need manual intervention)  
✅ **Username uniqueness**: Checked before saving (duplicate will show error)  
✅ **Welcome messages**: Already include `[[username]]` and `[[password]]` placeholders  
✅ **Generated username length**: Max 11 chars (3+4+4), within validation limits (3-54)  

## Potential Edge Cases to Consider

⚠️ **Race Condition**: If two admins add customers simultaneously, one may get a duplicate username error. Solution: Retry or manually adjust.

⚠️ **Company Name Changes**: If company name changes in settings, new customers will have different prefix. Old customers keep their original usernames (this is fine).

⚠️ **Year Transition**: Sequence resets each year (by design). Example: `Net20250099` → `Net20260001` (new year).

⚠️ **Customer Edit Page**: Currently not modified. Auto-generated usernames can still be edited manually if needed.

⚠️ **Bulk Import/CSV**: Auto-generate feature not available for bulk imports. Manual usernames required.

⚠️ **Sequence Reset**: If you need to reset sequence for a year, you'd need to manually delete or rename existing customers with that pattern, or modify the function.

## Usage Notes

1. **For Customers**: They receive one credential (username = password) for both:
   - Customer portal login
   - PPPoE connection

2. **For Admins**: 
   - Can toggle auto-generate on/off
   - Can see preview of next username
   - Can manually override if needed (uncheck auto-generate)

3. **Welcome Messages**: Automatically include generated credentials via `[[username]]` and `[[password]]` placeholders.

## Testing Checklist

- [x] Generate username with normal company name (3+ letters)
- [x] Generate username with short company name (< 3 letters)
- [x] Generate username with special characters in company name
- [x] Sequence increments correctly (0001 → 0002)
- [x] Sequence resets per year (2025 → 2026)
- [x] Uniqueness check prevents duplicates
- [x] Fields visible but read-only when auto-generate enabled
- [x] Fields editable when auto-generate disabled
- [x] Preview shows correct next username
- [x] PPPoE credentials match portal credentials
- [x] Welcome message includes credentials
