# Component Usage Guide

Quick guide for using the modern PHPNuxBill components.

## Form Components

### Form Input

```smarty
{include file="components/form-input.tpl"
    name="username"
    label="Username"
    type="text"
    placeholder="Enter username"
    required=true
    help="Choose a unique username"
    icon="fa fa-user"
    inputGroup=true
}
```

### Form Select

```smarty
{include file="components/form-select.tpl"
    name="service_type"
    label="Service Type"
    options=[
        {value: "Hotspot", text: "Hotspot"},
        {value: "PPPoE", text: "PPPoE"},
        {value: "VPN", text: "VPN"}
    ]
    required=true
    help="Select the service type"
}
```

### Card Component

```smarty
{include file="components/card.tpl"
    title="Card Title"
    borderColor="primary"
    content="Card content here"
    footer="<button class='btn btn-primary'>Action</button>"
}
```

## JavaScript Forms Module

### Initialize Form Validation

```javascript
// Automatic - all forms with .needs-validation are auto-validated
// Or manually:
import { Forms } from './modules/forms.js';
Forms.initValidation('#myForm');
```

### Submit Form via AJAX

```javascript
import { Forms } from './modules/forms.js';

const form = document.querySelector('#myForm');
Forms.submitAjax(form, {
    url: '/api/submit',
    method: 'POST',
    onSuccess: (data) => {
        console.log('Success!', data);
    },
    onError: (error) => {
        console.error('Error:', error);
    },
    successMessage: 'Form submitted successfully',
    errorMessage: 'An error occurred'
});
```

### Set Field Error

```javascript
Forms.setFieldError('#username', 'Username is required');
```

### Clear Field Error

```javascript
Forms.clearFieldError('#username');
```

## Migration Examples

### Old Panel → New Card

```smarty
{* Old Bootstrap 3 *}
<div class="panel panel-primary">
    <div class="panel-heading">Title</div>
    <div class="panel-body">Content</div>
</div>

{* New Bootstrap 5 Component *}
{include file="components/card.tpl"
    title="Title"
    borderColor="primary"
    content="Content"
}
```

### Old Form Group → New Form Input

```smarty
{* Old Bootstrap 3 *}
<div class="form-group">
    <label class="control-label">Username</label>
    <input type="text" class="form-control" name="username">
    <span class="help-block">Help text</span>
</div>

{* New Bootstrap 5 Component *}
{include file="components/form-input.tpl"
    name="username"
    label="Username"
    help="Help text"
}
```

## Best Practices

1. **Always use components** for consistency
2. **Add validation** attributes (required, pattern, etc.)
3. **Use Bootstrap 5 classes** not Bootstrap 3
4. **Include help text** for better UX
5. **Handle errors** properly with error parameter

## See Also

- [Component Library README](./ui/ui/admin/components/README.md)
- [Component Examples](./ui/ui/admin/components/example.tpl)
- [Bootstrap 5 Migration Guide](./BOOTSTRAP5_MIGRATION_GUIDE.md)
