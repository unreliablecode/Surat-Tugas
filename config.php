<?php
session_start();
header("Content-Security-Policy: upgrade-insecure-requests;");
// --- DATABASE CONFIGURATION ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Your DB username
define('DB_PASS', '250304');     // Your DB password
define('DB_NAME', 'surat_tugas_db');

// --- SITE CONFIGURATION ---
define('BASE_URL', 'https://localhost'); // Change this to your domain

// --- ESTABLISH DATABASE CONNECTION ---
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// --- HELPER FUNCTION TO CHECK IF USER IS LOGGED IN ---
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// --- HELPER FUNCTION TO CHECK IF USER IS ADMIN ---
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?>
