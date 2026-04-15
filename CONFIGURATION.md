# PHPNuxBill Configuration Guide

This guide explains how to configure PHPNuxBill for your environment.

## Configuration Files

PHPNuxBill uses multiple configuration methods:

1. **config.php** - Main configuration file (database settings, app stage)
2. **Database (tbl_appconfig)** - Application settings stored in database
3. **.env file** - Environment variables (optional, for advanced users)

## Quick Setup

### 1. Initial Configuration

Copy the example configuration file:

```bash
cp config.example.php config.php
```

Edit `config.php` and update the database settings:

```php
$db_host    = "localhost";     // Your database host
$db_user    = "root";          // Your database username
$db_pass    = "your_password"; // Your database password
$db_name    = "phpnuxbill";    // Your database name
```

### 2. Installation

Run the installer at: `http://your-domain/install/`

The installer will:
- Create the database tables
- Set up initial configuration
- Create admin account

## Configuration Options

### Database Configuration

#### Main Database (Required)
```php
$db_host    = "localhost";     // Database server hostname or IP
$db_port    = "";             // Database port (leave blank for default 3306)
$db_user    = "root";         // Database username
$db_pass    = "";             // Database password
$db_name    = "phpnuxbill";   // Database name
```

#### Radius Database (Optional)
If using FreeRadius with MySQL, uncomment and configure:

```php
$radius_host    = "localhost";
$radius_user    = "root";
$radius_pass    = "";
$radius_name    = "radius";
```

### Application Stage

```php
$_app_stage = 'Live';  // Options: 'Live', 'Dev', 'Demo'
```

- **Live**: Production mode - errors hidden, optimized for performance
- **Dev**: Development mode - errors shown, detailed logging
- **Demo**: Demo mode - similar to Live but may have restrictions

### Environment Variables (.env file)

For advanced users, you can use a `.env` file for configuration:

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` with your settings:
   ```
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=your_password
   DB_NAME=phpnuxbill
   APP_STAGE=Live
   ```

**Note**: The `.env` file is optional. If not used, configuration from `config.php` will be used.

## Application Settings

Most application settings are stored in the database (`tbl_appconfig` table) and can be configured through the admin panel:

- **Settings → Application Settings** - General application configuration
- **Settings → Payment Gateway** - Payment gateway configuration
- **Settings → SMS/WhatsApp/Telegram** - Notification settings
- **Settings → Router** - Mikrotik router settings

## Configuration Helper Class

PHPNuxBill includes a `Config` helper class for programmatic configuration management:

```php
// Get configuration value
$value = Config::get($config, 'setting_key', 'default_value');

// Set configuration value
Config::set('setting_key', 'value');

// Check if configuration exists
if (Config::has('setting_key')) {
    // ...
}

// Validate database connection
$result = Config::validateDatabase($host, $user, $pass, $name);
if ($result['valid']) {
    echo "Database connection successful!";
} else {
    echo "Error: " . $result['message'];
}
```

## Security Best Practices

1. **Never commit config.php to version control**
   - `config.php` is already in `.gitignore`
   - Always use `config.example.php` as a template

2. **Protect your .env file**
   - `.env` is also in `.gitignore`
   - Set proper file permissions: `chmod 600 .env`

3. **Use strong database passwords**
   - Use complex passwords for database access
   - Limit database user permissions

4. **Keep APP_STAGE as 'Live' in production**
   - Never expose errors in production
   - Use 'Dev' only in development environments

## Troubleshooting

### Database Connection Issues

If you're having database connection problems:

1. Verify database credentials in `config.php`
2. Check if MySQL service is running
3. Verify database user has proper permissions
4. Check firewall settings if using remote database

Use the Config helper to test connection:

```php
$result = Config::validateDatabase($db_host, $db_user, $db_pass, $db_name);
var_dump($result);
```

### Configuration Not Loading

If configuration values aren't loading:

1. Check if `tbl_appconfig` table exists
2. Verify database connection
3. Check file permissions on `config.php`
4. Review error logs

### Environment Variables Not Working

If `.env` file isn't being loaded:

1. Verify `.env` file exists in root directory
2. Check file permissions
3. Ensure file format is correct (KEY=VALUE)
4. Check for syntax errors (no spaces around `=`)

## Migration from Old Versions

If upgrading from an older version:

1. Backup your current `config.php`
2. Compare with `config.example.php` for new options
3. Update any deprecated settings
4. Test configuration after migration

## Additional Resources

- [Installation Guide](https://github.com/hotspotbilling/phpnuxbill/wiki)
- [Configuration Settings Documentation](https://github.com/hotspotbilling/phpnuxbill/wiki/Settings)
- [Community Support](https://github.com/hotspotbilling/phpnuxbill/discussions)
