<?php
$host = 'localhost';
$db   = 'org_map';     // 🔁 Replace with your actual DB name
$user = 'root';                   // Default for XAMPP
$pass = '';                       // Default for XAMPP (empty password)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Return associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                   // Use real prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);  // ✅ THIS is what was missing or broken
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
