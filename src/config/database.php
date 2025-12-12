<?php
// --- KONEKSI DATABASE DOCKER ---

// Mengambil konfigurasi dari Environment Variable (Docker)
// Jika tidak ada di env, gunakan nilai default (fallback)
$host = getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'dbserver';
$db   = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'appdb';
$user = getenv('DB_USER') !== false ? getenv('DB_USER') : 'appuser';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'secret123';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Jika koneksi gagal, tampilkan pesan error yang jelas
    die("Koneksi Database Gagal: " . $e->getMessage() . 
        "<br>Pastikan container 'dbserver' sudah berjalan.");
}
?>