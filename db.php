<?php
/**
 * Inisialisasi Koneksi Database - Kelulusan MTsN 11 Majalengka
 * Membaca kredensial aman secara dinamis dari config.php.
 */

$configPath = __DIR__ . '/config.php';
$config = [];

// Baca file konfigurasi aman jika tersedia
if (file_exists($configPath)) {
    $config = require $configPath;
}

// Tentukan nilai default jika konfigurasi tidak tersedia/tidak lengkap
$dbType = $config['db_type'] ?? 'sqlite';
$dbHost = $config['db_host'] ?? 'localhost';
$dbName = $config['db_name'] ?? 'kelulusan_mtsn11';
$dbUser = $config['db_user'] ?? 'root';
$dbPass = $config['db_pass'] ?? '';
$sqliteFile = $config['sqlite_file'] ?? __DIR__ . '/database.sqlite';

try {
    if ($dbType === 'mysql') {
        $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } else {
        $dsn = "sqlite:" . $sqliteFile;
        $pdo = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        // Aktifkan Foreign Keys di SQLite
        $pdo->exec("PRAGMA foreign_keys = ON;");
    }
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
