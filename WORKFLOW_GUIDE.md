# Development Workflow Guide

## 🚀 Quick Start - Making Changes

### For Code Changes (CSS, PHP, Templates):
1. Edit your files
2. Double-click: `AUTO_PUSH.bat`
3. Done! Changes are on GitHub

### For Database Changes:
1. Double-click: `database\migrations\create_migration.bat`
2. Enter a description (e.g., `add_user_settings`)
3. Edit the created `.sql` file with your changes
4. Double-click: `AUTO_PUSH.bat`
5. Done! Database + code pushed to GitHub

---

## 📁 Files You Need to Know

| File | Purpose | When to Use |
|------|---------|-------------|
| `AUTO_PUSH.bat` | Push all changes to GitHub | After ANY changes |
| `database\migrations\create_migration.bat` | Create new database migration | When adding/modifying tables |
| `database\migrations\apply_migrations.php` | Apply pending migrations | Runs automatically in AUTO_PUSH |
| `SYNC_WITH_GITHUB.bat` | Pull latest from GitHub | When starting work |
| `COMPARE_LOCAL_VS_GITHUB.bat` | Check differences | To verify sync status |

---

## 🔄 Complete Workflow

### Daily Development:

```
1. Start work:
   → Run: SYNC_WITH_GITHUB.bat (pull latest)

2. Make your changes:
   → Edit code files
   → If database changes needed:
     → Run: database\migrations\create_migration.bat
     → Edit the created .sql file

3. Push changes:
   → Run: AUTO_PUSH.bat
   → This will:
     - Apply any new database migrations
     - Commit all code changes
     - Push to GitHub
```

---

## 🗄️ Database Migration Examples

### Adding a New Table:

1. Run: `database\migrations\create_migration.bat`
2. Enter: `add_customer_notifications`
3. Edit the created file:

```sql
-- Migration: add_customer_notifications
-- Created: 2025-04-21
-- Author: wilndotcom

CREATE TABLE IF NOT EXISTS tbl_customer_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES tbl_customers(id) ON DELETE CASCADE
);
```

4. Run: `AUTO_PUSH.bat`

### Modifying an Existing Table:

```sql
-- Migration: add_phone_to_customers

ALTER TABLE tbl_customers
ADD COLUMN phone_number VARCHAR(20) AFTER email;
```

---

## ⚡ Emergency Commands

If AUTO_PUSH fails:

```bash
cd C:\xampp\htdocs\modified-nuxbill
git add -A
git commit -m "manual: your message" --author="wilndotcom <kennethndugi@gmail.com>"
git pull origin main --no-rebase
git push origin main
```

---

## 📊 Checking Status

### Check GitHub vs Local:
```
Run: COMPARE_LOCAL_VS_GITHUB.bat
```

### Check Database Migrations:
```bash
cd C:\xampp\htdocs\modified-nuxbill\database\migrations
php apply_migrations.php
```

---

## ✅ Best Practices

1. **Always run AUTO_PUSH after changes** - Don't let work pile up
2. **Create migrations for DB changes** - Never modify DB directly without migration
3. **Use descriptive migration names** - `add_user_settings` not `update1`
4. **Test locally first** - Run migrations before pushing
5. **Check status regularly** - Run COMPARE_LOCAL_VS_GITHUB.bat weekly

---

## 🆘 Troubleshooting

### "Failed to push" error:
```
→ Run: SYNC_WITH_GITHUB.bat (pulls latest, then pushes)
```

### Database migration failed:
```
→ Check the .sql file syntax
→ Fix the error
→ Run: php apply_migrations.php again
```

### Changes not showing on GitHub:
```
→ Clear browser cache
→ Check: https://github.com/wilndotcom/modified-nuxbill/commits/main
```

---

## 🔗 Repository

**GitHub:** https://github.com/wilndotcom/modified-nuxbill  
**Author:** wilndotcom <kennethndugi@gmail.com>
