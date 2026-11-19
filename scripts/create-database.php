<?php

// Called by setup.ps1 after Composer installation; never drops existing data.
require dirname(__DIR__).'/vendor/autoload.php';

$environment = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$environment->load();
$name = $_ENV['DB_DATABASE'] ?? 'hmvc_react';
if (! preg_match('/^[A-Za-z0-9_]+$/D', $name)) {
    throw new RuntimeException('DB_DATABASE hanya boleh berisi huruf, angka, dan underscore.');
}
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = (int) ($_ENV['DB_PORT'] ?? 3306);
$connection = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $_ENV['DB_USERNAME'] ?? 'root', $_ENV['DB_PASSWORD'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$connection->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "Database {$name} siap.\n";
