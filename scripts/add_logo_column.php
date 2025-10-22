<?php
/**
 * Safe script to add `logo_path` column to company_data table.
 * Usage: php scripts/add_logo_column.php
 *
 * This script reads DB connection info from the project's .env file (project root).
 * It supports: pgsql, mysql, sqlite.
 */

function loadEnv($path)
{
    if (!file_exists($path)) return [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $data = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (!strpos($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        $v = preg_replace('/(^"|"$)/', '', $v);
        $v = preg_replace("/(^'|'$)/", '', $v);
        $data[$k] = $v;
    }
    return $data;
}

$env = loadEnv(__DIR__ . '/../.env');

$driver = $env['DB_CONNECTION'] ?? $env['DB_DRIVER'] ?? 'mysql';
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? null;
$database = $env['DB_DATABASE'] ?? null;
$username = $env['DB_USERNAME'] ?? null;
$password = $env['DB_PASSWORD'] ?? null;

echo "DB driver: $driver\n";

try {
    if ($driver === 'sqlite' || $driver === 'sqlite3') {
        $dsn = 'sqlite:' . ($database ?: __DIR__ . '/../database/database.sqlite');
        $pdo = new PDO($dsn);
    } elseif ($driver === 'pgsql' || $driver === 'postgres') {
        $port = $port ?: 5432;
        $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
        $pdo = new PDO($dsn, $username, $password);
    } else {
        // default mysql
        $port = $port ?: 3306;
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
} catch (Exception $e) {
    echo "Failed to connect to DB: " . $e->getMessage() . "\n";
    exit(1);
}

$table = 'company_data';
$column = 'logo_path';

function columnExists(PDO $pdo, $driver, $database, $table, $column)
{
    if ($driver === 'pgsql' || $driver === 'postgres') {
        $sql = "SELECT 1 FROM information_schema.columns WHERE table_schema = 'public' AND table_name = :table AND column_name = :column";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['table' => $table, 'column' => $column]);
        return (bool) $stmt->fetchColumn();
    }
    if ($driver === 'sqlite' || $driver === 'sqlite3') {
        $sql = "PRAGMA table_info('{$table}')";
        $stmt = $pdo->query($sql);
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            if (isset($c['name']) && $c['name'] === $column) return true;
        }
        return false;
    }
    // mysql
    $sql = "SELECT 1 FROM information_schema.columns WHERE table_schema = :db AND table_name = :table AND column_name = :column";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['db' => $database, 'table' => $table, 'column' => $column]);
    return (bool) $stmt->fetchColumn();
}

if (columnExists($pdo, $driver, $database, $table, $column)) {
    echo "Column '{$column}' already exists on table '{$table}'. Nothing to do.\n";
    exit(0);
}

echo "Adding column '{$column}' to table '{$table}'...\n";

try {
    if ($driver === 'pgsql' || $driver === 'postgres') {
        $pdo->exec("ALTER TABLE \"{$table}\" ADD COLUMN {$column} varchar(255);");
    } elseif ($driver === 'sqlite' || $driver === 'sqlite3') {
        // SQLite cannot add column with position, but supports adding a column
        $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$column} varchar(255);");
    } else {
        // mysql
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` varchar(255) NULL;");
    }
    echo "Column added successfully.\n";
} catch (Exception $e) {
    echo "Failed to add column: " . $e->getMessage() . "\n";
    exit(1);
}

// Done
exit(0);
