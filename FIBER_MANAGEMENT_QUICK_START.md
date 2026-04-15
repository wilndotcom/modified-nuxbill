# Fiber Management - Quick Start Guide

## 🚀 Quick Setup (5 Minutes)

### Step 1: Run Database Migrations

1. **OLT Tables:**
   ```
   http://localhost/phpnuxbill/create_olt_tables.php
   ```

2. **CPE Router Tables:**
   ```
   http://localhost/phpnuxbill/create_cpe_tables.php
   ```

### Step 2: Access Fiber Management

1. Login to admin panel
2. Click **"Fiber Management"** in sidebar
3. You're ready to go!

---

## 📋 Common Tasks

### Adding Your First Customer (5 Steps)

**1. Add OLT Device**
```
Fiber Management > OLT Devices > New OLT Device
- Name: "Main OLT"
- Brand: Huawei
- IP: 192.168.1.10
- Port: 23
- Username: admin
- Password: [your password]
- Save
```

**2. Create Profile**
```
Fiber Management > Profiles > New Profile
- Profile Name: "100Mbps-Down-50Mbps-Up"
- OLT: Main OLT
- Download: 100 Mbps
- Upload: 50 Mbps
- Save
```

**3. Create Billing Plan**
```
Plans > New Plan
- Plan Name: "Fiber 100Mbps"
- Type: OLT
- Price: [your price]
- Save
```

**4. Add ONU**
```
Fiber Management > ONUs > New ONU
- OLT: Main OLT
- Serial: HWTC12345678
- ONU ID: 0/1/2
- PON Port: 0/1
- Customer: [select customer]
- Plan: Fiber 100Mbps
- Save
```

**5. Activate ONU**
```
Click "Activate" button on the ONU
✅ Customer now has internet!
```

---

### Suspending a Customer (2 Steps)

**1. Suspend ONU**
```
Fiber Management > ONUs
- Find customer's ONU
- Click "Suspend" button
✅ Customer loses internet access
```

**2. (Optional) Reboot Router**
```
Fiber Management > CPE Routers
- Find customer's router
- Click "Reboot" button
```

---

### Adding Customer Router (3 Steps)

**1. Add Router**
```
Fiber Management > CPE Routers > New CPE Router
- Customer: [select]
- Router MAC: 00:11:22:33:44:55
- Router IP: 192.168.1.1
- Brand: TP-Link
- Model: Archer C6
- Protocol: HTTP
- Username: admin
- Password: [router password]
- Save
```

**2. Configure WiFi**
```
Click "Configure" button
- WiFi Settings
- SSID: CustomerName-WiFi
- Password: SecurePass123
- Band: Both
- Save
```

**3. Reboot Router** (if needed)
```
Click "Reboot" button
✅ Router restarts with new settings
```

---

## 🎯 Page-by-Page Guide

### OLT Devices Page

**What you see:**
- List of all OLT devices
- Status (Online/Offline)
- Connection test button
- Add/Edit/Delete buttons

**What to do:**
- ✅ Add all your OLT devices
- ✅ Test connections regularly
- ✅ Keep credentials updated

**Example:**
```
Name: Main Office OLT
Brand: Huawei
IP: 192.168.1.10
Status: Online ✓
```

---

### ONUs Page

**What you see:**
- List of all ONUs
- Customer linked
- Status (Active/Inactive/Suspended)
- Signal strength
- Actions: Activate, Suspend, Sync, Edit

**What to do:**
- ✅ Add ONUs when installing
- ✅ Link to customers immediately
- ✅ Use "Sync" to refresh status
- ✅ Use "Activate" to enable service

**Example:**
```
Serial: HWTC12345678
Customer: John Doe
Status: Active 🟢
Signal: -15 dBm
Plan: Fiber 100Mbps
```

---

### Profiles Page

**What you see:**
- List of bandwidth profiles
- Download/Upload speeds
- Linked plans
- OLT assignment

**What to do:**
- ✅ Create profiles before plans
- ✅ Match speeds to plan speeds
- ✅ Link profiles to plans

**Example:**
```
Profile: 100Mbps-Down-50Mbps-Up
Download: 100 Mbps
Upload: 50 Mbps
Plan: Fiber 100Mbps Plan
```

---

### CPE Routers Page

**What you see:**
- List of customer routers
- Router MAC, IP, Brand, Model
- Status (Online/Offline)
- Customer linked
- Actions: Status, Configure, Reboot, Reset, Edit

**What to do:**
- ✅ Add routers when installing
- ✅ Link to customers/ONUs
- ✅ Use "Configure" for WiFi/DHCP settings
- ✅ Use "Reboot" for troubleshooting

**Example:**
```
MAC: 00:11:22:33:44:55
IP: 192.168.1.1
Brand: TP-Link
Model: Archer C6
Status: Online 🟢
Customer: John Doe
```

---

### Monitoring Dashboard

**What you see:**
- Total OLTs count
- Online OLTs count
- Total ONUs count
- Active/Inactive/Suspended counts
- OLT status overview

**What to do:**
- ✅ Check daily for network health
- ✅ Monitor OLT connections
- ✅ Track ONU statistics

**Example:**
```
Total OLTs: 3
Online OLTs: 3 ✓
Total ONUs: 150
Active ONUs: 145 🟢
Suspended ONUs: 5 🟡
```

---

## ⚡ Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| Add New | Click "New" button |
| Edit | Click pencil icon |
| Delete | Click trash icon |
| Refresh | Click refresh icon |
| Search | Type in search box |

---

## 🔍 Status Indicators

| Icon | Meaning |
|------|---------|
| 🟢 Green | Active/Online/Working |
| 🔴 Red | Inactive/Offline/Error |
| 🟡 Yellow | Suspended/Warning |
| ⚪ Gray | Unknown/Not Set |

---

## ❓ FAQ

**Q: Where do I add a new customer?**
A: Customers > New Customer (not in Fiber Management)

**Q: How do I link ONU to customer?**
A: Edit ONU > Select Customer > Save

**Q: Can I change customer's plan?**
A: Yes! Edit ONU > Select new plan > Update (automatically changes profile)

**Q: Why can't I connect to OLT?**
A: Check IP, port, username, password, and network connectivity

**Q: Router actions not working?**
A: Brand-specific drivers may need implementation. See driver guide.

**Q: How do I suspend a customer?**
A: ONUs > Find ONU > Click "Suspend" button

**Q: How do I activate a customer?**
A: ONUs > Find ONU > Click "Activate" button

---

## 📞 Need Help?

1. Check full guide: `FIBER_MANAGEMENT_USER_GUIDE.md`
2. Check implementation docs: `OLT_INTEGRATION_IMPLEMENTATION.md`
3. Check CPE docs: `CPE_MANAGEMENT_SUMMARY.md`

---

**Quick Start Version:** 1.0
**Last Updated:** 2025-02-20
