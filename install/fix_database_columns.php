<?php
/**
 * Fix Missing Database Columns for Fiber Management
 */

include '../init.php';

echo "<h2>🔧 Fixing Database Columns</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px;'>\n";

$errors = [];
$fixes = [];

// Function to check if column exists
function columnExists($table, $column) {
    try {
        $columns = ORM::for_table($table)->raw_query("SHOW COLUMNS FROM {$table}")->find_array();
        foreach ($columns as $col) {
            if ($col['Field'] === $column) {
                return true;
            }
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

// Function to add column
function addColumn($table, $column, $type, $default = null) {
    try {
        $sql = "ALTER TABLE {$table} ADD COLUMN {$column} {$type}";
        if ($default !== null) {
            $sql .= " DEFAULT '{$default}'";
        }
        $sql .= " NULL";
        
        ORM::for_table($table)->raw_query($sql);
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

echo "<strong>Checking tbl_onus columns...</strong>\n";

// Columns needed for tbl_onus
$onusColumns = [
    'last_seen' => 'DATETIME',
    'signal_level' => 'VARCHAR(20)',
    'distance' => 'VARCHAR(20)',
    'uptime' => 'VARCHAR(50)',
];

foreach ($onusColumns as $col => $type) {
    if (columnExists('tbl_onus', $col)) {
        echo "  ✓ {$col} exists\n";
    } else {
        echo "  ⚠ {$col} missing - adding...\n";
        $result = addColumn('tbl_onus', $col, $type);
        if ($result === true) {
            echo "    ✓ Added {$col}\n";
            $fixes[] = "Added {$col} to tbl_onus";
        } else {
            echo "    ✗ Error: {$result}\n";
            $errors[] = "Failed to add {$col}: {$result}";
        }
    }
}

echo "\n<strong>Checking tbl_olt_devices columns...</strong>\n";

// Columns needed for tbl_olt_devices
$oltColumns = [
    'last_seen' => 'DATETIME',
];

foreach ($oltColumns as $col => $type) {
    if (columnExists('tbl_olt_devices', $col)) {
        echo "  ✓ {$col} exists\n";
    } else {
        echo "  ⚠ {$col} missing - adding...\n";
        $result = addColumn('tbl_olt_devices', $col, $type);
        if ($result === true) {
            echo "    ✓ Added {$col}\n";
            $fixes[] = "Added {$col} to tbl_olt_devices";
        } else {
            echo "    ✗ Error: {$result}\n";
            $errors[] = "Failed to add {$col}: {$result}";
        }
    }
}

echo "\n<strong>Checking tbl_cpe_routers columns...</strong>\n";

// Columns needed for tbl_cpe_routers
$cpeColumns = [
    'last_seen' => 'DATETIME',
];

foreach ($cpeColumns as $col => $type) {
    if (columnExists('tbl_cpe_routers', $col)) {
        echo "  ✓ {$col} exists\n";
    } else {
        echo "  ⚠ {$col} missing - adding...\n";
        $result = addColumn('tbl_cpe_routers', $col, $type);
        if ($result === true) {
            echo "    ✓ Added {$col}\n";
            $fixes[] = "Added {$col} to tbl_cpe_routers";
        } else {
            echo "    ✗ Error: {$result}\n";
            $errors[] = "Failed to add {$col}: {$result}";
        }
    }
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
if (count($errors) === 0) {
    echo "✅ <strong style='color: green;'>ALL COLUMNS FIXED!</strong>\n";
} else {
    echo "❌ <strong style='color: red;'>ERRORS FOUND:</strong>\n";
    foreach ($errors as $e) {
        echo "  ✗ {$e}\n";
    }
}

if (count($fixes) > 0) {
    echo "\n<strong>Fixes Applied:</strong>\n";
    foreach ($fixes as $f) {
        echo "  ✓ {$f}\n";
    }
}

echo "\n<a href='../?_route=dashboard' style='padding: 10px 20px; background: #337ab7; color: white; text-decoration: none; border-radius: 4px;'>Go to Dashboard</a>\n";

echo "</pre>";
