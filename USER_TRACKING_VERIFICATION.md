# User Tracking Verification

## How Users Are Tracked by Type

### ✅ Hotspot Users
**Source:** `/ip/hotspot/active/print` from RouterOS

**Logic:**
- Queries all routers for active hotspot sessions
- Counts unique usernames from `/ip/hotspot/active`
- Each active session = 1 hotspot user
- **Excludes:** Users that are also identified as static

**Example:**
```
Router: /ip/hotspot/active/print
Returns: user1, user2, user3
Count: 3 Hotspot users
```

### ✅ PPPoE Users
**Source:** `/ppp/active/print` from RouterOS

**Logic:**
- Queries all routers for active PPP sessions
- Filters by `service = 'pppoe'`
- Counts unique usernames where service is PPPoE
- **Excludes:** Users identified as static (even if they use PPPoE connection)

**Example:**
```
Router: /ppp/active/print
Filters: service == 'pppoe'
Returns: pppoe_user1, pppoe_user2
Count: 2 PPPoE users
```

### ✅ Static Users
**Source:** Multiple detection methods

**Detection Methods:**

1. **From PPPoE Active Sessions:**
   - If PPPoE user has `service_type = 'Others'` AND has `ip_address` set
   - If PPPoE user has profile name containing "static"
   - If PPPoE user has static IP address assigned in database

2. **From IP Bindings:**
   - Checks `/ip/hotspot/ip-binding/print` for active bindings
   - Matches IP addresses to customers with static IPs
   - If customer has `ip_address` field set and matches binding

3. **Database Check:**
   - Customer has `ip_address` field populated (not empty, not 0.0.0.0)
   - Customer `service_type = 'Others'` with IP address
   - Profile name contains "static" keyword

**Example:**
```
PPPoE Active: static_user1 (profile: "static-ip")
Database: static_user1 has ip_address = "192.168.1.100"
Result: Counted as Static (not PPPoE)
```

### ✅ Total Online Users
**Calculation:** `Hotspot + PPPoE + Static`

**Double Counting Prevention:**
- Static users are removed from Hotspot count
- Static users are removed from PPPoE count
- Total = unique count of all online users

## Verification Scripts

### Quick Test
Run: `http://your-site-url/test_user_tracking.php`
- Shows current counts
- Verifies no double counting
- Quick overview

### Detailed Verification
Run: `http://your-site-url/verify_user_tracking.php`
- Tests each router connection
- Shows detailed user lists by type
- Compares helper function vs direct count
- Shows exactly which users are in each category

## How to Verify Tracking is Correct

1. **Run Verification Script:**
   ```
   http://your-site-url/verify_user_tracking.php
   ```

2. **Check RouterOS Manually:**
   - Login to MikroTik router
   - Run: `/ip/hotspot/active/print` - Count users
   - Run: `/ppp/active/print` - Count PPPoE users
   - Compare with widget counts

3. **Check Database:**
   ```sql
   -- Check customers with static IPs
   SELECT username, ip_address, service_type 
   FROM tbl_customers 
   WHERE ip_address != '' AND ip_address != '0.0.0.0';
   ```

4. **Verify Widget Display:**
   - Go to Dashboard
   - Check widget counts match verification script
   - Click "Refresh" button to update

## Expected Behavior

### Hotspot Users
- ✅ Counts all users in `/ip/hotspot/active`
- ✅ Excludes static users (if any hotspot users are also static)
- ✅ Shows real-time active sessions

### PPPoE Users  
- ✅ Counts PPPoE service sessions from `/ppp/active`
- ✅ Excludes static users (even if they connect via PPPoE)
- ✅ Shows real-time active sessions

### Static Users
- ✅ Detects users with static IP addresses
- ✅ Detects users with "static" in profile name
- ✅ Detects users from IP bindings
- ✅ Not double-counted in Hotspot or PPPoE

### Total
- ✅ Sum of Hotspot + PPPoE + Static
- ✅ No double counting
- ✅ Accurate total of all online users

## Troubleshooting

### If counts don't match:
1. Clear cache: Delete `system/cache/online_users_stats.cache`
2. Check router connectivity
3. Verify router credentials
4. Run verification script to see detailed breakdown

### If static users not detected:
1. Ensure customers have `ip_address` field populated
2. Check if profile names contain "static"
3. Verify IP bindings are active on router
4. Check `service_type` field in database

### If double counting occurs:
- The code removes static users from hotspot/pppoe lists
- Verify the `isStaticUser()` function is working correctly
- Check that static detection logic matches your setup
