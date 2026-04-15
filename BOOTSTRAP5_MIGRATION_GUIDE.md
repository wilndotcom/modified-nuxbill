# Bootstrap 5 Migration Guide

This guide helps you migrate from Bootstrap 3 to Bootstrap 5 in PHPNuxBill templates.

## Quick Reference: Class Replacements

### Layout & Grid

| Bootstrap 3 | Bootstrap 5 | Notes |
|------------|-------------|-------|
| `col-xs-*` | `col-*` | Extra small breakpoint |
| `col-sm-*` | `col-sm-*` | ✅ Same |
| `col-md-*` | `col-md-*` | ✅ Same |
| `col-lg-*` | `col-lg-*` | ✅ Same |
| `col-xl-*` | `col-xl-*` | ✅ Same (new in BS5) |
| `col-xxl-*` | `col-xxl-*` | New breakpoint in BS5 |

### Float Utilities

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `pull-left` | `float-start` |
| `pull-right` | `float-end` |
| `clearfix` | `clearfix` ✅ Same |

### Display Utilities

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `hidden-xs` | `d-none d-sm-block` |
| `hidden-sm` | `d-sm-none d-md-block` |
| `hidden-md` | `d-md-none d-lg-block` |
| `hidden-lg` | `d-lg-none d-xl-block` |
| `visible-xs` | `d-block d-sm-none` |
| `visible-sm` | `d-sm-block d-md-none` |
| `visible-md` | `d-md-block d-lg-none` |
| `visible-lg` | `d-lg-block d-xl-none` |

### Buttons

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `btn-default` | `btn-secondary` |
| `btn-xs` | `btn-sm` (or use custom size) |
| `btn-lg` | `btn-lg` ✅ Same |
| `btn-block` | `w-100` (width utility) |

### Labels & Badges

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `label` | `badge` |
| `label-default` | `badge bg-secondary` |
| `label-primary` | `badge bg-primary` |
| `label-success` | `badge bg-success` |
| `label-info` | `badge bg-info` |
| `label-warning` | `badge bg-warning` |
| `label-danger` | `badge bg-danger` |

### Panels & Cards

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `panel` | `card` |
| `panel-body` | `card-body` |
| `panel-heading` | `card-header` |
| `panel-footer` | `card-footer` |
| `panel-title` | `card-title` |
| `panel-primary` | `card border-primary` |
| `panel-success` | `card border-success` |
| `panel-info` | `card border-info` |
| `panel-warning` | `card border-warning` |
| `panel-danger` | `card border-danger` |

### Forms

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `form-group` | `mb-3` (margin utility) |
| `form-control-static` | `form-control-plaintext` |
| `input-group-addon` | `input-group-text` |
| `input-lg` | `form-control-lg` |
| `input-sm` | `form-control-sm` |
| `help-block` | `form-text` |
| `checkbox-inline` | `form-check-inline` |
| `radio-inline` | `form-check-inline` |
| `has-error` | `is-invalid` |
| `has-success` | `is-valid` |
| `has-warning` | `is-warning` |
| `control-label` | `form-label` |

### Navbar

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `navbar-toggle` | `navbar-toggler` |
| `navbar-collapse` | `navbar-collapse` ✅ Same |
| `navbar-default` | `navbar-light` or `navbar-dark` |
| `navbar-inverse` | `navbar-dark` |
| `navbar-fixed-top` | `fixed-top` |
| `navbar-fixed-bottom` | `fixed-bottom` |

### Tables

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `table-condensed` | `table-sm` |
| `table-responsive` | `table-responsive` ✅ Same |

### Wells & Alerts

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `well` | `card` or `alert` |
| `well-sm` | `card card-sm` |
| `well-lg` | `card card-lg` |
| `alert-link` | `alert-link` ✅ Same |

### Images

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `img-responsive` | `img-fluid` |
| `img-circle` | `rounded-circle` |
| `img-rounded` | `rounded` |
| `img-thumbnail` | `img-thumbnail` ✅ Same |

### Pagination

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `pagination-lg` | `pagination-lg` ✅ Same |
| `pagination-sm` | `pagination-sm` ✅ Same |

### Progress Bars

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `progress-bar-striped` | `progress-bar-striped` ✅ Same |
| `progress-bar-active` | `progress-bar-animated` |

### List Groups

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `list-group-item-heading` | `list-group-item-heading` ✅ Same |
| `list-group-item-text` | `list-group-item-text` ✅ Same |

### Dropdowns

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `dropdown-menu-right` | `dropdown-menu-end` |
| `dropdown-menu-left` | `dropdown-menu-start` |

### Modals

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `modal-dialog-centered` | `modal-dialog-centered` ✅ Same |
| `modal-sm` | `modal-sm` ✅ Same |
| `modal-lg` | `modal-lg` ✅ Same |

### Tooltips & Popovers

| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `data-toggle="tooltip"` | `data-bs-toggle="tooltip"` |
| `data-toggle="popover"` | `data-bs-toggle="popover"` |
| `data-placement` | `data-bs-placement` |

### JavaScript API Changes

#### Tooltips
```javascript
// Bootstrap 3
$('[data-toggle="tooltip"]').tooltip();

// Bootstrap 5
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl);
});
```

#### Popovers
```javascript
// Bootstrap 3
$('[data-toggle="popover"]').popover();

// Bootstrap 5
var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
  return new bootstrap.Popover(popoverTriggerEl);
});
```

#### Modals
```javascript
// Bootstrap 3
$('#myModal').modal('show');

// Bootstrap 5
var myModal = new bootstrap.Modal(document.getElementById('myModal'));
myModal.show();
```

## Common Migration Patterns

### Example 1: Panel to Card
```html
<!-- Bootstrap 3 -->
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">Title</h3>
    </div>
    <div class="panel-body">
        Content
    </div>
    <div class="panel-footer">
        Footer
    </div>
</div>

<!-- Bootstrap 5 -->
<div class="card border-primary">
    <div class="card-header">
        <h3 class="card-title">Title</h3>
    </div>
    <div class="card-body">
        Content
    </div>
    <div class="card-footer">
        Footer
    </div>
</div>
```

### Example 2: Label to Badge
```html
<!-- Bootstrap 3 -->
<span class="label label-success">Success</span>
<span class="label label-danger">Danger</span>

<!-- Bootstrap 5 -->
<span class="badge bg-success">Success</span>
<span class="badge bg-danger">Danger</span>
```

### Example 3: Float Utilities
```html
<!-- Bootstrap 3 -->
<div class="pull-right">Right aligned</div>
<div class="pull-left">Left aligned</div>

<!-- Bootstrap 5 -->
<div class="float-end">Right aligned</div>
<div class="float-start">Left aligned</div>
```

### Example 4: Form Groups
```html
<!-- Bootstrap 3 -->
<div class="form-group">
    <label class="control-label">Name</label>
    <input type="text" class="form-control">
    <span class="help-block">Help text</span>
</div>

<!-- Bootstrap 5 -->
<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" class="form-control">
    <div class="form-text">Help text</div>
</div>
```

### Example 5: Responsive Display
```html
<!-- Bootstrap 3 -->
<div class="hidden-xs hidden-sm">Visible on md+</div>
<div class="visible-xs">Visible only on xs</div>

<!-- Bootstrap 5 -->
<div class="d-none d-md-block">Visible on md+</div>
<div class="d-block d-sm-none">Visible only on xs</div>
```

## Migration Checklist

When migrating a template file:

1. ✅ Replace all `pull-left` → `float-start`
2. ✅ Replace all `pull-right` → `float-end`
3. ✅ Replace all `col-xs-*` → `col-*`
4. ✅ Replace all `btn-default` → `btn-secondary`
5. ✅ Replace all `panel` → `card`
6. ✅ Replace all `label-*` → `badge bg-*`
7. ✅ Replace all `hidden-*` → `d-none d-*-block`
8. ✅ Replace all `visible-*` → `d-block d-*-none`
9. ✅ Replace all `form-group` → `mb-3`
10. ✅ Replace all `control-label` → `form-label`
11. ✅ Replace all `help-block` → `form-text`
12. ✅ Replace all `navbar-toggle` → `navbar-toggler`
13. ✅ Replace all `img-responsive` → `img-fluid`
14. ✅ Replace all `img-circle` → `rounded-circle`
15. ✅ Update JavaScript initialization (tooltips, popovers, modals)
16. ✅ Test all interactive features
17. ✅ Test responsive behavior
18. ✅ Check accessibility

## Automated Migration Script

You can use find/replace in your IDE with these patterns:

### Find/Replace Patterns:
1. `pull-right` → `float-end`
2. `pull-left` → `float-start`
3. `col-xs-(\d+)` → `col-$1` (regex)
4. `btn-default` → `btn-secondary`
5. `panel ` → `card `
6. `panel-body` → `card-body`
7. `panel-heading` → `card-header`
8. `panel-footer` → `card-footer`
9. `label ` → `badge `
10. `label-default` → `badge bg-secondary`
11. `label-primary` → `badge bg-primary`
12. `label-success` → `badge bg-success`
13. `label-info` → `badge bg-info`
14. `label-warning` → `badge bg-warning`
15. `label-danger` → `badge bg-danger`

## Testing After Migration

After migrating, test:

1. ✅ Layout renders correctly
2. ✅ Forms work properly
3. ✅ Buttons function correctly
4. ✅ Modals open/close
5. ✅ Dropdowns work
6. ✅ Tooltips show
7. ✅ Responsive breakpoints work
8. ✅ JavaScript interactions work
9. ✅ No console errors
10. ✅ Accessibility features work

## Need Help?

Refer to:
- [Bootstrap 5 Migration Guide](https://getbootstrap.com/docs/5.3/migration/)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.3/)
- PHPNuxBill Frontend Modernization Plan
