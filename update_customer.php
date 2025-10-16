<?php
// Database connection parameters from .env
$host = getenv('DB_HOST') ?: 'localhost';
$database = getenv('DB_DATABASE') ?: 'bima_connect';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Update customer status
$customerId = 130;
$newStatus = '0'; // Use string '0' to match the status column's data type

$stmt = $pdo->prepare("UPDATE customers SET status = :status WHERE id = :id");
$stmt->execute([
    'status' => $newStatus,
    'id' => $customerId
]);

echo "Customer status updated successfully\n";
?>
