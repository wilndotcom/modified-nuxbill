# Customer Portal Debug Guide

## What Should Be Visible

### If Balance is Enabled (`enable_balance = 'yes'`):

#### 1. **Top Navigation Bar** (Top-Right)
- Should show balance amount
- If negative: Red text + "(Debt)" label
- If positive: Normal white text

#### 2. **Sidebar Balance Widget** (Left Sidebar)
- Should appear below "Dashboard" menu item
- Shows "Wallet Balance" label
- Displays balance with color:
  - **Red** if negative (with "⚠️ Debt" badge)
  - **Green** if positive (with "✓ Active" badge)

#### 3. **Alert Banner** (Top of Content Area)
- Only shows if balance is **negative**
- Red alert box with warning icon
- "Account Has Debt" message
- "Pay Now" button

#### 4. **User Dropdown** (Click Profile Picture)
- Shows balance in dropdown menu
- Red if negative, green if positive

#### 5. **Dashboard Widget**
- "Your Account Information" widget
- Shows balance with debt indicator

---

## Troubleshooting

### If You Don't See Balance Display:

1. **Check Balance is Enabled**:
   - Go to Admin → Settings → Application
   - Look for "Enable Balance" setting
   - Must be set to "Yes"

2. **Check Your Balance**:
   - Go to Dashboard
   - Look for "Your Account Information" widget
   - Check if balance is displayed there

3. **Check Browser Console**:
   - Press F12 → Console tab
   - Look for JavaScript errors
   - Look for template errors

4. **Clear Browser Cache**:
   - Hard refresh: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)
   - Or clear cache in browser settings

5. **Check if Logged In**:
   - Make sure you're logged into customer portal
   - Not admin panel

---

## Testing Debt Display

To test debt indicators, you need a customer with **negative balance**:

1. **Admin Panel** → Customers → Edit Customer
2. **Balance Section**:
   - Select "Subtract" from dropdown
   - Enter amount (e.g., 50)
   - Click "Save"
3. **Customer Portal**:
   - Log in as that customer
   - You should see:
     - Red balance in top navigation
     - Red alert banner on all pages
     - "Debt" badges everywhere

---

## What Each Location Shows

| Location | Positive Balance | Negative Balance (Debt) |
|----------|-----------------|------------------------|
| Top Nav | White text: `$50.00` | Red text: `-$25.00 (Debt)` |
| Sidebar | Green: `$50.00` + "✓ Active" | Red: `-$25.00` + "⚠️ Debt" |
| Alert Banner | Not shown | Red alert with "Pay Now" button |
| User Dropdown | Green balance | Red balance + "Debt" badge |
| Dashboard Widget | Green balance | Red balance + "Debt" badge |
| Buy Package Page | Normal | Yellow warning banner |
| Buy Balance Page | Normal | Yellow notice |

---

## Common Issues

### Issue: Balance Not Showing at All
**Possible Causes**:
- Balance not enabled in settings
- `$_user` variable not available
- Template cache issue

**Solution**:
- Check Settings → Application → Enable Balance = Yes
- Clear template cache: Delete `ui/ui/compiled/` folder contents
- Hard refresh browser

### Issue: Balance Shows But No Debt Indicators
**Possible Causes**:
- Balance is positive (debt indicators only show when negative)
- Template not updated

**Solution**:
- Check actual balance value (should be negative for debt)
- Verify template files are updated
- Clear cache

### Issue: Sidebar Balance Not Showing
**Possible Causes**:
- Balance not enabled
- `$_user` not set
- CSS hiding element

**Solution**:
- Check `enable_balance` setting
- Verify `$_user['balance']` exists
- Check browser inspector for CSS issues

---

## Quick Check List

- [ ] Balance enabled in settings?
- [ ] Logged in as customer (not admin)?
- [ ] Balance value exists in database?
- [ ] Template files updated?
- [ ] Browser cache cleared?
- [ ] No JavaScript errors in console?
- [ ] Balance is negative (to see debt indicators)?

---

## Files to Check

If balance still not showing, verify these files exist and are updated:

1. `system/boot.php` - Should assign `$_user` globally
2. `ui/ui/customer/header.tpl` - Should display balance
3. `ui/ui/widget/customers/account_info.tpl` - Dashboard widget
4. `ui/ui/customer/orderPlan.tpl` - Package page
5. `ui/ui/customer/orderBalance.tpl` - Balance page

---

## Still Not Working?

1. **Check Database**:
   ```sql
   SELECT id, username, balance FROM tbl_customers WHERE username = 'your_username';
   ```
   Verify balance column has a value.

2. **Check Settings**:
   ```sql
   SELECT * FROM tbl_appconfig WHERE setting = 'enable_balance';
   ```
   Value should be 'yes'.

3. **Check Template Compilation**:
   - Delete all files in `ui/ui/compiled/`
   - Reload page (templates will recompile)

4. **Enable Debug Mode** (if available):
   - Check for template errors
   - Verify variables are being passed

---

## Expected Behavior

### When Balance is Positive:
- ✅ Balance shows in green
- ✅ No alert banners
- ✅ "Active" badge in sidebar
- ✅ Normal display everywhere

### When Balance is Negative (Debt):
- ✅ Balance shows in red
- ✅ "(Debt)" labels everywhere
- ✅ Red alert banner on all pages
- ✅ "Debt" badges
- ✅ Warning messages on purchase pages

---

**If you still don't see balance display, please specify:**
1. What page you're on?
2. Do you see balance anywhere?
3. What's your actual balance value?
4. Is balance enabled in settings?
