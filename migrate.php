<?php
/**
 * Skrip Migrasi & Import Data JSON ke SQL
 * Jalankan file ini di browser untuk menginisialisasi database Anda.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Migrasi Database & Data Seeding</title>
    <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap' rel='stylesheet'>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; color: #333; padding: 30px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h1 { color: #007bff; border-bottom: 2px solid #eef2f5; padding-bottom: 10px; font-size: 1.8em; }
        .log-item { padding: 10px; margin: 8px 0; border-radius: 6px; font-size: 0.95em; border-left: 4px solid #ccc; background-color: #f8f9fa; }
        .success { border-left-color: #28a745; background-color: #e8f5e9; color: #1b5e20; }
        .warning { border-left-color: #ffc107; background-color: #fffde7; color: #f57f17; }
        .error { border-left-color: #dc3545; background-color: #ffebee; color: #c62828; }
        .btn { display: inline-block; background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; transition: background-color 0.2s; }
        .btn:hover { background-color: #0056b3; }
        .stats { margin-top: 20px; border-collapse: collapse; width: 100%; }
        .stats th, .stats td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .stats th { background-color: #f1f3f5; }
    </style>
</head>
<body>
<div class='container'>
    <h1>Migrasi Database & Penyemaian Data</h1>
";

$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
logMessage("Driver Database yang digunakan: <strong>" . strtoupper($driver) . "</strong>", "success");

try {
    // ========================================================
    // 1. BUAT TABEL-TABEL DATABASE
    // ========================================================
    
    logMessage("Memulai pembuatan tabel database...", "info");

    if ($driver === 'mysql') {
        // Skema MySQL
        $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $pdo->exec("CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nomor_peserta VARCHAR(50) NOT NULL UNIQUE,
            nisn VARCHAR(20) NOT NULL UNIQUE,
            nama VARCHAR(150) NOT NULL,
            jenis_kelamin VARCHAR(20) NOT NULL,
            tempat_lahir VARCHAR(100) NOT NULL,
            tanggal_lahir DATE NOT NULL,
            kelas VARCHAR(20) NOT NULL,
            status_kelulusan VARCHAR(30) NOT NULL,
            release_timestamp DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $pdo->exec("CREATE TABLE IF NOT EXISTS testimonials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            uid VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            likes INT DEFAULT 0,
            status VARCHAR(20) DEFAULT 'approved', -- testimoni lama otomatis disetujui
            date DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $pdo->exec("CREATE TABLE IF NOT EXISTS teacher_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            uid VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            likes INT DEFAULT 0,
            date DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            item_uid VARCHAR(50) NOT NULL,
            item_type VARCHAR(30) NOT NULL,
            author VARCHAR(150) NOT NULL,
            comment TEXT NOT NULL,
            date DATETIME NOT NULL,
            status VARCHAR(20) DEFAULT 'approved',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $pdo->exec("CREATE TABLE IF NOT EXISTS check_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nomor_peserta VARCHAR(50) NOT NULL,
            student_name VARCHAR(150) NOT NULL,
            result VARCHAR(50) NOT NULL,
            timestamp DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    } else {
        // Skema SQLite
        $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'admin',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nomor_peserta TEXT NOT NULL UNIQUE,
            nisn TEXT NOT NULL UNIQUE,
            nama TEXT NOT NULL,
            jenis_kelamin TEXT NOT NULL,
            tempat_lahir TEXT NOT NULL,
            tanggal_lahir TEXT NOT NULL,
            kelas TEXT NOT NULL,
            status_kelulusan TEXT NOT NULL,
            release_timestamp TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS testimonials (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            message TEXT NOT NULL,
            likes INTEGER DEFAULT 0,
            status TEXT DEFAULT 'approved',
            date TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS teacher_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            message TEXT NOT NULL,
            likes INTEGER DEFAULT 0,
            date TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            item_uid TEXT NOT NULL,
            item_type TEXT NOT NULL,
            author TEXT NOT NULL,
            comment TEXT NOT NULL,
            date TEXT NOT NULL,
            status TEXT DEFAULT 'approved',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS check_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nomor_peserta TEXT NOT NULL,
            student_name TEXT NOT NULL,
            result TEXT NOT NULL,
            timestamp TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");
    }

    // Tabel Settings (Sama untuk keduanya)
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT NOT NULL
    );");

    logMessage("Tabel-tabel database berhasil dibuat atau sudah ada.", "success");

    // ========================================================
    // 2. SEED AKUN ADMINISTRATOR AWAL
    // ========================================================
    logMessage("Memeriksa akun administrator...", "info");
    $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
    $adminExists = $stmt->fetchColumn() > 0;

    if (!$adminExists) {
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_BCRYPT);
        
        $insertAdmin = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
        $insertAdmin->execute([$username, $password, 'admin']);
        logMessage("Akun administrator awal berhasil dibuat! <strong>Username: admin | Password: admin123</strong>", "success");
    } else {
        logMessage("Akun administrator sudah terdaftar (lewati pembuatan akun).", "warning");
    }

    // ========================================================
    // 3. SEED SETTINGS GLOBAL (Target Waktu Pengumuman)
    // ========================================================
    logMessage("Memeriksa konfigurasi sistem...", "info");
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
    
    // Default Target Date: 2025-06-02 15:00:00
    $stmt->execute(['target_date']);
    if ($stmt->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")
            ->execute(['target_date', '2025-06-02 15:00:00']);
        logMessage("Waktu target kelulusan diatur ke: <strong>2025-06-02 15:00:00</strong>", "success");
    }

    // Default Maintenance Mode: false
    $stmt->execute(['maintenance_mode']);
    if ($stmt->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)")
            ->execute(['maintenance_mode', '0']);
    }

    // ========================================================
    // 4. MIGRASI DATA DARI FILE JSON LAMA
    // ========================================================
    logMessage("Memulai proses migrasi data dari file JSON...", "info");
    
    $stats = [
        'students' => 0,
        'testimonials' => 0,
        'teacher_messages' => 0,
        'comments' => 0,
        'check_history' => 0
    ];

    // --- A. DATA SISWA ---
    $studentFile = 'data_kelulusan_with_release_time.json';
    if (file_exists($studentFile)) {
        logMessage("Membaca $studentFile...", "info");
        $studentsData = json_decode(file_get_contents($studentFile), true);
        
        if (is_array($studentsData)) {
            $pdo->exec("DELETE FROM students"); // Kosongkan dulu untuk reload bersih
            
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO students (nomor_peserta, nisn, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, kelas, status_kelulusan, release_timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($driver === 'mysql') {
                $stmt = $pdo->prepare("INSERT IGNORE INTO students (nomor_peserta, nisn, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, kelas, status_kelulusan, release_timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            }
            
            foreach ($studentsData as $student) {
                $stmt->execute([
                    $student['nomor_peserta'],
                    $student['nisn'],
                    $student['nama'],
                    $student['jenis_kelamin'],
                    $student['tempat_lahir'],
                    $student['tanggal_lahir'],
                    $student['kelas'],
                    $student['status_kelulusan'],
                    $student['release_timestamp'] ?? '2025-06-02 15:00:00'
                ]);
                $stats['students']++;
            }
            logMessage("Selesai memigrasikan " . $stats['students'] . " data siswa.", "success");
        }
    } else {
        logMessage("File $studentFile tidak ditemukan. Lewati migrasi siswa.", "warning");
    }

    // --- B. DATA TESTIMONI & KOMENTAR TESTIMONI ---
    $testimonialFile = 'testimonials.json';
    if (file_exists($testimonialFile)) {
        logMessage("Membaca $testimonialFile...", "info");
        $testimonialsData = json_decode(file_get_contents($testimonialFile), true);
        
        if (is_array($testimonialsData)) {
            $pdo->exec("DELETE FROM testimonials");
            $pdo->exec("DELETE FROM comments WHERE item_type = 'testimonial'");
            
            $stmtT = $pdo->prepare("INSERT OR IGNORE INTO testimonials (uid, name, message, likes, status, date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtC = $pdo->prepare("INSERT INTO comments (item_uid, item_type, author, comment, date, status) VALUES (?, ?, ?, ?, ?, ?)");
            if ($driver === 'mysql') {
                $stmtT = $pdo->prepare("INSERT IGNORE INTO testimonials (uid, name, message, likes, status, date) VALUES (?, ?, ?, ?, ?, ?)");
            }
            
            foreach ($testimonialsData as $testi) {
                $uid = $testi['id'] ?? 'ts-' . uniqid();
                $stmtT->execute([
                    $uid,
                    $testi['name'],
                    $testi['message'],
                    $testi['likes'] ?? 0,
                    'approved', // testimoni lama otomatis disetujui
                    $testi['date'] ?? date('Y-m-d H:i:s')
                ]);
                $stats['testimonials']++;

                // Migrasikan komentar di dalam testimoni ini
                if (!empty($testi['comments']) && is_array($testi['comments'])) {
                    foreach ($testi['comments'] as $comm) {
                        $stmtC->execute([
                            $uid,
                            'testimonial',
                            $comm['author'] ?? 'Anonim',
                            $comm['comment'],
                            $comm['date'] ?? date('Y-m-d H:i:s'),
                            'approved'
                        ]);
                        $stats['comments']++;
                    }
                }
            }
            logMessage("Selesai memigrasikan " . $stats['testimonials'] . " testimoni dan " . $stats['comments'] . " komentar terkait.", "success");
        }
    } else {
        logMessage("File $testimonialFile tidak ditemukan. Lewati migrasi testimoni.", "warning");
    }

    // --- C. DATA PESAN GURU & KOMENTAR PESAN GURU ---
    $teacherFile = 'teacher_messages.json';
    if (file_exists($teacherFile)) {
        logMessage("Membaca $teacherFile...", "info");
        $teacherData = json_decode(file_get_contents($teacherFile), true);
        
        if (is_array($teacherData)) {
            $pdo->exec("DELETE FROM teacher_messages");
            $pdo->exec("DELETE FROM comments WHERE item_type = 'teacher_message'");
            
            $stmtT = $pdo->prepare("INSERT OR IGNORE INTO teacher_messages (uid, name, message, likes, date) VALUES (?, ?, ?, ?, ?)");
            $stmtC = $pdo->prepare("INSERT INTO comments (item_uid, item_type, author, comment, date, status) VALUES (?, ?, ?, ?, ?, ?)");
            if ($driver === 'mysql') {
                $stmtT = $pdo->prepare("INSERT IGNORE INTO teacher_messages (uid, name, message, likes, date) VALUES (?, ?, ?, ?, ?)");
            }
            
            foreach ($teacherData as $msg) {
                $uid = $msg['id'] ?? 'tm-' . uniqid();
                $stmtT->execute([
                    $uid,
                    $msg['name'],
                    $msg['message'],
                    $msg['likes'] ?? 0,
                    $msg['date'] ?? date('Y-m-d H:i:s')
                ]);
                $stats['teacher_messages']++;

                // Migrasikan komentar di dalam pesan guru ini
                if (!empty($msg['comments']) && is_array($msg['comments'])) {
                    foreach ($msg['comments'] as $comm) {
                        $stmtC->execute([
                            $uid,
                            'teacher_message',
                            $comm['author'] ?? 'Anonim',
                            $comm['comment'],
                            $comm['date'] ?? date('Y-m-d H:i:s'),
                            'approved'
                        ]);
                        $stats['comments']++;
                    }
                }
            }
            logMessage("Selesai memigrasikan " . $stats['teacher_messages'] . " pesan guru.", "success");
        }
    } else {
        logMessage("File $teacherFile tidak ditemukan. Lewati migrasi pesan guru.", "warning");
    }

    // --- D. DATA RIWAYAT PENGECEKAN ---
    $historyFile = 'check_history.json';
    if (file_exists($historyFile)) {
        logMessage("Membaca $historyFile...", "info");
        $historyData = json_decode(file_get_contents($historyFile), true);
        
        if (is_array($historyData)) {
            $pdo->exec("DELETE FROM check_history");
            
            $stmt = $pdo->prepare("INSERT INTO check_history (nomor_peserta, student_name, result, timestamp) VALUES (?, ?, ?, ?)");
            foreach ($historyData as $log) {
                $stmt->execute([
                    $log['nomor_peserta'],
                    $log['student_name'],
                    $log['result'],
                    $log['timestamp'] ?? date('Y-m-d H:i:s')
                ]);
                $stats['check_history']++;
            }
            logMessage("Selesai memigrasikan " . $stats['check_history'] . " log riwayat pengecekan.", "success");
        }
    } else {
        logMessage("File $historyFile tidak ditemukan. Lewati migrasi riwayat.", "warning");
    }

    // Tampilkan statistik migrasi
    echo "
    <h2>Statistik Data yang Dimigrasikan</h2>
    <table class='stats'>
        <thead>
            <tr><th>Tabel</th><th>Jumlah Record</th></tr>
        </thead>
        <tbody>
            <tr><td>Siswa (students)</td><td><strong>{$stats['students']}</strong></td></tr>
            <tr><td>Testimoni (testimonials)</td><td><strong>{$stats['testimonials']}</strong></td></tr>
            <tr><td>Pesan Guru (teacher_messages)</td><td><strong>{$stats['teacher_messages']}</strong></td></tr>
            <tr><td>Komentar (comments)</td><td><strong>{$stats['comments']}</strong></td></tr>
            <tr><td>Log Riwayat Pengecekan (check_history)</td><td><strong>{$stats['check_history']}</strong></td></tr>
        </tbody>
    </table>
    
    <div style='margin-top: 25px; text-align: center;'>
        <p style='color: #28a745; font-weight: bold;'>🎉 KESELURUHAN PROSES MIGRASI BERHASIL SELESAI!</p>
        <a href='index.php' class='btn' style='background-color: #28a745;'>Buka Halaman Utama</a>
    </div>
    ";

} catch (PDOException $e) {
    logMessage("Gagal mengeksekusi DDL/DML Migrasi: " . $e->getMessage(), "error");
}

echo "
</div>
</body>
</html>
";

/**
 * Fungsi Pembantu untuk Merender Log Logika Migrasi
 */
function logMessage($text, $type = 'info') {
    $class = '';
    if ($type === 'success') $class = 'success';
    if ($type === 'warning') $class = 'warning';
    if ($type === 'error') $class = 'error';
    
    echo "<div class='log-item {$class}'>{$text}</div>";
}
?>
