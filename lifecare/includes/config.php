<?php
// includes/config.php - database connection and helpers used by every page
session_start();

// use Kenya time for every date and time in the system
date_default_timezone_set('Africa/Nairobi');

$dbHost = 'localhost';
$dbName = 'lifecare';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $ex) {
    die('Database connection failed. Check that MySQL is running in XAMPP.');
}

// short helper to print text safely on a page
function e($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}