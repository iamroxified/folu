<?php
// Prevent redundant PDO instantiation if $pdo is already available
global $pdo;

if (isset($pdo) && $pdo instanceof PDO) {
    $GLOBALS['pdo'] = $pdo;
    return;
}

// 1. Re-use active Laravel database connection if running within Laravel application
if (class_exists('\Illuminate\Support\Facades\DB')) {
    try {
        $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
        if ($pdo instanceof PDO) {
            $GLOBALS['pdo'] = $pdo;
            return;
        }
    } catch (\Throwable $t) {
        // Fallback to manual PDO creation below
    }
}

// 2. Resolve database credentials from Laravel config, env, or environment variables
$host = function_exists('config') ? config('database.connections.mysql.host') : null;
$db   = function_exists('config') ? config('database.connections.mysql.database') : null;
$user = function_exists('config') ? config('database.connections.mysql.username') : null;
$pass = function_exists('config') ? config('database.connections.mysql.password') : null;
$port = function_exists('config') ? config('database.connections.mysql.port') : null;

if (!$host) {
    $host = function_exists('env') ? env('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1') : (getenv('DB_HOST') ?: '127.0.0.1');
}
if (!$db) {
    $db = function_exists('env') ? env('DB_DATABASE', getenv('DB_DATABASE') ?: 'folu') : (getenv('DB_DATABASE') ?: 'folu');
}
if (!$user) {
    $user = function_exists('env') ? env('DB_USERNAME', getenv('DB_USERNAME') ?: 'root') : (getenv('DB_USERNAME') ?: 'root');
}
if ($pass === null) {
    $pass = function_exists('env') ? env('DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
}
if (!$port) {
    $port = function_exists('env') ? env('DB_PORT', getenv('DB_PORT') ?: '3306') : (getenv('DB_PORT') ?: '3306');
}

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $GLOBALS['pdo'] = $pdo;
} catch (PDOException $e) {
    // Ultimate fallback if inside Laravel framework
    if (class_exists('\Illuminate\Support\Facades\DB')) {
        try {
            $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
            $GLOBALS['pdo'] = $pdo;
            return;
        } catch (\Throwable $t) {}
    }
    die("Database Connection Failed: " . $e->getMessage());
}
