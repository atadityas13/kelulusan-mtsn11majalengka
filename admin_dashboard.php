<?php
/**
 * Dasbor Utama Administrasi - MTsN 11 Majalengka
 * Menyediakan manajemen siswa (CRUD, Pencarian, Paginasi, Import),
 * moderasi testimoni siswa, pesan guru, riwayat pencarian, dan pengaturan sistem.
 */

session_start();
require_once 'db.php';

// Proteksi Sesi Login
if (!isset($_SESSION['logged_in_admin']) || $_SESSION['logged_in_admin'] !== true) {
    header('Location: admin_login.php');
    exit;
}

$adminUsername = $_SESSION['admin_username'];
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

// Pesan Umpan Balik Tindakan
$successMsg = '';
$errorMsg = '';

// Tentukan Tab Aktif
$activeTab = $_GET['tab'] ?? 'overview';

// ==========================================
// 1. TANGANIN OPERASI POST (TINDAKAN ADMIN)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- A. MANAJEMEN SISWA ---
    if ($action === 'add_student') {
        $nopes = trim($_POST['nomor_peserta'] ?? '');
        $nisn = trim($_POST['nisn'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $jk = $_POST['jenis_kelamin'] ?? '';
        $tempat = trim($_POST['tempat_lahir'] ?? '');
        $tgl = $_POST['tanggal_lahir'] ?? '';
        $kelas = trim($_POST['kelas'] ?? '');
        $status = $_POST['status_kelulusan'] ?? '';
        $release = trim($_POST['release_timestamp'] ?? '');

        if (empty($nopes) || empty($nisn) || empty($nama) || empty($tgl) || empty($kelas)) {
            $errorMsg = 'Seluruh field wajib diisi.';
        } else {
            try {
                $check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE nomor_peserta = ? OR nisn = ?");
                $check->execute([$nopes, $nisn]);
                if ($check->fetchColumn() > 0) {
                    $errorMsg = 'Gagal! Nomor Peserta atau NISN sudah terdaftar.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO students (nomor_peserta, nisn, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, kelas, status_kelulusan, release_timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nopes, $nisn, $nama, $jk, $tempat, $tgl, $kelas, $status, $release ?: null]);
                    $successMsg = 'Siswa baru berhasil ditambahkan!';
                }
            } catch (PDOException $e) {
                $errorMsg = 'Gagal menyimpan data: ' . $e->getMessage();
            }
        }
    }

    elseif ($action === 'edit_student') {
        $id = (int)($_POST['id'] ?? 0);
        $nopes = trim($_POST['nomor_peserta'] ?? '');
        $nisn = trim($_POST['nisn'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $jk = $_POST['jenis_kelamin'] ?? '';
        $tempat = trim($_POST['tempat_lahir'] ?? '');
        $tgl = $_POST['tanggal_lahir'] ?? '';
        $kelas = trim($_POST['kelas'] ?? '');
        $status = $_POST['status_kelulusan'] ?? '';
        $release = trim($_POST['release_timestamp'] ?? '');

        if ($id <= 0 || empty($nopes) || empty($nisn) || empty($nama) || empty($tgl) || empty($kelas)) {
            $errorMsg = 'Gagal memperbarui! Data tidak lengkap.';
        } else {
            try {
                $check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE (nomor_peserta = ? OR nisn = ?) AND id != ?");
                $check->execute([$nopes, $nisn, $id]);
                if ($check->fetchColumn() > 0) {
                    $errorMsg = 'Gagal memperbarui! Nomor Peserta atau NISN sudah digunakan siswa lain.';
                } else {
                    $stmt = $pdo->prepare("UPDATE students SET nomor_peserta = ?, nisn = ?, nama = ?, jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?, kelas = ?, status_kelulusan = ?, release_timestamp = ? WHERE id = ?");
                    $stmt->execute([$nopes, $nisn, $nama, $jk, $tempat, $tgl, $kelas, $status, $release ?: null, $id]);
                    $successMsg = 'Data siswa berhasil diperbarui!';
                }
            } catch (PDOException $e) {
                $errorMsg = 'Gagal memperbarui data: ' . $e->getMessage();
            }
        }
    }

    elseif ($action === 'delete_student') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
                $stmt->execute([$id]);
                $successMsg = 'Siswa berhasil dihapus dari database.';
            } catch (PDOException $e) {
                $errorMsg = 'Gagal menghapus siswa: ' . $e->getMessage();
            }
        }
    }

    elseif ($action === 'import_students') {
        if (isset($_FILES['json_file']) && $_FILES['json_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['json_file']['tmp_name'];
            $fileContent = file_get_contents($fileTmp);
            $studentsData = json_decode($fileContent, true);

            if (is_array($studentsData)) {
                $inserted = 0;
                $skipped = 0;
                
                try {
                    $pdo->beginTransaction();
                    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM students WHERE nomor_peserta = ? OR nisn = ?");
                    $stmtInsert = $pdo->prepare("INSERT INTO students (nomor_peserta, nisn, nama, jenis_kelamin, tempat_lahir, tanggal_lahir, kelas, status_kelulusan, release_timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    foreach ($studentsData as $student) {
                        $nopes = trim($student['nomor_peserta'] ?? '');
                        $nisn = trim($student['nisn'] ?? '');
                        
                        if (empty($nopes) || empty($nisn)) {
                            $skipped++;
                            continue;
                        }

                        $stmtCheck->execute([$nopes, $nisn]);
                        if ($stmtCheck->fetchColumn() == 0) {
                            $stmtInsert->execute([
                                $nopes,
                                $nisn,
                                trim($student['nama'] ?? 'Tanpa Nama'),
                                $student['jenis_kelamin'] ?? 'Laki-laki',
                                trim($student['tempat_lahir'] ?? 'Majalengka'),
                                $student['tanggal_lahir'] ?? date('Y-m-d'),
                                trim($student['kelas'] ?? 'IX'),
                                $student['status_kelulusan'] ?? 'Lulus',
                                $student['release_timestamp'] ?? '2025-06-02 15:00:00'
                            ]);
                            $inserted++;
                        } else {
                            $skipped++;
                        }
                    }
                    $pdo->commit();
                    $successMsg = "Proses import selesai. Berhasil memasukkan: {$inserted} siswa. Dilewati (sudah terdaftar/tidak lengkap): {$skipped} siswa.";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $errorMsg = 'Gagal melakukan import massal: ' . $e->getMessage();
                }
            } else {
                $errorMsg = 'Format file JSON tidak valid. Pastikan array dari objek siswa.';
            }
        } else {
            $errorMsg = 'Silakan pilih file JSON data siswa terlebih dahulu.';
        }
    }

    // --- B. MODERASI TESTIMONI ---
    elseif ($action === 'approve_testimonial') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE testimonials SET status = 'approved' WHERE id = ?");
                $stmt->execute([$id]);
                $successMsg = 'Testimoni disetujui dan kini tampil ke publik!';
            } catch (PDOException $e) {
                $errorMsg = 'Gagal menyetujui: ' . $e->getMessage();
            }
        }
    }

    elseif ($action === 'reject_testimonial') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE testimonials SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$id]);
                $successMsg = 'Testimoni ditolak.';
            } catch (PDOException $e) {
                $errorMsg = 'Gagal memperbarui status: ' . $e->getMessage();
            }
        }
    }

    elseif ($action === 'delete_testimonial') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                // Hapus juga komentar-komentar yang berkaitan dengan testimoni ini
                $stmtGetUid = $pdo->prepare("SELECT uid FROM testimonials WHERE id = ?");
                $stmtGetUid->execute([$id]);
                $uid = $stmtGetUid->fetchColumn();

                if ($uid) {
                    $pdo->beginTransaction();
                    $pdo->prepare("DELETE FROM comments WHERE item_uid = ? AND item_type = 'testimonial'")->execute([$uid]);
                    $pdo->prepare("DELETE FROM testimonials WHERE id = ?")->execute([$id]);
                    $pdo->commit();
                    $successMsg = 'Testimoni beserta seluruh komentar terkait berhasil dihapus.';
                }
            } catch (PDOException $e) {
                $errorMsg = 'Gagal menghapus testimoni: ' . $e->getMessage();
            }
        }
    }

    // --- C. MANAJEMEN PESAN GURU ---
    elseif ($action === 'add_teacher_message') {
        $name = trim($_POST['name'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($message)) {
            $errorMsg = 'Nama Guru dan isi pesan wajib diisi.';
        } else {
            try {
                $uid = 'tm-' . uniqid();
                $stmt = $pdo->prepare("INSERT INTO teacher_messages (uid, name, message, date) VALUES (?, ?, ?, ?)");
                $stmt->execute([$uid, $name, $message, date('Y-m-d H:i:s')]);
                $successMsg = 'Pesan dari guru berhasil diterbitkan ke halaman depan!';
            } catch (PDOException $e) {
                $errorMsg = 'Gagal menerbitkan pesan: ' . $e->getMessage();
            }
        }
    }

    elseif ($action === 'delete_teacher_message') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmtGetUid = $pdo->prepare("SELECT uid FROM teacher_messages WHERE id = ?");
                $stmtGetUid->execute([$id]);
                $uid = $stmtGetUid->fetchColumn();

                if ($uid) {
                    $pdo->beginTransaction();
                    $pdo->prepare("DELETE FROM comments WHERE item_uid = ? AND item_type = 'teacher_message'")->execute([$uid]);
                    $pdo->prepare("DELETE FROM teacher_messages WHERE id = ?")->execute([$id]);
                    $pdo->commit();
                    $successMsg = 'Pesan guru beserta seluruh komentar terkait berhasil dihapus.';
                }
            } catch (PDOException $e) {
                $errorMsg = 'Gagal menghapus pesan guru: ' . $e->getMessage();
            }
        }
    }

    // --- D. RIWAYAT PENGECEKAN ---
    elseif ($action === 'clear_history') {
        try {
            $pdo->exec("DELETE FROM check_history");
            $successMsg = 'Seluruh riwayat pengecekan kelulusan berhasil dibersihkan.';
        } catch (PDOException $e) {
            $errorMsg = 'Gagal membersihkan riwayat: ' . $e->getMessage();
        }
    }

    // --- E. PENGATURAN SISTEM ---
    elseif ($action === 'save_settings') {
        $targetDate = trim($_POST['target_date'] ?? '');
        $maintenance = isset($_POST['maintenance_mode']) ? '1' : '0';

        if (empty($targetDate)) {
            $errorMsg = 'Tanggal target tidak boleh kosong.';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Simpan target date
                $pdo->prepare("DELETE FROM settings WHERE setting_key = 'target_date'")->execute();
                $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('target_date', ?)")->execute([$targetDate]);

                // Simpan maintenance mode
                $pdo->prepare("DELETE FROM settings WHERE setting_key = 'maintenance_mode'")->execute();
                $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('maintenance_mode', ?)")->execute([$maintenance]);
                
                $pdo->commit();
                $successMsg = 'Pengaturan sistem berhasil disimpan!';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $errorMsg = 'Gagal menyimpan pengaturan: ' . $e->getMessage();
            }
        }
    }

    elseif ($action === 'change_password') {
        $current = trim($_POST['current_password'] ?? '');
        $new = trim($_POST['new_password'] ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');

        if (empty($current) || empty($new) || empty($confirm)) {
            $errorMsg = 'Seluruh kolom kata sandi wajib diisi.';
        } elseif ($new !== $confirm) {
            $errorMsg = 'Konfirmasi kata sandi baru tidak cocok.';
        } elseif (strlen($new) < 6) {
            $errorMsg = 'Kata sandi baru minimal harus 6 karakter.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
                $hashed = $stmt->fetchColumn();

                if (password_verify($current, $hashed)) {
                    $newHashed = password_hash($new, PASSWORD_BCRYPT);
                    $update = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                    $update->execute([$newHashed, $_SESSION['admin_id']]);
                    $successMsg = 'Kata sandi administrator berhasil diubah!';
                } else {
                    $errorMsg = 'Kata sandi saat ini yang Anda masukkan salah.';
                }
            } catch (PDOException $e) {
                $errorMsg = 'Gagal mengubah kata sandi: ' . $e->getMessage();
            }
        }
    }
}

// ==========================================
// 2. QUERY RINGKASAN DATA (STATS CARDS)
// ==========================================
$statTotalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$statTotalLulus = $pdo->query("SELECT COUNT(*) FROM students WHERE status_kelulusan = 'Lulus'")->fetchColumn();
$statTotalTidakLulus = $pdo->query("SELECT COUNT(*) FROM students WHERE status_kelulusan = 'Tidak Lulus'")->fetchColumn();
$statTotalPendingTesti = $pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'pending'")->fetchColumn();
$statTotalChecks = $pdo->query("SELECT COUNT(*) FROM check_history")->fetchColumn();

// Ambil Pengaturan Aktif
$currentSettings = [];
$stmtSet = $pdo->query("SELECT * FROM settings");
while ($row = $stmtSet->fetch()) {
    $currentSettings[$row['setting_key']] = $row['setting_value'];
}
$settingTargetDate = $currentSettings['target_date'] ?? '2025-06-02 15:00:00';
$settingMaintenance = $currentSettings['maintenance_mode'] ?? '0';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Kontrol Admin - MTsN 11 Majalengka</title>
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #4f46e5;
            --primary-light: #e0e7ff;
            --secondary: #0f172a;
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--secondary);
            color: #fff;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0,0,0,0.05);
            transition: var(--transition);
        }

        .sidebar-brand {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-brand img {
            width: 42px;
            height: auto;
        }

        .sidebar-brand span {
            font-weight: 700;
            font-size: 1.05em;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-menu-item a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 24px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.92em;
            font-weight: 500;
            transition: var(--transition);
            border-left: 4px solid transparent;
        }

        .sidebar-menu-item a:hover {
            background-color: rgba(255,255,255,0.04);
            color: #fff;
        }

        .sidebar-menu-item.active a {
            background-color: rgba(255,255,255,0.06);
            color: #fff;
            border-left-color: var(--primary);
        }

        .sidebar-menu-item a i {
            font-size: 1.15em;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 20px 24px;
            border-top: 1px solid rgba(255,255,255,0.08);
            font-size: 0.85em;
            color: rgba(255,255,255,0.5);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-footer a {
            color: var(--danger);
            font-size: 1.2em;
            transition: var(--transition);
        }

        .sidebar-footer a:hover {
            transform: scale(1.15);
        }

        /* --- MAIN CONTENT AREA --- */
        .main-panel {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 30px;
            box-sizing: border-box;
            max-width: calc(100% - var(--sidebar-width));
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 1.7em;
            margin: 0;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.5px;
        }

        .user-widget {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            padding: 8px 16px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            font-size: 0.9em;
            font-weight: 500;
        }

        .user-widget i {
            color: var(--primary);
            font-size: 1.1em;
        }

        /* Alert notifications */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            font-size: 0.92em;
            font-weight: 500;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeIn 0.4s ease;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--success);
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        /* --- STATS CARDS --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }

        .stat-info h3 {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.88em;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info p {
            margin: 8px 0 0 0;
            font-size: 1.8em;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4em;
        }

        .stat-icon-blue { background-color: #eff6ff; color: #3b82f6; }
        .stat-icon-green { background-color: #ecfdf5; color: #10b981; }
        .stat-icon-orange { background-color: #fffbeb; color: #f59e0b; }
        .stat-icon-red { background-color: #fef2f2; color: #ef4444; }

        /* --- MAIN CARDS & TAB PANELS --- */
        .card {
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-header h2 {
            margin: 0;
            font-size: 1.15em;
            font-weight: 600;
            color: var(--text-dark);
        }

        .card-body {
            padding: 24px;
        }

        /* --- DATA TABLES --- */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
            text-align: left;
        }

        table.data-table th, table.data-table td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
        }

        table.data-table th {
            background-color: #f8fafc;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.data-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
        }

        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info { background-color: #e0f2fe; color: #075985; }

        /* Actions group */
        .actions-cell {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-icon:hover {
            color: var(--primary);
            border-color: var(--primary);
            background-color: var(--primary-light);
        }

        .btn-icon-danger:hover {
            color: var(--danger);
            border-color: var(--danger);
            background-color: #fee2e2;
        }

        /* --- BUTTONS --- */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.9em;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .btn-primary { background-color: var(--primary); color: #fff; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2); }
        .btn-primary:hover { background-color: #4338ca; transform: translateY(-1px); }
        
        .btn-secondary { background-color: #fff; border-color: var(--border); color: var(--text-dark); }
        .btn-secondary:hover { background-color: #f8fafc; border-color: var(--text-muted); }

        .btn-danger { background-color: var(--danger); color: #fff; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2); }
        .btn-danger:hover { background-color: #dc2626; transform: translateY(-1px); }

        /* --- FORM CONTROLS --- */
        .search-form {
            display: flex;
            gap: 10px;
            flex-grow: 1;
            max-width: 400px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.9em;
            box-sizing: border-box;
            background-color: #fff;
            color: var(--text-dark);
            outline: none;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group-db {
            margin-bottom: 18px;
            text-align: left;
        }

        .form-group-db label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.88em;
            font-weight: 500;
            color: var(--text-dark);
        }

        /* --- PAGINATION --- */
        .pagination-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .pagination-info {
            font-size: 0.85em;
            color: var(--text-muted);
        }

        .pagination-links {
            display: flex;
            gap: 5px;
        }

        .pagination-link {
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background-color: #fff;
            color: var(--text-dark);
            text-decoration: none;
            font-size: 0.85em;
            font-weight: 500;
            transition: var(--transition);
        }

        .pagination-link:hover {
            border-color: var(--primary);
            color: var(--primary);
            background-color: var(--primary-light);
        }

        .pagination-link.active {
            background-color: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .pagination-link.disabled {
            color: #bbb;
            border-color: var(--border);
            cursor: not-allowed;
            background-color: #f1f5f9;
        }

        /* --- MODAL --- */
        .modal {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.3s ease;
            box-sizing: border-box;
        }

        .modal-content {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.15em;
            font-weight: 600;
            color: var(--text-dark);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.4em;
            color: var(--text-muted);
            cursor: pointer;
            transition: var(--transition);
        }

        .modal-close:hover {
            color: var(--danger);
        }

        .modal-body {
            padding: 24px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background-color: #f8fafc;
        }

        /* --- KEYFRAMES --- */
        @keyframes fadeIn {
            from { opacity: 0; } to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-15px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* --- RESPONSIVITAS --- */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
            }
            .sidebar-brand span, .sidebar-footer span, .sidebar-menu-item span {
                display: none;
            }
            .sidebar-brand {
                padding: 15px;
                justify-content: center;
            }
            .sidebar-menu-item a {
                padding: 15px;
                justify-content: center;
                border-left-width: 3px;
            }
            .sidebar-footer {
                padding: 15px;
                justify-content: center;
            }
            .main-panel {
                margin-left: 70px;
                max-width: calc(100% - 70px);
                padding: 20px;
            }
        }

        @media (max-width: 600px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .user-widget {
                width: 100%;
                box-sizing: border-box;
                justify-content: center;
            }
            .card-header {
                flex-direction: column;
                align-items: stretch;
            }
            .search-form {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- SIDEBAR KIRI -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="assets/mtsn11majalengka-logo.png" alt="Logo">
            <span>Admin Control<br><small style="font-size:0.75em; font-weight:normal; opacity:0.7;">MTsN 11 Majalengka</small></span>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item <?php echo $activeTab === 'overview' ? 'active' : ''; ?>">
                <a href="?tab=overview"><i class="fa-solid fa-chart-line"></i> <span>Ringkasan</span></a>
            </li>
            <li class="sidebar-menu-item <?php echo $activeTab === 'students' ? 'active' : ''; ?>">
                <a href="?tab=students"><i class="fa-solid fa-graduation-cap"></i> <span>Data Siswa</span></a>
            </li>
            <li class="sidebar-menu-item <?php echo $activeTab === 'testimonials' ? 'active' : ''; ?>">
                <a href="?tab=testimonials"><i class="fa-solid fa-comments"></i> <span>Testimoni Siswa</span></a>
            </li>
            <li class="sidebar-menu-item <?php echo $activeTab === 'teacher_messages' ? 'active' : ''; ?>">
                <a href="?tab=teacher_messages"><i class="fa-solid fa-comment-medical"></i> <span>Pesan Guru</span></a>
            </li>
            <li class="sidebar-menu-item <?php echo $activeTab === 'history' ? 'active' : ''; ?>">
                <a href="?tab=history"><i class="fa-solid fa-clock-rotate-left"></i> <span>Log Riwayat</span></a>
            </li>
            <li class="sidebar-menu-item <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
                <a href="?tab=settings"><i class="fa-solid fa-sliders"></i> <span>Pengaturan</span></a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <span>Versi 2.0</span>
            <a href="logout.php" title="Keluar" onclick="return confirm('Apakah Anda yakin ingin keluar?')"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </aside>

    <!-- PANEL UTAMA KANAN -->
    <main class="main-panel">
        <header class="header">
            <div>
                <h1>
                    <?php
                    if ($activeTab === 'overview') echo 'Ringkasan & Statistik';
                    elseif ($activeTab === 'students') echo 'Manajemen Data Siswa';
                    elseif ($activeTab === 'testimonials') echo 'Moderasi Kesan & Pesan';
                    elseif ($activeTab === 'teacher_messages') echo 'Kelola Pesan Guru';
                    elseif ($activeTab === 'history') echo 'Log Riwayat Pengecekan';
                    elseif ($activeTab === 'settings') echo 'Pengaturan Sistem';
                    ?>
                </h1>
            </div>
            <div class="user-widget">
                <i class="fa-solid fa-user-shield"></i>
                <span>Halo, <?php echo htmlspecialchars($adminUsername); ?></span>
            </div>
        </header>

        <!-- Pesan Alert -->
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span><?php echo htmlspecialchars($successMsg); ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo htmlspecialchars($errorMsg); ?></span>
            </div>
        <?php endif; ?>

        <!-- ========================================================
             TAB 1: OVERVIEW (RINGKASAN)
             ======================================================== -->
        <?php if ($activeTab === 'overview'): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Siswa</h3>
                        <p><?php echo $statTotalStudents; ?></p>
                    </div>
                    <div class="stat-icon stat-icon-blue">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Siswa Lulus</h3>
                        <p><?php echo $statTotalLulus; ?></p>
                    </div>
                    <div class="stat-icon stat-icon-green">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Menunggu Moderasi</h3>
                        <p><?php echo $statTotalPendingTesti; ?></p>
                    </div>
                    <div class="stat-icon stat-icon-orange">
                        <i class="fa-solid fa-comment-slash"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Pengecekan</h3>
                        <p><?php echo $statTotalChecks; ?></p>
                    </div>
                    <div class="stat-icon stat-icon-red">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                </div>
            </div>

            <!-- Tiga Riwayat Terakhir -->
            <div class="card">
                <div class="card-header">
                    <h2>Pengecekan Kelulusan Terakhir (Real-time)</h2>
                    <a href="?tab=history" class="btn btn-secondary btn-sm" style="padding: 6px 12px; font-size: 0.8em;">Lihat Seluruh Log</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>No. Peserta</th>
                                <th>Nama Siswa</th>
                                <th>Hasil</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM check_history ORDER BY id DESC LIMIT 5");
                            $recentChecks = $stmt->fetchAll();
                            if (!empty($recentChecks)):
                                foreach ($recentChecks as $log):
                            ?>
                                <tr>
                                    <td><?php echo date('d M Y H:i:s', strtotime($log['timestamp'])); ?></td>
                                    <td><?php echo htmlspecialchars($log['nomor_peserta']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($log['student_name']); ?></strong></td>
                                    <td>
                                        <span class="badge <?php echo $log['result'] === 'Lulus' ? 'badge-success' : ($log['result'] === 'Tidak Lulus' ? 'badge-danger' : 'badge-warning'); ?>">
                                            <?php echo htmlspecialchars($log['result']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php 
                                endforeach;
                            else:
                            ?>
                                <tr><td colspan="4" style="text-align: center; color: #888;">Belum ada log pengecekan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card Petunjuk -->
            <div class="card">
                <div class="card-header">
                    <h2>Panduan Administrasi Singkat</h2>
                </div>
                <div class="card-body" style="font-size: 0.95em; line-height: 1.7;">
                    <p>Selamat datang di Panel Administrasi Pengumuman Kelulusan MTsN 11 Majalengka. Berikut adalah tindakan penting yang dapat Anda lakukan:</p>
                    <ul>
                        <li><strong>Input Data Siswa Massal</strong>: Buka menu <em>Data Siswa</em>, klik tombol <em>Import Siswa</em> dan unggah file JSON berisi kumpulan data siswa kelulusan.</li>
                        <li><strong>Atur Tanggal Rilis</strong>: Masuk ke menu <em>Pengaturan</em> untuk mengonfigurasi jam buka pengumuman global. Jika pengumuman belum dibuka, siswa akan diarahkan otomatis ke halaman hitung mundur.</li>
                        <li><strong>Setujui Kesan & Pesan</strong>: Siswa yang lulus dapat mengirimkan kesan dan pesan. Agar pesan tersebut tampil ke publik di halaman depan, Anda harus memoderasinya terlebih dahulu di tab <em>Testimoni Siswa</em>.</li>
                    </ul>
                </div>
            </div>

        <!-- ========================================================
             TAB 2: STUDENTS (DATA SISWA)
             ======================================================== -->
        <?php elseif ($activeTab === 'students'): ?>
            <?php
            // Setup searching & pagination
            $search = trim($_GET['search'] ?? '');
            
            // Tentukan Halaman Terkini
            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            // Membangun Kueri SQL Pencarian
            if (!empty($search)) {
                $countQuery = $pdo->prepare("SELECT COUNT(*) FROM students WHERE nama LIKE ? OR nisn LIKE ? OR nomor_peserta LIKE ? OR kelas LIKE ?");
                $countQuery->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
                $totalRecords = $countQuery->fetchColumn();

                $stmt = $pdo->prepare("SELECT * FROM students WHERE nama LIKE ? OR nisn LIKE ? OR nomor_peserta LIKE ? OR kelas LIKE ? ORDER BY nama ASC LIMIT ? OFFSET ?");
                // Ikat parameter limit & offset secara manual karena PDO emulasi/tipe data
                $stmt->bindValue(1, "%$search%", PDO::PARAM_STR);
                $stmt->bindValue(2, "%$search%", PDO::PARAM_STR);
                $stmt->bindValue(3, "%$search%", PDO::PARAM_STR);
                $stmt->bindValue(4, "%$search%", PDO::PARAM_STR);
                $stmt->bindValue(5, $limit, PDO::PARAM_INT);
                $stmt->bindValue(6, $offset, PDO::PARAM_INT);
                $stmt->execute();
                $studentsList = $stmt->fetchAll();
            } else {
                $totalRecords = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();

                $stmt = $pdo->prepare("SELECT * FROM students ORDER BY nama ASC LIMIT ? OFFSET ?");
                $stmt->bindValue(1, $limit, PDO::PARAM_INT);
                $stmt->bindValue(2, $offset, PDO::PARAM_INT);
                $stmt->execute();
                $studentsList = $stmt->fetchAll();
            }

            $totalPages = ceil($totalRecords / $limit);
            ?>

            <div class="card">
                <div class="card-header">
                    <form action="admin_dashboard.php" method="GET" class="search-form">
                        <input type="hidden" name="tab" value="students">
                        <input type="text" name="search" class="form-control" placeholder="Cari Nama, NISN, Kelas..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </form>
                    <div style="display:flex; gap:10px;">
                        <button type="button" class="btn btn-secondary" onclick="openModal('importModal')"><i class="fa-solid fa-file-import"></i> Import Siswa</button>
                        <button type="button" class="btn btn-primary" onclick="openAddStudentModal()"><i class="fa-solid fa-plus"></i> Tambah Siswa</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>No. Peserta</th>
                                <th>NISN</th>
                                <th>Nama Lengkap</th>
                                <th>Kelas</th>
                                <th>Hasil</th>
                                <th>Waktu Rilis</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($studentsList)):
                                $no = $offset + 1;
                                foreach ($studentsList as $student):
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($student['nomor_peserta']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($student['nisn']); ?></td>
                                    <td><?php echo htmlspecialchars($student['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($student['kelas']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $student['status_kelulusan'] === 'Lulus' ? 'badge-success' : ($student['status_kelulusan'] === 'Tidak Lulus' ? 'badge-danger' : 'badge-warning'); ?>">
                                            <?php echo htmlspecialchars($student['status_kelulusan']); ?>
                                        </span>
                                    </td>
                                    <td><small><?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($student['release_timestamp']))); ?></small></td>
                                    <td>
                                        <div class="actions-cell">
                                            <button class="btn-icon" title="Edit" onclick='openEditStudentModal(<?php echo json_encode($student); ?>)'><i class="fa-solid fa-pen-to-square"></i></button>
                                            <form action="admin_dashboard.php?tab=students&page=<?php echo $page; ?>&search=<?php echo urlencode($search); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus siswa ini?')" style="margin:0;">
                                                <input type="hidden" name="action" value="delete_student">
                                                <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                                <button type="submit" class="btn-icon btn-icon-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endforeach;
                            else:
                            ?>
                                <tr><td colspan="8" style="text-align: center; color: #888;">Data siswa tidak ditemukan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginasi -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-body" style="padding: 0 24px 24px 24px;">
                        <div class="pagination-container">
                            <span class="pagination-info">Menampilkan data <?php echo $offset + 1; ?> - <?php echo min($offset + $limit, $totalRecords); ?> dari total <?php echo $totalRecords; ?> data siswa</span>
                            <div class="pagination-links">
                                <?php if ($page > 1): ?>
                                    <a href="?tab=students&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="pagination-link">&laquo; Prev</a>
                                <?php else: ?>
                                    <span class="pagination-link disabled">&laquo; Prev</span>
                                <?php endif; ?>

                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <a href="?tab=students&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <a href="?tab=students&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="pagination-link">Next &raquo;</a>
                                <?php else: ?>
                                    <span class="pagination-link disabled">Next &raquo;</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- MODAL IMPORT DATA SISWA -->
            <div id="importModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <form action="admin_dashboard.php?tab=students" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="import_students">
                        <div class="modal-header">
                            <h3>Import Data Siswa Masal (JSON)</h3>
                            <button type="button" class="modal-close" onclick="closeModal('importModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p style="font-size:0.9em; line-height: 1.5; color: var(--text-muted); margin-bottom: 20px;">
                                Silakan unggah file JSON berisi data kelulusan siswa. Sistem akan otomatis memasukkan data baru dan melewati data yang sudah ada (berdasarkan Nomor Peserta / NISN).
                            </p>
                            <div class="form-group-db">
                                <label for="json_file">Pilih File JSON Data Siswa</label>
                                <input type="file" id="json_file" name="json_file" class="form-control" accept=".json" required>
                            </div>
                            <div style="font-size:0.82em; color: var(--text-muted); background: #f8fafc; padding: 12px; border-radius: 8px; border: 1.5px solid var(--border);">
                                <strong>Format JSON yang diterima:</strong><br>
                                <code>[ { "nomor_peserta": "...", "nisn": "...", "nama": "...", "jenis_kelamin": "...", "tempat_lahir": "...", "tanggal_lahir": "YYYY-MM-DD", "kelas": "...", "status_kelulusan": "Lulus/Tidak Lulus", "release_timestamp": "YYYY-MM-DD HH:MM:SS" } ]</code>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('importModal')">Batal</button>
                            <button type="submit" class="btn btn-primary">Mulai Import</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL CRUD SISWA (ADD / EDIT) -->
            <div id="studentFormModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <form action="admin_dashboard.php?tab=students&page=<?php echo $page; ?>&search=<?php echo urlencode($search); ?>" method="POST" id="studentForm">
                        <input type="hidden" name="action" id="studentFormAction" value="add_student">
                        <input type="hidden" name="id" id="studentId" value="">
                        
                        <div class="modal-header">
                            <h3 id="studentModalTitle">Tambah Data Siswa</h3>
                            <button type="button" class="modal-close" onclick="closeModal('studentFormModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group-db">
                                    <label for="nomor_peserta">Nomor Peserta Ujian</label>
                                    <input type="text" id="nomor_peserta" name="nomor_peserta" class="form-control" placeholder="Contoh: 25-10-10-2-0089-0001" required>
                                </div>
                                <div class="form-group-db">
                                    <label for="nisn">NISN Siswa</label>
                                    <input type="text" id="nisn" name="nisn" class="form-control" placeholder="Contoh: 0098765432" required>
                                </div>
                            </div>

                            <div class="form-group-db">
                                <label for="nama">Nama Lengkap Siswa</label>
                                <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama siswa" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group-db">
                                    <label for="jenis_kelamin">Jenis Kelamin</label>
                                    <select id="jenis_kelamin" name="jenis_kelamin" class="form-control">
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                                <div class="form-group-db">
                                    <label for="kelas">Kelas Siswa</label>
                                    <input type="text" id="kelas" name="kelas" class="form-control" placeholder="Contoh: IX.1" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-db">
                                    <label for="tempat_lahir">Tempat Lahir</label>
                                    <input type="text" id="tempat_lahir" name="tempat_lahir" class="form-control" placeholder="Contoh: Majalengka">
                                </div>
                                <div class="form-group-db">
                                    <label for="tanggal_lahir">Tanggal Lahir</label>
                                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group-db">
                                    <label for="status_kelulusan">Status Kelulusan</label>
                                    <select id="status_kelulusan" name="status_kelulusan" class="form-control">
                                        <option value="Lulus">Lulus</option>
                                        <option value="Tidak Lulus">Tidak Lulus</option>
                                        <option value="Ditangguhkan">Ditangguhkan</option>
                                    </select>
                                </div>
                                <div class="form-group-db">
                                    <label for="release_timestamp">Waktu Rilis Batch</label>
                                    <input type="datetime-local" id="release_timestamp" name="release_timestamp" class="form-control" step="1">
                                    <small style="color:var(--text-muted); font-size:0.78em;">Kosongkan jika ingin mengikuti waktu target default.</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('studentFormModal')">Batal</button>
                            <button type="submit" class="btn btn-primary" id="studentFormSubmitBtn">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modifikasi JS untuk Operasi Modal Form CRUD Siswa -->
            <script>
                function openAddStudentModal() {
                    document.getElementById('studentForm').reset();
                    document.getElementById('studentFormAction').value = 'add_student';
                    document.getElementById('studentId').value = '';
                    document.getElementById('studentModalTitle').innerText = 'Tambah Data Siswa';
                    document.getElementById('studentFormSubmitBtn').innerText = 'Tambah Siswa';
                    openModal('studentFormModal');
                }

                function openEditStudentModal(student) {
                    document.getElementById('studentFormAction').value = 'edit_student';
                    document.getElementById('studentId').value = student.id;
                    document.getElementById('studentModalTitle').innerText = 'Edit Data Siswa';
                    document.getElementById('studentFormSubmitBtn').innerText = 'Perbarui Data';

                    document.getElementById('nomor_peserta').value = student.nomor_peserta;
                    document.getElementById('nisn').value = student.nisn;
                    document.getElementById('nama').value = student.nama;
                    document.getElementById('jenis_kelamin').value = student.jenis_kelamin;
                    document.getElementById('kelas').value = student.kelas;
                    document.getElementById('tempat_lahir').value = student.tempat_lahir;
                    document.getElementById('tanggal_lahir').value = student.tanggal_lahir;
                    document.getElementById('status_kelulusan').value = student.status_kelulusan;
                    
                    // Format release timestamp ke datetime-local (YYYY-MM-DDTHH:MM:SS)
                    if (student.release_timestamp) {
                        var dateStr = student.release_timestamp.replace(' ', 'T');
                        document.getElementById('release_timestamp').value = dateStr;
                    } else {
                        document.getElementById('release_timestamp').value = '';
                    }

                    openModal('studentFormModal');
                }
            </script>

        <!-- ========================================================
             TAB 3: TESTIMONIALS (MODERASI TESTIMONI)
             ======================================================== -->
        <?php elseif ($activeTab === 'testimonials'): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Kesan & Pesan dari Siswa</h2>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Pengirim</th>
                                <th>Pesan Kesan</th>
                                <th>Likes</th>
                                <th>Status</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM testimonials ORDER BY id DESC");
                            $testimonialsList = $stmt->fetchAll();

                            if (!empty($testimonialsList)):
                                foreach ($testimonialsList as $t):
                            ?>
                                <tr>
                                    <td><small><?php echo date('d-m-Y H:i', strtotime($t['date'])); ?></small></td>
                                    <td><strong><?php echo htmlspecialchars($t['name']); ?></strong></td>
                                    <td style="max-width:300px; font-style:italic;">"<?php echo htmlspecialchars($t['message']); ?>"</td>
                                    <td><i class="fa-solid fa-heart" style="color:var(--danger);"></i> <?php echo $t['likes']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $t['status'] === 'approved' ? 'badge-success' : ($t['status'] === 'pending' ? 'badge-warning' : 'badge-danger'); ?>">
                                            <?php echo htmlspecialchars($t['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions-cell">
                                            <?php if ($t['status'] !== 'approved'): ?>
                                                <form action="admin_dashboard.php?tab=testimonials" method="POST" style="margin:0;">
                                                    <input type="hidden" name="action" value="approve_testimonial">
                                                    <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                                    <button type="submit" class="btn-icon" style="color:var(--success); border-color:var(--success);" title="Setujui"><i class="fa-solid fa-check"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($t['status'] !== 'rejected'): ?>
                                                <form action="admin_dashboard.php?tab=testimonials" method="POST" style="margin:0;">
                                                    <input type="hidden" name="action" value="reject_testimonial">
                                                    <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                                    <button type="submit" class="btn-icon" style="color:var(--warning); border-color:var(--warning);" title="Tolak"><i class="fa-solid fa-ban"></i></button>
                                                </form>
                                            <?php endif; ?>

                                            <form action="admin_dashboard.php?tab=testimonials" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus testimoni ini secara permanen beserta seluruh komentar terkait?')" style="margin:0;">
                                                <input type="hidden" name="action" value="delete_testimonial">
                                                <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                                <button type="submit" class="btn-icon btn-icon-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endforeach;
                            else:
                            ?>
                                <tr><td colspan="6" style="text-align: center; color: #888;">Belum ada testimoni dari siswa.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- ========================================================
             TAB 4: TEACHER MESSAGES (PESAN GURU)
             ======================================================== -->
        <?php elseif ($activeTab === 'teacher_messages'): ?>
            <div class="stats-grid" style="grid-template-columns: 1fr 2fr; align-items: start;">
                <!-- Form Tulis Pesan -->
                <div class="card">
                    <div class="card-header">
                        <h2>Tulis Pesan Guru Baru</h2>
                    </div>
                    <div class="card-body">
                        <form action="admin_dashboard.php?tab=teacher_messages" method="POST">
                            <input type="hidden" name="action" value="add_teacher_message">
                            
                            <div class="form-group-db">
                                <label for="name">Nama Lengkap Guru / Gelar</label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="Contoh: Tin Sumartini, S.Pd." required>
                            </div>

                            <div class="form-group-db">
                                <label for="message">Isi Petuah / Pesan Kelulusan</label>
                                <textarea id="message" name="message" class="form-control" rows="6" placeholder="Tulis petuah Anda untuk siswa lulus di sini..." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;"><i class="fa-solid fa-paper-plane"></i> Terbitkan Pesan</button>
                        </form>
                    </div>
                </div>

                <!-- Daftar Pesan Guru -->
                <div class="card">
                    <div class="card-header">
                        <h2>Daftar Pesan Guru yang Diterbitkan</h2>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Guru</th>
                                    <th>Isi Pesan</th>
                                    <th>Likes</th>
                                    <th>Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT * FROM teacher_messages ORDER BY id DESC");
                                $messagesList = $stmt->fetchAll();

                                if (!empty($messagesList)):
                                    foreach ($messagesList as $msg):
                                ?>
                                    <tr>
                                        <td><small><?php echo date('d-m-Y H:i', strtotime($msg['date'])); ?></small></td>
                                        <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                                        <td style="max-width:250px; font-style:italic;">"<?php echo htmlspecialchars($msg['message']); ?>"</td>
                                        <td><i class="fa-solid fa-heart" style="color:var(--danger);"></i> <?php echo $msg['likes']; ?></td>
                                        <td>
                                            <div class="actions-cell">
                                                <form action="admin_dashboard.php?tab=teacher_messages" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesan ini beserta seluruh komentar terkait?')" style="margin:0;">
                                                    <input type="hidden" name="action" value="delete_teacher_message">
                                                    <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                                    <button type="submit" class="btn-icon btn-icon-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                    <tr><td colspan="5" style="text-align: center; color: #888;">Belum ada pesan dari guru yang diterbitkan.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <!-- ========================================================
             TAB 5: LOG PENGECEKAN (HISTORY)
             ======================================================== -->
        <?php elseif ($activeTab === 'history'): ?>
            <?php
            // Pagination & Search
            $search = trim($_GET['search'] ?? '');
            
            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 15;
            $offset = ($page - 1) * $limit;

            if (!empty($search)) {
                $countQuery = $pdo->prepare("SELECT COUNT(*) FROM check_history WHERE student_name LIKE ? OR nomor_peserta LIKE ?");
                $countQuery->execute(["%$search%", "%$search%"]);
                $totalRecords = $countQuery->fetchColumn();

                $stmt = $pdo->prepare("SELECT * FROM check_history WHERE student_name LIKE ? OR nomor_peserta LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?");
                $stmt->bindValue(1, "%$search%", PDO::PARAM_STR);
                $stmt->bindValue(2, "%$search%", PDO::PARAM_STR);
                $stmt->bindValue(3, $limit, PDO::PARAM_INT);
                $stmt->bindValue(4, $offset, PDO::PARAM_INT);
                $stmt->execute();
                $historyList = $stmt->fetchAll();
            } else {
                $totalRecords = $pdo->query("SELECT COUNT(*) FROM check_history")->fetchColumn();

                $stmt = $pdo->prepare("SELECT * FROM check_history ORDER BY id DESC LIMIT ? OFFSET ?");
                $stmt->bindValue(1, $limit, PDO::PARAM_INT);
                $stmt->bindValue(2, $offset, PDO::PARAM_INT);
                $stmt->execute();
                $historyList = $stmt->fetchAll();
            }

            $totalPages = ceil($totalRecords / $limit);
            ?>
            <div class="card">
                <div class="card-header">
                    <form action="admin_dashboard.php" method="GET" class="search-form">
                        <input type="hidden" name="tab" value="history">
                        <input type="text" name="search" class="form-control" placeholder="Cari Nama Siswa, No. Peserta..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </form>
                    
                    <form action="admin_dashboard.php?tab=history" method="POST" onsubmit="return confirm('⚠️ TINDAKAN PERINGATAN: Apakah Anda yakin ingin menghapus bersih seluruh riwayat log pengecekan kelulusan?')">
                        <input type="hidden" name="action" value="clear_history">
                        <button type="submit" class="btn btn-danger"><i class="fa-solid fa-broom"></i> Bersihkan Seluruh Log</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Waktu Pengecekan</th>
                                <th>Nomor Peserta</th>
                                <th>Nama Lengkap Siswa</th>
                                <th>Hasil Pencarian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($historyList)):
                                $no = $offset + 1;
                                foreach ($historyList as $log):
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo date('d M Y, H:i:s', strtotime($log['timestamp'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($log['nomor_peserta']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($log['student_name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $log['result'] === 'Lulus' ? 'badge-success' : ($log['result'] === 'Tidak Lulus' ? 'badge-danger' : 'badge-warning'); ?>">
                                            <?php echo htmlspecialchars($log['result']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php 
                                endforeach;
                            else:
                            ?>
                                <tr><td colspan="5" style="text-align: center; color: #888;">Log pencarian kosong.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginasi -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-body" style="padding: 0 24px 24px 24px;">
                        <div class="pagination-container">
                            <span class="pagination-info">Menampilkan data <?php echo $offset + 1; ?> - <?php echo min($offset + $limit, $totalRecords); ?> dari total <?php echo $totalRecords; ?> log riwayat</span>
                            <div class="pagination-links">
                                <?php if ($page > 1): ?>
                                    <a href="?tab=history&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="pagination-link">&laquo; Prev</a>
                                <?php else: ?>
                                    <span class="pagination-link disabled">&laquo; Prev</span>
                                <?php endif; ?>

                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <a href="?tab=history&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <a href="?tab=history&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="pagination-link">Next &raquo;</a>
                                <?php else: ?>
                                    <span class="pagination-link disabled">Next &raquo;</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        <!-- ========================================================
             TAB 6: SETTINGS (PENGATURAN)
             ======================================================== -->
        <?php elseif ($activeTab === 'settings'): ?>
            <div class="stats-grid" style="grid-template-columns: 1fr 1fr; align-items: start;">
                <!-- Pengaturan Global Sistem -->
                <div class="card">
                    <div class="card-header">
                        <h2>Sistem Target Kelulusan & Pengumuman</h2>
                    </div>
                    <div class="card-body">
                        <form action="admin_dashboard.php?tab=settings" method="POST">
                            <input type="hidden" name="action" value="save_settings">

                            <div class="form-group-db">
                                <label for="target_date">Tanggal & Waktu Pengumuman Dibuka</label>
                                <input type="text" id="target_date" name="target_date" class="form-control" placeholder="Format: YYYY-MM-DD HH:MM:SS" value="<?php echo htmlspecialchars($settingTargetDate); ?>" required>
                                <small style="color:var(--text-muted); font-size:0.8em; margin-top:5px; display:block;">Contoh format: <code>2025-06-02 15:00:00</code> (Waktu Indonesia Barat/WIB)</small>
                            </div>

                            <div class="form-group-db" style="margin: 25px 0 30px 0;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <input type="checkbox" id="maintenance_mode" name="maintenance_mode" style="width:20px; height:20px; accent-color:var(--primary);" <?php echo $settingMaintenance === '1' ? 'checked' : ''; ?>>
                                    <label for="maintenance_mode" style="user-select:none; margin:0; cursor:pointer;"><strong>Aktifkan Mode Maintenance</strong></label>
                                </div>
                                <small style="color:var(--text-muted); font-size:0.8em; margin-top:5px; display:block; margin-left: 32px;">Menyembunyikan form pengecekan kelulusan dan menampilkan informasi perawatan sistem.</small>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;"><i class="fa-solid fa-floppy-disk"></i> Simpan Konfigurasi</button>
                        </form>
                    </div>
                </div>

                <!-- Ubah Password Admin -->
                <div class="card">
                    <div class="card-header">
                        <h2>Ubah Kata Sandi Administrator</h2>
                    </div>
                    <div class="card-body">
                        <form action="admin_dashboard.php?tab=settings" method="POST">
                            <input type="hidden" name="action" value="change_password">

                            <div class="form-group-db">
                                <label for="current_password">Kata Sandi Saat Ini</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Kata sandi lama" required>
                            </div>

                            <div class="form-group-db">
                                <label for="new_password">Kata Sandi Baru</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Kata sandi baru (min 6 karakter)" required>
                            </div>

                            <div class="form-group-db">
                                <label for="confirm_password">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Ulangi kata sandi baru" required>
                            </div>

                            <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center; background-color: var(--primary); border-color: var(--primary); box-shadow: 0 4px 10px rgba(79,70,229,0.2);"><i class="fa-solid fa-key"></i> Perbarui Kata Sandi</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- JS GLOBAL MODAL -->
    <script>
        function openModal(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) modal.style.display = 'flex';
        }

        function closeModal(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) modal.style.display = 'none';
        }

        // Tutup modal jika klik di luar box
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
