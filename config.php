<?php
$host = "localhost";
$dbname = "org_map";
$username = "root";
$password = "";

// PDO options for better performance, error handling, and security
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,             // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Return associative arrays by default
    PDO::ATTR_EMULATE_PREPARES => false,                     // Use native prepared statements
    PDO::ATTR_PERSISTENT => true                             // Persistent connection (optional, improves performance)
];

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        $options
    );
} catch (PDOException $e) {
    // Production: log error to file and show generic message
    // file_put_contents('pdo_errors.log', $e->getMessage(), FILE_APPEND);
    die("Database connection failed. Please contact the administrator.");
}
?>
