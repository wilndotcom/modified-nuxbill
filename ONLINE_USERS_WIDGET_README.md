# Online Users Dashboard Widgets

## Overview

This implementation adds 4 real-time dashboard widgets that track currently online users from MikroTik RouterOS:

1. **Online Hotspot Users** - Counts active hotspot sessions from `/ip/hotspot/active`
2. **Online PPPoE Users** - Counts active PPPoE sessions from `/ppp/active` where service = pppoe
3. **Online Static Users** - Detects static IP users from active sessions
4. **Total Online Users** - Sum of all three categories above

## Features

- ✅ **Real-time Data**: Fetches live data directly from MikroTik RouterOS API
- ✅ **Performance Optimized**: 30-second caching to avoid overloading routers
- ✅ **Auto-refresh**: Widgets automatically update every 30 seconds via AJAX
- ✅ **Multi-router Support**: Aggregates data from all enabled routers
- ✅ **Error Handling**: Gracefully handles router connection failures
- ✅ **Admin Only**: Security check ensures only admins can view
- ✅ **No Core Changes**: All code is in separate files, no existing functionality modified

## Files Created

1. `system/autoload/OnlineUsersHelper.php` - Helper class for RouterOS queries with caching
2. `system/widgets/online_users.php` - Widget class
3. `ui/ui/widget/online_users.tpl` - Widget template with 4 cards
4. Modified `system/controllers/dashboard.php` - Added AJAX endpoint

## Installation

### Step 1: Files are Already Created
All necessary files have been created in the correct locations.

### Step 2: Add Widget to Database

You need to add the widget to your database. Run this SQL query:

```sql
INSERT INTO `tbl_widgets` (`widget`, `name`, `user`, `enabled`, `position`, `orders`) 
VALUES ('online_users', 'Online Users', 'Admin', 1, 1, 999);
```

Or add it via the admin panel:
1. Go to **Settings** → **Widgets**
2. Click **Add Widget**
3. Fill in:
   - **Widget**: `online_users`
   - **Name**: `Online Users`
   - **User**: `Admin`
   - **Enabled**: Yes
   - **Position**: 1 (or your desired position)
   - **Orders**: 999 (or your desired order)

### Step 3: Add Widget to Dashboard Layout

1. Go to **Settings** → **Widgets**
2. Scroll to **Dashboard Structure**
3. Add the widget to your layout (e.g., `3,3,3,3` for 4 columns)

### Step 4: Verify Router Configuration

Ensure your routers are properly configured in:
- **Settings** → **Routers**
- Make sure routers are **Enabled**
- Verify IP address, username, and password are correct

## How It Works

### Data Source
- **Hotspot Users**: Queries `/ip/hotspot/active/print` from all routers
- **PPPoE Users**: Queries `/ppp/active/print` and filters by `service = pppoe`
- **Static Users**: Determined by:
  - Checking customer `service_type = 'Static'` in database
  - Matching active PPP sessions with static profiles
  - Checking IP bindings for static IP addresses

### Caching
- Data is cached for 30 seconds to prevent router overload
- Cache file: `system/cache/online_users_stats.cache`
- Cache is automatically cleared when expired

### Auto-Refresh
- Widgets refresh every 30 seconds via AJAX
- No page reload required
- Smooth number animations when values change

## AJAX Endpoint

The widget uses this endpoint for real-time updates:
```
?_route=dashboard/online-stats
```

**Response Format:**
```json
{
    "success": true,
    "stats": {
        "hotspot": 12,
        "pppoe": 8,
        "static": 5,
        "total": 25,
        "error": null
    },
    "timestamp": 1234567890
}
```

## Troubleshooting

### Widget Not Showing
1. Check if widget is enabled in database
2. Verify widget is added to dashboard layout
3. Check browser console for JavaScript errors

### Showing Zero or No Data
1. Verify routers are enabled and configured correctly
2. Check router connectivity (ping router IP)
3. Verify RouterOS API credentials are correct
4. Check if router has hotspot/PPPoE configured
5. Look for error messages in widget (yellow alert box)

### Router Connection Errors
- Check router IP address and port
- Verify username and password
- Ensure RouterOS API is enabled on router
- Check firewall rules allowing API access

### Cache Issues
To manually clear cache, you can:
1. Delete `system/cache/online_users_stats.cache`
2. Or add `&refresh=1` to the AJAX URL

## Security

- ✅ Admin authentication required
- ✅ Session validation before API calls
- ✅ No SQL injection risks (using ORM)
- ✅ Router credentials stored securely in database

## Performance Considerations

- **Cache Duration**: 30 seconds (configurable in `OnlineUsersHelper::CACHE_DURATION`)
- **Query Optimization**: Single query per router type (not 4 separate queries)
- **Error Handling**: Failed routers don't crash the widget
- **AJAX Refresh**: 30-second interval (configurable in template JavaScript)

## Customization

### Change Cache Duration
Edit `system/autoload/OnlineUsersHelper.php`:
```php
const CACHE_DURATION = 60; // Change to 60 seconds
```

### Change Refresh Interval
Edit `ui/ui/widget/online_users.tpl`:
```javascript
refreshInterval = setInterval(updateOnlineUsers, 60000); // Change to 60 seconds
```

### Modify Widget Colors
Edit `ui/ui/widget/online_users.tpl` - change gradient colors in the `bg-aqua`, `bg-green`, `bg-yellow`, `bg-red` classes.

## Future Enhancements

The code is structured to easily add:
- Online users by router
- Online users by plan
- Online users by location
- Historical charts
- Export functionality

## Support

If you encounter issues:
1. Check PHP error logs
2. Check browser console for JavaScript errors
3. Verify RouterOS API connectivity
4. Ensure all files are in correct locations

## Notes

- Widget only counts **currently online** users, not all registered/paid users
- Static users are detected by service type, profile name, or IP binding
- Multiple routers are aggregated (no double counting)
- Widget works with existing phpNuxBill installation without modifications
