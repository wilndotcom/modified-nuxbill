<?php
/**
 * Database Migration System
 * Applies pending SQL migrations to the database
 */

require_once __DIR__ . '/../../init.php';

$migrationsDir = __DIR__;
$logFile = $migrationsDir . '/migrations_log.txt';

echo "========================================\n";
echo "DATABASE MIGRATION SYSTEM\n";
echo "========================================\n\n";

// Read applied migrations
$appliedMigrations = [];
if (file_exists($logFile)) {
    $appliedMigrations = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

// Get all migration files
$migrationFiles = glob($migrationsDir . '/*.sql');
sort($migrationFiles);

$pendingMigrations = [];
foreach ($migrationFiles as $file) {
    $filename = basename($file);
    if (!in_array($filename, $appliedMigrations) && $filename !== 'phpnuxbill.sql') {
        $pendingMigrations[] = $file;
    }
}

if (empty($pendingMigrations)) {
    echo "No pending migrations. Database is up to date!\n";
    exit(0);
}

echo "Found " . count($pendingMigrations) . " pending migration(s):\n";
foreach ($pendingMigrations as $file) {
    echo "  - " . basename($file) . "\n";
}
echo "\n";

// Apply migrations
$success = true;
foreach ($pendingMigrations as $file) {
    $filename = basename($file);
    echo "Applying: $filename ...\n";
    
    $sql = file_get_contents($file);
    
    try {
        // Execute SQL
        $pdo = ORM::get_db();
        $pdo->exec($sql);
        
        // Log the migration
        file_put_contents($logFile, $filename . "\n", FILE_APPEND);
        
        echo "  ✓ Success\n\n";
    } catch (Exception $e) {
        echo "  ✗ Failed: " . $e->getMessage() . "\n\n";
        $success = false;
        break;
    }
}

if ($success) {
    echo "========================================\n";
    echo "All migrations applied successfully!\n";
    echo "========================================\n";
    exit(0);
} else {
    echo "========================================\n";
    echo "Migration failed! Check errors above.\n";
    echo "========================================\n";
    exit(1);
}
