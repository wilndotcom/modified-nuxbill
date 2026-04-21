-- Fix for admin_read_at column error
-- Run this in phpMyAdmin or MySQL console

-- Check if column exists, if not add it
SET @dbname = DATABASE();
SET @tablename = 'tbl_tickets';
SET @columnname = 'admin_read_at';

SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = @dbname 
AND table_name = @tablename 
AND column_name = @columnname;

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE tbl_tickets ADD COLUMN admin_read_at DATETIME DEFAULT NULL', 
    'SELECT "Column already exists" as message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for performance
SET @indexname = 'idx_admin_read_at';
SELECT COUNT(*) INTO @idx_exists 
FROM information_schema.statistics 
WHERE table_schema = @dbname 
AND table_name = @tablename 
AND index_name = @indexname;

SET @sql2 = IF(@idx_exists = 0, 
    'ALTER TABLE tbl_tickets ADD INDEX idx_admin_read_at (admin_read_at)', 
    'SELECT "Index already exists" as message'
);
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
