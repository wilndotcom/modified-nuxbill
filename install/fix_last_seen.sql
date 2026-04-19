-- Direct SQL to add last_seen columns
-- Run this in phpMyAdmin or MySQL

-- Add to tbl_onus
ALTER TABLE tbl_onus ADD COLUMN IF NOT EXISTS last_seen DATETIME NULL;
ALTER TABLE tbl_onus ADD COLUMN IF NOT EXISTS signal_level VARCHAR(20) NULL;
ALTER TABLE tbl_onus ADD COLUMN IF NOT EXISTS distance VARCHAR(20) NULL;
ALTER TABLE tbl_onus ADD COLUMN IF NOT EXISTS uptime VARCHAR(50) NULL;

-- Add to tbl_olt_devices
ALTER TABLE tbl_olt_devices ADD COLUMN IF NOT EXISTS last_seen DATETIME NULL;

-- Add to tbl_cpe_routers
ALTER TABLE tbl_cpe_routers ADD COLUMN IF NOT EXISTS last_seen DATETIME NULL;

-- Alternative for older MySQL versions without IF NOT EXISTS:
-- First check if column exists, then add

-- For tbl_onus (if above fails):
-- ALTER TABLE tbl_onus ADD COLUMN last_seen DATETIME NULL;

-- For tbl_olt_devices (if above fails):
-- ALTER TABLE tbl_olt_devices ADD COLUMN last_seen DATETIME NULL;

-- For tbl_cpe_routers (if above fails):
-- ALTER TABLE tbl_cpe_routers ADD COLUMN last_seen DATETIME NULL;
