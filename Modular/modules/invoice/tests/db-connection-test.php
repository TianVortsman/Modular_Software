<?php
// Simple DB connection test script

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'ACC002';
$user = getenv('DB_USER') ?: 'Tian';
$password = getenv('DB_PASSWORD') ?: 'Modul@rdev@2024';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connection successful!\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
} 