<?php
/**
 * Konfigurasi Database - Kelulusan MTsN 11 Majalengka
 * Mendukung MySQL dan SQLite secara dinamis.
 */

// Ubah ke 'mysql' untuk hosting produksi, atau tetap 'sqlite' untuk database portabel berbasis file.
define('DB_TYPE', 'sqlite'); 

// ==========================================
// 1. KONFIGURASI MYSQL (Produksi)
// ==========================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'kelulusan_mtsn11');
define('DB_USER', 'root');
define('DB_PASS', '');

// ==========================================
// 2. KONFIGURASI SQLITE (Portabel / Pengujian)
// ==========================================
define('SQLITE_FILE', __DIR__ . '/database.sqlite');

try {
    if (DB_TYPE === 'mysql') {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } else {
        $dsn = "sqlite:" . SQLITE_FILE;
        $pdo = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        // Aktifkan dukungan Foreign Keys untuk SQLite
        $pdo->exec("PRAGMA foreign_keys = ON;");
    }
} catch (PDOException $e) {
    // Tampilkan pesan kesalahan koneksi
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
