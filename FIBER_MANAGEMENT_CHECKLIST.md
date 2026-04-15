# Fiber Management – What Might Be Forgotten / Incomplete

This checklist summarizes gaps, missing pieces, and possible improvements so you can decide what to prioritize.

---

## 1. Missing UI Templates (Will Cause Errors)

### CPE Routers – status and configure pages

- **status.tpl** – Controller calls `$ui->display('admin/fiber/cpe-routers/status.tpl')` but this file **does not exist**.
- **configure.tpl** – Controller calls `$ui->display('admin/fiber/cpe-routers/configure.tpl')` but this file **does not exist**.

**Impact:** Clicking “Status” or “Configure” on a CPE router will trigger a Smarty “template not found” error.

**Action:** Create `ui/ui/admin/fiber/cpe-routers/status.tpl` and `configure.tpl`, or change the controller to redirect/list until those features are built.

---

## 2. Plan / Recharge Integration

### Recharge page does not offer OLT plans

- **File:** `ui/ui/admin/plan/recharge.tpl`
- **Current:** Only “Hotspot”, “PPPOE”, and “VPN” plan types are available.
- **Missing:** No “OLT” or “Fiber” plan type option.

**Impact:** Admins cannot select OLT plans from **Plan > Recharge**. They would have to recharge via another path (e.g. direct DB, custom script, or customer panel if you add OLT there).

**Action:** Add an OLT option (e.g. radio or type filter) and ensure the plan dropdown and backend load OLT plans; “Routers” dropdown may need to be hidden or repurposed for OLT.

### Active plans list – OLT type not handled

- **File:** `ui/ui/admin/plan/active.tpl`
- **Current:** Plan name links go to `services/edit`, `services/pppoe-edit`, or `services/vpn-edit` for Hotspot/PPPOE/VPN. There is no branch for `type == 'OLT'`.
- **Missing:** For OLT type, no specific link (e.g. to ONU or Fiber Management).

**Impact:** OLT plans show in the list, but the plan name link does not point to anything meaningful for OLT.

**Action:** Add `{elseif $ds['type'] == 'OLT'}` with a link to the relevant Fiber/ONU view (e.g. ONU list filtered by customer or plan).

---

## 3. OLT / CPE Drivers – Skeleton Only

- **OLT drivers:** Huawei, ZTE, BDCOM, VSOL (and Generic SNMP) are mostly stubs: connection and ONU activate/suspend/change profile are **not** fully implemented (TODOs in code).
- **CPE drivers:** HTTP API, SNMP, TP-Link, Huawei, ZTE are stubs: connect, reset, reboot, WiFi/DHCP config, etc. are **not** fully implemented.

**Impact:**  
- OLT: Adding/suspending customers via the device driver may not actually run commands on the OLT.  
- CPE: Router status/configure/reboot/reset will often return “not fully implemented” or similar.

**Action:** Implement real CLI/API/SNMP logic per brand (or at least for the brands you use), as described in `OLT_DRIVER_IMPLEMENTATION_GUIDE.md` and CPE docs.

---

## 4. Cron and Sync

- **OLT sync:** `system/cron_olt_sync.php` exists but is **not** called from the main cron (`system/cron.php`). It is meant to be scheduled separately (e.g. `*/10 * * * * php system/cron_olt_sync.php`).
- **Main cron:** When a user expires, `Package::getDevice($p)` is used and OLT device’s `remove_customer` is called, so **expiry → disconnect** is wired.

**Impact:**  
- ONU status (online/offline, signal, etc.) will not auto-update unless you schedule `cron_olt_sync.php`.  
- Expiry-triggered suspension is already integrated.

**Action:** Document that OLT sync must be added to crontab (or call `cron_olt_sync.php` from your own cron wrapper). Optionally add a note/link in the UI (e.g. Fiber Management or Help).

---

## 5. Pagination Not Shown in Fiber Lists

- **Controller:** Uses `Paginator::findMany()` for ONUs, Profiles, and CPE Routers lists (and assigns `paginator` to the UI).
- **Templates:** None of the Fiber list templates use `paginator` (no pagination block).

**Impact:** With many ONUs/profiles/CPEs, only the first page (e.g. 10 items) is visible with no way to go to the next page.

**Action:** Add a pagination block in:
- `ui/ui/admin/fiber/onus/list.tpl`
- `ui/ui/admin/fiber/profiles/list.tpl`
- `ui/ui/admin/fiber/cpe-routers/list.tpl`  
Use the same pattern as other admin lists (e.g. `customers/list.tpl` or `ticket/list.tpl`) and the `paginator` variable.

---

## 6. Security / Consistency

- **CSRF:** Fiber forms (OLT devices, ONUs, profiles, CPE add/edit, etc.) do **not** appear to use `stoken` / `getToken()` / `csrf_token`. Other admin forms (e.g. customers, plan deposit) do.
- **Confirmation:** Destructive actions (delete OLT/ONU/profile/CPE, suspend ONU) use `confirm()` in JS; no server-side double-check or token.

**Impact:** Forms are slightly more vulnerable to CSRF than the rest of the admin panel.

**Action:** Add CSRF token to all Fiber POST forms and validate it in the controller (same pattern as plan/customers). Optionally add server-side confirmation for critical actions.

---

## 7. CPE List Filters and GET Parameters

- **ONU list:** Supports both POST and GET for filters (`search`, `olt_id`, `status`), so dashboard links like “Active ONUs” can open with a pre-applied filter.
- **CPE list:** Filters are read only from POST. There is no `_get()` fallback.

**Impact:** You cannot deep-link to “CPE Routers with status=Online” (e.g. from a future dashboard widget); the filter would not apply when opening the page via GET.

**Action:** If you want linkable CPE filters, in the CPE list case use the same pattern as ONU list: e.g. `$status_filter = _post('status') ?: _get('status') ?: '';` (and same for other filters).

---

## 8. Monitoring Dashboard – Extra Clickable Stats

- **Current:** “Total OLTs”, “Online OLTs”, “Total ONUs”, and “Active ONUs” are clickable and link to the right lists (with “Active ONUs” filtered).
- **Missing:** “Suspended ONUs” and “Inactive ONUs” (if shown on the dashboard) are not clickable.

**Impact:** Minor UX: users cannot jump in one click to suspended or inactive ONU lists.

**Action:** If the dashboard shows suspended/inactive counts, make those stat boxes clickable to the ONU list with `status=Suspended` and `status=Inactive` (and ensure ONU list accepts these via GET, which it already does).

---

## 9. Language / Translations

- Many Fiber and CPE strings were added to `system/lan/english.json`. Other languages (if any) were not updated.

**Impact:** In non-English locales, new Fiber/CPE labels may fall back to keys or English.

**Action:** If you use multiple languages, add the same keys to the other language files.

---

## 10. Database / Migration

- **OLT tables:** Created by `create_olt_tables.php` or the migrations in `system/updates.json` (version 2025.12.1).
- **CPE tables:** Created by `create_cpe_tables.php` or migrations (version 2025.12.2).  
- **Foreign keys:** `tbl_cpe_routers` has FK to `tbl_olt_onu`. If OLT tables are created after CPE tables, creation order can matter.

**Impact:** Generally fine if migrations are run in the correct order; just something to keep in mind for fresh installs or new environments.

**Action:** Document recommended order (e.g. OLT tables first, then CPE) in your deployment/install notes.

---

## 11. Customer View – Link to ONU/CPE

- Customer detail view (e.g. “View Customer”) likely does not show “Assigned ONU” or “CPE Router” with links to Fiber Management.

**Impact:** To see a customer’s ONU or CPE, staff must go to Fiber > ONUs or CPE Routers and search/filter.

**Action:** Optional: on the customer view page, if the customer has an ONU or CPE, show a link to the corresponding ONU/CPE in Fiber Management.

---

## 12. Suspended / Inactive Counts on Monitoring Dashboard

- The monitoring controller passes stats (e.g. `active_onus`, `total_onus`). Confirm whether `suspended_onus` and `inactive_onus` are computed and passed to the template.

**Impact:** If the template expects these but they are not assigned, you may get empty or undefined values.

**Action:** Verify in the controller that all stats displayed on the dashboard are assigned; add any missing ones (e.g. `suspended_onus`, `inactive_onus`).

---

## Summary Table

| Item | Severity | Effort |
|------|----------|--------|
| Missing CPE `status.tpl` and `configure.tpl` | High (runtime error) | Medium (create templates or redirect) |
| OLT plans on Recharge page | High (feature missing) | Medium |
| OLT in Active plans list (link) | Medium | Low |
| OLT/CPE drivers implementation | High (real device control) | High |
| Cron OLT sync documented/scheduled | Medium | Low |
| Pagination in Fiber lists | Medium | Low |
| CSRF on Fiber forms | Medium | Medium |
| CPE list GET params for filters | Low | Low |
| Dashboard Suspended/Inactive clickable | Low | Low |
| Other languages | Low | Per language |
| Customer view ONU/CPE link | Low | Low |

If you tell me which of these you want to tackle first (e.g. “fix missing CPE templates” or “add OLT to recharge”), I can outline or implement the exact code changes step by step.
