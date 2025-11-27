<?php
// Database connection settings
$host = "localhost";
$dbname = "eagle-flight-school";  // your database name
$username = "root";               // default XAMPP username
$password = "";                   // default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
