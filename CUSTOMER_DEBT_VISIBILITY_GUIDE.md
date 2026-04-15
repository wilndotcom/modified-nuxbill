# Customer Debt Visibility Guide

## Overview
This document explains **all the places** where customers can see if their wallet/account has debt (negative balance) in the customer portal.

---

## Where Customers Can See Debt Status

### 1. **Top Navigation Bar (Always Visible)** 🔴
**Location**: Top-right corner of every page

**What they see**:
- **Positive balance**: Normal white text showing balance amount
- **Negative balance (Debt)**: 
  - Red text showing negative balance
  - Small yellow "(Debt)" label next to it

**Example**:
- Positive: `$50.00`
- Debt: `-$25.00 (Debt)` in red

**Visibility**: ✅ Always visible on every page

---

### 2. **Prominent Alert Banner (All Pages)** 🚨
**Location**: Top of content area on every page (below header)

**What they see**:
- **Red alert box** with warning icon
- **Title**: "Account Has Debt"
- **Current balance** displayed prominently in red
- **Explanation**: "You have a negative balance (debt). Please make a payment to clear your debt. When you pay, the amount will automatically reduce your debt."
- **"Pay Now" button** (if payment gateway enabled)

**Visibility**: ✅ Shows on ALL pages when balance is negative
**Dismissible**: ✅ Yes (can close with X button)

**Example**:
```
⚠️ Account Has Debt
Your current balance: -$25.00
You have a negative balance (debt). Please make a payment...
[Pay Now Button]
```

---

### 3. **User Dropdown Menu** 👤
**Location**: Click on profile picture/balance in top-right

**What they see**:
- Profile picture and name
- **Balance section**:
  - **Positive**: Green balance amount
  - **Negative**: Red balance amount + Red "Debt" badge

**Visibility**: ✅ Visible when clicking profile dropdown

---

### 4. **Dashboard Widget (Account Info)** 📊
**Location**: Dashboard page - "Your Account Information" widget

**What they see**:
- **Balance row**:
  - **Positive**: Green balance amount (larger font)
  - **Negative**: Red balance amount + Red "Debt" badge

**Visibility**: ✅ Visible on dashboard/home page

---

### 5. **Sidebar Balance Display** 📱
**Location**: Left sidebar menu (below Dashboard)

**What they see**:
- **Balance section** with label
- **Positive**: Green balance amount
- **Negative**: Red balance amount + Red "Debt" badge

**Visibility**: ✅ Always visible in sidebar on all pages

---

### 6. **Buy Package Page (Order Plan)** 📦
**Location**: "Order Internet Package" page

**What they see**:
- **Yellow warning banner** at top:
  - "Note: You have a debt of -$25.00"
  - "You can still purchase packages. When you make a payment, it will automatically reduce your debt."
  - "Add Balance" button
- **Plan cards**: Each plan shows:
  - Red "Debt" badge if balance is negative
  - Yellow "Insufficient" badge if balance is positive but less than plan price
  - Green "Affordable" badge if balance is sufficient

**Visibility**: ✅ Visible when viewing packages

---

### 7. **Buy Balance Page** 💰
**Location**: "Buy Balance Package" page

**What they see**:
- **Yellow warning banner**:
  - "Debt Notice: Your account has a debt of -$25.00"
  - "Adding balance will automatically reduce your debt."

**Visibility**: ✅ Visible when viewing balance purchase page

---

## Visual Indicators Summary

### Color Coding:
- 🟢 **Green** = Positive balance (good)
- 🔴 **Red** = Negative balance / Debt (needs attention)
- 🟡 **Yellow** = Warning / Information

### Badges/Labels:
- **"Debt"** badge = Red label indicating negative balance
- **"Affordable"** badge = Green label (can afford plan)
- **"Insufficient"** badge = Yellow label (balance too low)
- **"Debt"** badge on plans = Red label (has debt)

### Alert Types:
- **Red Alert Banner** = Critical (debt exists) - appears on all pages
- **Yellow Warning** = Informational (on specific pages)

---

## Customer Experience Flow

### When Customer Has Debt:

1. **Login** → Sees red balance in top navigation
2. **Any Page** → Sees prominent red alert banner at top
3. **Dashboard** → Sees red balance in widget + sidebar
4. **View Packages** → Sees yellow warning + red "Debt" badges on plans
5. **Buy Balance** → Sees yellow notice explaining debt reduction

### When Customer Pays:

1. **Payment processed** → Balance increases
2. **If balance was negative** → Debt is automatically reduced
3. **Example**: Balance was -$25.00, customer pays $50.00 → New balance = $25.00
4. **All debt indicators disappear** → Balance turns green

---

## Key Messages Shown to Customers

### Alert Banner Message:
> "⚠️ **Account Has Debt**  
> Your current balance: **-$25.00**  
> You have a negative balance (debt). Please make a payment to clear your debt. When you pay, the amount will automatically reduce your debt."

### Package Page Warning:
> "**Note**: You have a debt of **-$25.00**. You can still purchase packages. When you make a payment, it will automatically reduce your debt."

### Balance Page Notice:
> "**Debt Notice**: Your account has a debt of **-$25.00**. Adding balance will automatically reduce your debt."

---

## Summary

✅ **7 different locations** where customers can see debt status:
1. Top navigation bar (always visible)
2. Alert banner (all pages)
3. User dropdown menu
4. Dashboard widget
5. Sidebar balance display
6. Buy Package page
7. Buy Balance page

✅ **Multiple visual indicators**:
- Color coding (red for debt)
- Badges and labels
- Alert banners
- Warning messages

✅ **Clear messaging**:
- Explains what debt means
- Shows current balance
- Explains how payments reduce debt
- Provides "Pay Now" buttons

✅ **User-friendly**:
- Prominent but not intrusive
- Dismissible alerts
- Clear call-to-action buttons
- Consistent across all pages

---

## Technical Implementation

- **Debt Detection**: `$_user['balance'] < 0`
- **Display Logic**: Conditional rendering based on balance value
- **Styling**: Red color (#dc3545) for negative, green (#28a745) for positive
- **Badges**: Bootstrap label classes (`label-danger` for debt)
- **Alerts**: Bootstrap alert components with custom styling

---

**Result**: Customers **cannot miss** their debt status - it's visible in multiple prominent locations throughout the portal!
