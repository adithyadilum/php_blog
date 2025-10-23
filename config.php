<?php

declare(strict_types=1);

if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', __DIR__);
}

/**
 * Load environment variables from .env file located at project root.
 */
if (!function_exists('loadAppEnv')) {
    // Load key/value pairs from the project .env file into an array cache.
    function loadAppEnv(string $basePath): array
    {
        static $cachedEnv = null;

        if ($cachedEnv !== null) {
            return $cachedEnv;
        }

        $envFile = $basePath . DIRECTORY_SEPARATOR . '.env';
        $cachedEnv = [];

        if (is_readable($envFile)) {
            $parsed = parse_ini_file($envFile, false, INI_SCANNER_TYPED);
            if (is_array($parsed)) {
                $cachedEnv = $parsed;
            }
        }

        return $cachedEnv;
    }
}

$env = loadAppEnv(APP_BASE_PATH);

$dbHost = $env['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$dbPort = (int) ($env['DB_PORT'] ?? getenv('DB_PORT') ?: 3306);
$dbName = $env['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: 'phpblogdb';
$dbUser = $env['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root';
$dbPass = $env['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
$dbCharset = $env['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
    $conn->set_charset($dbCharset);
} catch (mysqli_sql_exception $exception) {
    error_log('Database connection error: ' . $exception->getMessage());
    http_response_code(500);
    exit('Database connection failed.');
}
