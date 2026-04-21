# Database Migration System

## Overview
This folder contains database migrations to keep local and production databases in sync.

## Naming Convention
- `YYYY-MM-DD_HHMMSS_description.sql`
- Example: `2025-04-21_143000_add_user_preferences.sql`

## How to Use

### 1. When You Add/Modify Database Tables:

1. Create a new SQL file in this folder with the naming format above
2. Write your SQL changes (CREATE TABLE, ALTER TABLE, etc.)
3. Run the migration: `php apply_migrations.php`

### 2. Example Migration File:

```sql
-- 2025-04-21_143000_add_user_preferences.sql
-- Adds user preferences table

CREATE TABLE IF NOT EXISTS tbl_user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    preference_key VARCHAR(255) NOT NULL,
    preference_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tbl_customers(id) ON DELETE CASCADE
);
```

### 3. Apply Migrations:

Run this after creating new migration files:
```bash
php apply_migrations.php
```

This will:
- Check which migrations have been applied
- Run new migrations in order
- Record applied migrations in `migrations_log.txt`

## Automatic GitHub Sync

When you make changes:
1. **Code changes**: Run `AUTO_PUSH.bat` in project root
2. **Database changes**: 
   - Create migration file in this folder
   - Run `apply_migrations.php`
   - Run `AUTO_PUSH.bat` to push migration to GitHub

## Files in This System

- `MIGRATION_SYSTEM.md` - This documentation
- `apply_migrations.php` - Script to run migrations
- `migrations_log.txt` - Record of applied migrations
- `*.sql` - Individual migration files
