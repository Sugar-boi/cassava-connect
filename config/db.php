<?php
// config/db.php
$host = '127.0.0.1';
$db   = 'cassava_connect';
$user = 'root';
$pass = ''; // Laragon default has empty password; change if you set one
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('DB Connection failed: ' . $e->getMessage());
}
