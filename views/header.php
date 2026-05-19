<?php
// Prevent browser caching
header("Cache-Control: no-cache, no-store, must-revalidate, private");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?action=login");
    exit();
}

// Database connection
require_once __DIR__ . '/../config/Database.php';
$database = new Database();
$db = $database->connect();

if (!$db) {
    die("Database connection failed. Please check your configuration.");
}

// Define isAdmin function if not exists
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['position']) && strtoupper(trim($_SESSION['position'])) === 'ADMIN';
    }
}
?>