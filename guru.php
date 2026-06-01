<?php
session_start(); // Mulai sesi untuk manajemen akses
date_default_timezone_set('Asia/Jakarta'); // Mengatur zona waktu ke WIB
$kepalaMadrasahMessage = [
    'message' => "Selamat dan sukses untuk seluruh siswa-siswi MTsN 11 Majalengka yang telah menyelesaikan pendidikan dengan baik. Teruslah berjuang meraih impian dan jadilah generasi penerus bangsa yang membanggakan. Ilmu yang kalian dapatkan semoga menjadi bekal untuk masa depan yang lebih cerah. Ingatlah selalu nilai-nilai agama dan budi pekerti luhur. Jaga nama baik almamater dan teruslah berprestasi di jenjang pendidikan berikutnya.",
    'name' => "H. Jajang Gunawan, S.Ag.,M.Pd.I.", // Ganti dengan nama Kepala Madrasah yang sebenarnya
    'position' => "Kepala Madrasah MTsN 11 Majalengka"
];

// Konfigurasi PIN (gunakan hash, bukan plain text)
// PIN asli: 20278893
$pin_hash = '$2y$10$Y4sBDd9i0ze.cILR.DlwIum2xgSwdy64TIfwG667/rE/YCV9BOqQ6';

$loggedIn = false;
$errorPin = '';
$showTable = false;

// Cek apakah ada form PIN yang dikirim
if (isset($_POST['pin_submit'])) {
    if (isset($_POST['pin']) && password_verify($_POST['pin'], $pin_hash)) {
        $_SESSION['logged_in_guru'] = true;
        $loggedIn = true;
    } else {
        $errorPin = 'PIN salah!';
    }
}

// Cek apakah sudah login berdasarkan sesi
if (isset($_SESSION['logged_in_guru']) && $_SESSION['logged_in_guru'] === true) {
    $loggedIn = true;
}

// --- Logika untuk menyimpan pesan guru ---
$messageSuccess = '';
$messageError = '';
$jsonFileTeacherMessages = 'teacher_messages.json';

if (isset($_POST['submit_teacher_message']) && $loggedIn) {
    $teacherName = trim($_POST['teacher_name'] ?? '');
    $messageContent = trim($_POST['message_content'] ?? '');

    if (empty($teacherName) || empty($messageContent)) {
        $messageError = 'Nama Guru dan Pesan tidak boleh kosong.';
    } else {
        $currentMessages = [];
        if (file_exists($jsonFileTeacherMessages)) {
            $jsonData = file_get_contents($jsonFileTeacherMessages);
            $currentMessages = json_decode($jsonData, true);
            if (!is_array($currentMessages)) {
                $currentMessages = [];
            }
        }

        // --- PERBAIKAN: Pindahkan deklarasi $newId ke dalam blok ini ---
        // Buat ID unik untuk pesan guru baru
        // Gunakan prefix 'tm-' untuk teacher messages
        $newId = 'tm-' . uniqid(); 

        $newMessage = [
            'id' => $newId, // Menggunakan ID unik yang baru dibuat
            'name' => htmlspecialchars($teacherName, ENT_QUOTES, 'UTF-8'), // Sanitasi input
            'message' => htmlspecialchars($messageContent, ENT_QUOTES, 'UTF-8'), // Sanitasi input
            'date' => date('Y-m-d H:i:s'), // Tanggal dan waktu pengiriman akan dalam WIB
            'likes' => 0, // Inisiasi jumlah likes dengan 0
            'comments' => [] // Inisiasi array comments kosong
        ];

        // Tambahkan pesan baru ke awal array (agar yang terbaru di atas)
        array_unshift($currentMessages, $newMessage);

        // Opsional: Batasi jumlah pesan yang disimpan (misalnya, hanya 100 pesan terbaru)
        $currentMessages = array_slice($currentMessages, 0, 100);

        if (file_put_contents($jsonFileTeacherMessages, json_encode($currentMessages, JSON_PRETTY_PRINT))) {
            $messageSuccess = 'Pesan berhasil disimpan.';
            // Opsional: Redirect untuk mencegah resubmission form pada refresh
            // header('Location: ' . $_SERVER['PHP_SELF']);
            // exit();
        } else {
            $messageError = 'Gagal menyimpan pesan. Periksa izin file.';
        }
    }
}
// --- Akhir logika pesan guru ---


// Membaca data siswa
$students = [];
$jsonFileStudents = 'data_kelulusan.json';
if (file_exists($jsonFileStudents)) {
    $jsonDataStudents = file_get_contents($jsonFileStudents);
    $students = json_decode($jsonDataStudents, true);
    if (!is_array($students)) {
        $students = [];
    }
}

// Membaca pesan guru yang sudah ada (untuk ditampilkan)
$teacherMessages = [];
if (file_exists($jsonFileTeacherMessages)) {
    $jsonDataTeacherMessages = file_get_contents($jsonFileTeacherMessages);
    $teacherMessages = json_decode($jsonDataTeacherMessages, true);
    if (!is_array($teacherMessages)) {
        $teacherMessages = [];
    }
    // Urutkan berdasarkan tanggal terbaru di atas
    usort($teacherMessages, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

// Membaca data testimoni
$testimonials = [];
$jsonFileTestimonials = 'testimonials.json';
if (file_exists($jsonFileTestimonials)) {
    $jsonDataTestimonials = file_get_contents($jsonFileTestimonials);
    $testimonials = json_decode($jsonDataTestimonials, true);
    if (!is_array($testimonials)) {
        $testimonials = [];
    }
    // Urutkan berdasarkan tanggal terbaru di atas
    usort($testimonials, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

// Membaca data riwayat pengecekan
$checkHistory = [];
$jsonFileCheckHistory = 'check_history.json';
if (file_exists($jsonFileCheckHistory)) {
    $jsonDataCheckHistory = file_get_contents($jsonFileCheckHistory);
    $checkHistory = json_decode($jsonDataCheckHistory, true);
    if (!is_array($checkHistory)) {
        $checkHistory = [];
    }
}

// --- Pagination Logic ---
$records_per_page = 10; // Default 10 data per page

// Get current page from URL, default to 1 if not set
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the database query (or array slice)
$offset = ($current_page - 1) * $records_per_page;

// Get total number of records
$total_records = count($students); // In a real app: SELECT COUNT(*) FROM students;

// Calculate total pages
$total_pages = ceil($total_records / $records_per_page);

// Slice the array to get only the data for the current page
// In a real app, this would be part of your SQL query (LIMIT offset, records_per_page)
$paginated_students = array_slice($students, $offset, $records_per_page);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Guru - MTsN 11 Majalengka</title>
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General body style (default for logged-in state) */
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: var(--bg-color); /* Default background for admin page */
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* OVERRIDE body style for login page (when $loggedIn is false) */
        <?php if (!$loggedIn) { ?>
        body {
            font-family: 'Segoe UI', 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0078d4 0%, #004a8b 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        <?php } ?>

        /* Specific styles for admin page, supplement style.css */
        .admin-container {
            max-width: 1000px; /* Slightly wider admin container */
            margin: 40px auto;
            padding: 30px;
            background-color: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        /* OVERRIDE .admin-container style for login page (when $loggedIn is false) */
        <?php if (!$loggedIn) { ?>
        .admin-container {
            background: none;
            box-shadow: none;
            padding: 0;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        <?php } ?>


        .admin-container h1 {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-size: 2.5em; /* Larger heading */
            font-family: 'Merriweather', serif;
        }
        .admin-container h2 {
            color: var(--secondary-color);
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 2em; /* Larger subheading */
            border-bottom: 2px solid var(--border-color); /* Thicker border */
            padding-bottom: 10px;
            text-align: left; /* Align h2 to left */
        }
        .admin-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.95em; /* Slightly larger font */
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); /* Subtle table shadow */
        }
        .admin-container th, .admin-container td {
            border: 1px solid var(--border-color);
            padding: 12px; /* More padding */
            text-align: left;
        }
        .admin-container th {
            background-color: var(--bg-light); /* Changed to var(--bg-light) for consistency */
            font-weight: 700; /* Bolder header */
            color: var(--text-color);
        }
        .admin-container tr:nth-child(even) {
            background-color: var(--bg-color); /* Changed to var(--bg-color) */
        }
        .status-lulus-admin {
            color: var(--success-color); /* Changed to success-color */
            font-weight: 600;
        }
        .status-tidak-lulus-admin {
            color: var(--danger-color);
            font-weight: 600;
        }
        .testimonial-list, .check-history-list, .teacher-message-list { /* Added teacher-message-list */
            list-style: none;
            padding: 0;
            margin-top: 20px;
            text-align: left;
        }
        .testimonial-item, .check-history-item, .teacher-message-item { /* Added teacher-message-item */
            background-color: var(--bg-light);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 6px rgba(0,0,0,0.03); /* Subtle item shadow */
        }
        .testimonial-item p, .check-history-item p, .teacher-message-item p { /* Added teacher-message-item p */
            margin: 0 0 8px 0;
            font-style: italic;
            color: #555;
        }
        .testimonial-item .author-date, .check-history-item .info-date, .teacher-message-item .info-date { /* Added teacher-message-item .info-date */
            font-size: 0.9em;
            color: #777;
            text-align: right;
        }
        .testimonial-item .author-date strong, .check-history-item .info-date strong, .teacher-message-item .info-date strong { /* Added teacher-message-item .info-date strong */
            color: var(--secondary-color);
        }

        /* Styles for the PIN form - Windows 11 like */
        .pin-form {
            max-width: 400px; /* Lebar yang lebih moderat */
            width: 90%; /* Responsif */
            padding: 50px 40px; /* Padding lebih besar */
            background-color: var(--win11-card-bg); /* Menggunakan variabel baru */
            border-radius: 12px; /* Lebih rounded */
            box-shadow: 0 10px 30px var(--win11-shadow-strong); /* Bayangan lebih dramatis */
            text-align: center;
            position: relative; /* Untuk efek blur latar belakang */
            overflow: hidden; /* Menjaga efek blur di dalam batas */
            backdrop-filter: blur(10px); /* Efek blur kaca */
            -webkit-backdrop-filter: blur(10px); /* Dukungan Safari */
            border: 1px solid var(--win11-card-border); /* Border tipis transparan */
        }

        .pin-form h2 {
            color: #333; /* Warna teks yang jelas */
            margin-bottom: 30px; /* Jarak bawah lebih */
            font-size: 2.2em; /* Ukuran heading lebih besar */
            font-weight: 600; /* Sedikit lebih tebal */
            font-family: 'Segoe UI', 'Merriweather', serif; /* Font yang cocok */
        }

        .pin-form .form-group {
            margin-bottom: 30px; /* Jarak bawah untuk input */
        }

        .pin-form input[type="password"] {
            width: 100%; /* Isi penuh lebar parent */
            padding: 15px 20px; /* Padding lebih besar */
            border: 2px solid #ccc; /* Border default */
            border-radius: 8px; /* Lebih rounded */
            font-size: 1.8em; /* Ukuran font lebih besar */
            text-align: center;
            background-color: #f8f8f8; /* Background input sedikit abu-abu */
            color: #333;
            outline: none; /* Hilangkan outline default */
            transition: all 0.3s ease; /* Transisi halus */
            letter-spacing: 5px; /* Jarak antar karakter agar terlihat seperti PIN */
            box-sizing: border-box; /* Pastikan padding termasuk dalam lebar */
        }

        .pin-form input[type="password"]:focus {
            border-color: var(--primary-color); /* Border biru saat fokus */
            box-shadow: 0 0 0 4px rgba(0, 120, 212, 0.2); /* Efek shadow saat fokus */
            background-color: #fff; /* Background putih saat fokus */
        }

        .pin-form button[type="submit"], .form-teacher-message button { /* Added form-teacher-message button */
            width: 100%;
            background-color: var(--primary-color);
            color: #fff;
            padding: 16px 0; /* Padding lebih besar */
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2em;
            font-weight: 700;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-top: 25px; /* Jarak dari input PIN */
        }
        .pin-form button[type="submit"]:hover, .form-teacher-message button:hover { /* Added form-teacher-message button */
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        .pin-form button[type="submit"]:active, .form-teacher-message button:active { /* Added form-teacher-message button */
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .error-message {
            color: var(--danger-color);
            margin-bottom: 25px; /* Jarak bawah lebih */
            font-weight: 600;
            font-size: 1.1em;
        }
        .success-message { /* Added success message style */
            color: var(--success-color); /* Changed to success-color */
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Styles for the new teacher message form */
        .form-teacher-message {
            background-color: var(--bg-light);
            padding: 25px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-top: 30px;
            text-align: left;
        }
        .form-teacher-message label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }
        .form-teacher-message input[type="text"],
        .form-teacher-message textarea {
            width: calc(100% - 20px); /* Adjust for padding */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid var(--form-border);
            border-radius: 5px;
            font-size: 1em;
        }
        .form-teacher-message textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-teacher-message input[type="text"]:focus,
        .form-teacher-message textarea:focus {
            border-color: var(--focus-border);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        /* Optional: Animasi untuk efek "shake" jika PIN salah */
        .pin-form.shake {
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-10px);
            }
            20%, 40%, 60%, 80% {
                transform: translateX(10px);
            }
        }

        /* Styles for "Show More" feature */
        .hidden-item {
            display: none; /* Sembunyikan item secara default */
        }

        .show-more-button {
            display: block;
            width: fit-content; /* Sesuai lebar konten */
            margin: 20px auto; /* Pusatkan tombol */
            padding: 10px 25px;
            background-color: #6c757d; /* Warna abu-abu yang netral */
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        .show-more-button:hover {
            background-color: #5a6268;
        }

        /* Styles for likes and comments */
        .message-actions {
            display: flex;
            justify-content: flex-end; /* Pindahkan tombol ke kanan */
            align-items: center;
            margin-top: 10px;
            gap: 10px; /* Jarak antar tombol */
        }

        .like-button, .comment-button {
            background: none;
            border: none;
            color: var(--light-text-color);
            cursor: pointer;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 5px 8px; /* Padding untuk area klik yang lebih baik */
            border-radius: 5px;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .like-button {
            background: #ffeaea;
            border: 1.5px solid #e74c3c;
            color: #e74c3c;
            border-radius: 20px;
            padding: 8px 20px 8px 14px;
            font-size: 1em;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(231,76,60,0.08);
            cursor: pointer;
            margin-right: 10px;
            transition: background 0.18s, color 0.18s, box-shadow 0.18s, border 0.18s;
            outline: none;
            position: relative;
        }
        .like-button .fa-heart {
            color: #e74c3c;
            font-size: 1.2em;
            transition: color 0.2s;
        }
        .like-button.liked .fa-heart {
            color: #c0392b;
            animation: liked-pop 0.3s;
        }
        @keyframes liked-pop {
            0% { transform: scale(1); }
            60% { transform: scale(1.4); }
            100% { transform: scale(1); }
        }
        .like-button .like-count {
            font-weight: bold;
            color: #e74c3c;
            margin-left: 2px;
            font-size: 1.05em;
        }
        .like-button .like-label {
            color: #c0392b;
            font-size: 1em;
            margin-left: 3px;
            letter-spacing: 0.5px;
        }
        .like-button:hover, .like-button.liked {
            background: #fff0f0;
            color: #c0392b;
            border-color: #c0392b;
            box-shadow: 0 4px 16px rgba(231,76,60,0.13);
            transform: translateY(-1px) scale(1.04);
        }
        .like-button:active {
            background: #ffd6d6;
            box-shadow: 0 1px 4px rgba(231,76,60,0.08);
            transform: scale(0.98);
        }

        .comment-button {
            background: #e7f3fe;
            border: 1px solid #3498db;
            color: #3498db;
            border-radius: 20px;
            padding: 8px 20px 8px 14px;
            font-size: 1em;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(52,152,219,0.08);
            cursor: pointer;
            transition: background 0.18s, color 0.18s, box-shadow 0.18s, border 0.18s;
            outline: none;
        }
        .comment-button .fa-comment {
            color: #3498db;
            font-size: 1.2em;
            transition: color 0.2s;
        }
        .comment-button:hover {
            background: #d1e7ff;
            color: #155724;
            border-color: #155724;
            box-shadow: 0 4px 16px rgba(52,152,219,0.13);
            transform: translateY(-1px);
        }
        .comment-button:active {
            background: #cfe2ff;
            box-shadow: 0 1px 4px rgba(52,152,219,0.08);
            transform: scale(0.98);
        }

        .like-count, .comment-count {
            font-weight: 600;
            color: var(--text-color);
        }

        .comments-section {
            border-top: 1px solid var(--border-color);
            margin-top: 15px;
            padding-top: 15px;
        }

        .comments-section h4 {
            margin-top: 0;
            margin-bottom: 10px;
            color: var(--secondary-color);
        }

        .comments-section .existing-comments p {
            background-color: #f0f2f5; /* Warna background komentar */
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 8px;
            font-style: normal; /* Override italic dari p umum */
            color: var(--text-color);
            font-size: 0.9em;
        }

        .comments-section .existing-comments p strong {
            color: var(--primary-color);
        }
        .comments-section .existing-comments p small {
            display: block;
            text-align: right;
            color: var(--light-text-color);
            font-size: 0.8em;
        }

        .add-comment-form input[type="text"],
        .add-comment-form textarea {
            width: calc(100% - 22px); /* Adjust for padding and border */
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid var(--form-border);
            border-radius: 5px;
            font-size: 0.9em;
        }

        .add-comment-form button {
            background-color: var(--primary-color);
            color: #fff;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s ease;
        }

        .add-comment-form button:hover {
            background-color: var(--secondary-color);
        }

        /* Responsive adjustments */
        @media (max-width: 900px) {
            .admin-container h1 { font-size: 2em; }
            .admin-container h2 { font-size: 1.8em; }
            .admin-container table { font-size: 0.9em; }
            .admin-container th, .admin-container td { padding: 10px; }
            .testimonial-item, .check-history-item, .teacher-message-item { padding: 12px; }
            .pin-form { padding: 40px 30px; }
            .pin-form h2 { font-size: 2em; }
            .pin-form input[type="password"] { font-size: 1.5em; padding: 12px 15px; }
            .pin-form button[type="submit"], .form-teacher-message button { padding: 12px 0; font-size: 1em; }
            .show-more-button { padding: 8px 20px; font-size: 0.9em; }
            .like-button, .comment-button { font-size: 0.8em; padding: 4px 6px; }
        }

        @media (max-width: 500px) {
            .admin-container { padding: 15px; margin: 20px auto; }
            .admin-container h1 { font-size: 1.8em; }
            .admin-container h2 { font-size: 1.6em; }
            .admin-container th, .admin-container td { padding: 8px; }
            .pin-form { padding: 30px 20px; }
            .pin-form h2 { font-size: 1.8em; }
            .pin-form input[type="password"] { font-size: 1.3em; padding: 10px 12px; }
            .pin-form button[type="submit"], .form-teacher-message button { padding: 10px 0; font-size: 0.95em; }
            .error-message { font-size: 0.9em; }
            .like-button, .comment-button { font-size: 0.75em; padding: 3px 5px; }
            .add-comment-form input[type="text"], .add-comment-form textarea { width: calc(100% - 18px); }
            .comments-section .existing-comments p { font-size: 0.85em; }
        }
                .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            padding: 10px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .pagination a, .pagination span {
            padding: 8px 15px;
            margin: 0 5px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #3498db;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }
        .pagination a:hover {
            background-color: #3498db;
            color: white;
        }
        .pagination span.current-page {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
            font-weight: bold;
            cursor: default;
        }
        .pagination span.disabled {
            color: #bbb;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php if ($loggedIn) { ?>
        <img src="assets/mtsn11majalengka-logo.png" alt="Logo MTsN 11 Majalengka" class="logo">
            <h1>Pengumuman Kelulusan<br>MTsN 11 Majalengka</h1>
            <h2>Daftar Status Kelulusan Siswa</h2>
            <?php if ($showTable) { ?>
                <section class="card fade-in scroll-reveal">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>No. Peserta</th>
                                <th>NISN</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($paginated_students)) { ?>
                               <?php
                                // Calculate the starting number for the current page
                                $row_number = $offset + 1;
                                ?>
                                <?php foreach ($paginated_students as $student) { ?>
                                    <tr>
                                        <td><?php echo $row_number++; ?></td>
                                        <td><?php echo htmlspecialchars($student['nomor_peserta']); ?></td>
                                        <td><?php echo htmlspecialchars($student['nisn']); ?></td>
                                        <td><?php echo htmlspecialchars($student['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($student['kelas']); ?></td>
                                        <td class="<?php echo ($student['status_kelulusan'] == 'Lulus') ? 'status-lulus-admin' : 'status-tidak-lulus-admin'; ?>">
                                            <?php echo htmlspecialchars($student['status_kelulusan']); ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="6">Data siswa tidak ditemukan atau kosong untuk halaman ini.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="pagination">
                        <?php if ($current_page > 1) { ?>
                            <a href="?page=<?php echo $current_page - 1; ?>">Previous</a>
                        <?php } else { ?>
                            <span class="disabled">Previous</span>
                        <?php } ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                            <?php if ($i == $current_page) { ?>
                                <span class="current-page"><?php echo $i; ?></span>
                            <?php } else { ?>
                                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php } ?>
                        <?php } ?>

                        <?php if ($current_page < $total_pages) { ?>
                            <a href="?page=<?php echo $current_page + 1; ?>">Next</a>
                        <?php } else { ?>
                            <span class="disabled">Next</span>
                        <?php } ?>
                    </div>
                </section>
            <?php } else { ?>
                <section class="card fade-in scroll-reveal">
                    <div class="message-container">
                        <p>Mohon maaf data kelulusan belum bisa ditampilkan. Setelah rapat dewan guru, data final akan ditampilkan.</p>
                    </div>
                </section>
            <?php } ?>
            <section class="card fade-in scroll-reveal">
                <h2>Kirim Pesan untuk Siswa</h2>
                <div class="form-teacher-message">
                    <?php if ($messageSuccess) { ?>
                        <p class="success-message"><?php echo $messageSuccess; ?></p>
                    <?php } ?>
                    <?php if ($messageError) { ?>
                        <p class="error-message"><?php echo $messageError; ?></p>
                    <?php } ?>
                    <form action="guru.php" method="POST">
                        <div class="form-group">
                            <label for="teacher_name">Nama Guru:</label>
                            <input type="text" id="teacher_name" name="teacher_name" required>
                        </div>
                        <div class="form-group">
                            <label for="message_content">Pesan:</label>
                            <textarea id="message_content" name="message_content" required></textarea>
                        </div>
                        <button type="submit" name="submit_teacher_message">Kirim Pesan</button>
                    </form>
                </div>
            </section>
            <section id="input-section" class="card fade-in scroll-reveal">
                <div class="testimoni-section card fade-in scroll-reveal">
                    <h2>Pesan dari Kepala Madrasah</h2>
                    <div class="testimoni-grid">
                        <div class="testimoni-item">
                            <div class="head-teacher-photo">
                                <img src="assets/kepalamadrasah.png" alt="<?php echo htmlspecialchars($kepalaMadrasahMessage['name']); ?>" class="photo">
                            </div>
                            <p>"<?php echo htmlspecialchars($kepalaMadrasahMessage['message']); ?>"</p>
                            <span class="author">- <?php echo htmlspecialchars($kepalaMadrasahMessage['name']); ?> <br>(<?php echo htmlspecialchars($kepalaMadrasahMessage['position']); ?>)</span>
                        </div>
                    </div>
                </div>     
            </section>
            <section class="teacher-message-display-section card fade-in scroll-reveal">
                <h2>Pesan dari Guru</h2>
                <?php if (!empty($teacherMessages)): ?>
                    <ul class="teacher-message-list-public">
                        <?php foreach ($teacherMessages as $msg):
                            $messageId = htmlspecialchars($msg['id'] ?? md5($msg['message'] . $msg['date'] . $msg['name']));
                            $likesCount = $msg['likes'] ?? 0;
                            $comments = $msg['comments'] ?? [];
                        ?>
                            <li class="teacher-message-list-public-item scroll-reveal" data-item-id="<?php echo $messageId; ?>" data-item-type="teacher_message">
                                <span class="date"><?php echo date('d M Y H:i', strtotime($msg['date'])); ?></span>
                                <p>"<?php echo htmlspecialchars($msg['message']); ?>"</p>
                                <span class="author">- <?php echo htmlspecialchars($msg['name']); ?></span>
                                <div class="feedback-actions">
                                    <button class="like-button"
                                        data-id="<?php echo $messageId; ?>"
                                        data-type="teacher_message">
                                        <i class="fa-solid fa-heart"></i>
                                        <span class="like-count"><?php echo $likesCount; ?></span>
                                        <span class="like-label">Suka</span>
                                    </button>
                                    <button class="comment-toggle-button"
                                        data-id="<?php echo $messageId; ?>"
                                        data-type="teacher_message"
                                        type="button">
                                        <i class="fa-solid fa-comment"></i> Komentar
                                    </button>
                                </div>
                                <div class="comments-section" id="comments-section-<?php echo $messageId; ?>" style="display: none;">
                                    <ul class="comments-list">
                                        <?php if (!empty($comments)): ?>
                                            <?php foreach ($comments as $comment): ?>
                                                <li class="comment-item">
                                                    <span class="comment-author"><?php echo htmlspecialchars($comment['author']); ?></span>
                                                    <span class="comment-date"><?php echo date('d M Y H:i', strtotime($comment['date'])); ?></span>
                                                    <p class="comment-text"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="no-comments-msg">Belum ada komentar.</p>
                                        <?php endif; ?>
                                    </ul>
                                    <!-- Ubah: gunakan class comment-form saja, tanpa add-comment-form -->
                                    <form class="comment-form" onsubmit="return addComment(event, '<?php echo $messageId; ?>', 'teacher_message')">
                                        <input type="text" placeholder="Nama Anda" class="comment-author-input">
                                        <textarea placeholder="Tambahkan komentar..." required></textarea>
                                        <button type="submit">Kirim Komentar</button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="text-align: center; color: #777;">Belum ada pesan dari guru.</p>
                <?php endif; ?>
            </section>
            <?php if (count($teacherMessages) > 5): ?>
                <button class="show-more-button" data-target="teacher-message-list-public" data-offset="5">Tampilkan Lebih Banyak</button>
            <?php endif; ?>

            <section class="testimonial-display-section card fade-in scroll-reveal">
                <h2>Pesan dan Kesan dari Siswa</h2>
                <?php if (!empty($testimonials)): ?>
                    <ul id="testimonial-list-public-live" class="testimonial-list-public">
                        <?php foreach ($testimonials as $testi):
                            $testimonialId = htmlspecialchars($testi['id'] ?? md5($testi['message'] . $testi['date'] . $testi['name']));
                            $testiLikesCount = $testi['likes'] ?? 0;
                            $comments = $testi['comments'] ?? [];
                        ?>
                            <li class="testimonial-list-public-item scroll-reveal" data-item-id="<?php echo $testimonialId; ?>" data-item-type="testimonial">
                                <span class="date"><?php echo date('d M Y H:i', strtotime($testi['date'])); ?></span>
                                <p>"<?php echo htmlspecialchars($testi['message']); ?>"</p>
                                <span class="author">- <?php echo htmlspecialchars($testi['name']); ?></span>
                                <div class="feedback-actions">
                                    <button class="like-button"
                                        data-id="<?php echo $testimonialId; ?>"
                                        data-type="testimonial">
                                        <i class="fa-solid fa-heart"></i>
                                        <span class="like-count"><?php echo $testiLikesCount; ?></span>
                                        <span class="like-label">Suka</span>
                                    </button>
                                    <button class="comment-toggle-button"
                                        data-id="<?php echo $testimonialId; ?>"
                                        data-type="testimonial">
                                        <i class="fa-solid fa-comment"></i> Komentar
                                    </button>
                                </div>
                                <div class="comments-section" id="comments-section-<?php echo $testimonialId; ?>" style="display: none;">
                                    <ul class="comments-list">
                                        <?php if (!empty($comments)): ?>
                                            <?php foreach ($comments as $comment): ?>
                                                <li class="comment-item">
                                                    <span class="comment-author"><?php echo htmlspecialchars($comment['author']); ?></span>
                                                    <span class="comment-date"><?php echo date('d M Y H:i', strtotime($comment['date'])); ?></span>
                                                    <p class="comment-text"><?php echo htmlspecialchars($comment['comment']); ?></p>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="no-comments-msg">Belum ada komentar.</p>
                                        <?php endif; ?>
                                    </ul>
                                    <form class="comment-form" onsubmit="addComment(event, '<?php echo $testimonialId; ?>', 'testimonial')">
                                        <input type="text" placeholder="Nama Anda" class="comment-author-input">
                                        <textarea placeholder="Tambahkan komentar..." required></textarea>
                                        <button type="submit">Kirim Komentar</button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="text-align: center; color: #777;">Belum ada Pesan dan Kesan dari siswa.</p>
                <?php endif; ?>
            </section>
            <?php if (count($testimonials) > 5): ?>
                <button class="show-more-button" data-target="testimonial-list-public-live" data-offset="5">Tampilkan Lebih Banyak</button>
            <?php endif; ?>

            <section class="card fade-in scroll-reveal">
                <h2>Riwayat Pengecekan Kelulusan</h2>
                <ul class="check-history-list" id="check-history-list">
                    <?php if (!empty($checkHistory)) {
                        $i = 0;
                        foreach ($checkHistory as $entry) { ?>
                            <li class="check-history-item <?php echo ($i >= 5) ? 'hidden-item' : ''; ?>">
                                <p>
                                    <?php echo htmlspecialchars($entry['student_name']); ?> (<?php echo htmlspecialchars($entry['nomor_peserta']); ?>)
                                    sudah mengecek kelulusan dengan hasil:
                                    <span class="<?php echo ($entry['result'] == 'Lulus') ? 'status-lulus-admin' : 'status-tidak-lulus-admin'; ?>">
                                        <?php echo htmlspecialchars($entry['result']); ?>
                                    </span>
                                </p>
                                <div class="info-date">
                                    Pada: <?php echo htmlspecialchars($entry['timestamp']); ?>
                                </div>
                            </li>
                        <?php $i++; }
                    } else { ?>
                        <li>Belum ada riwayat pengecekan kelulusan.</li>
                    <?php } ?>
                </ul>
                <?php if (count($checkHistory) > 5) { ?>
                    <button class="show-more-button" data-target="check-history-list" data-offset="5">Tampilkan Lebih Banyak</button>
                <?php } ?>
            </section>
            <footer class="footer">
                <p>&copy; <?php echo date('Y'); ?> MTsN 11 Majalengka. Semua Hak Dilindungi. Developed by A.T. Aditya</p>
                <div class="social-links"></div>
            </footer>
        <?php } else { ?>
            <section class="card fade-in scroll-reveal">
                <div class="pin-form">
                    <div class="pin-lock-icon">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <h2>Login Guru</h2>
                    <?php if ($errorPin) { ?>
                        <div class="error-message"><?php echo $errorPin; ?></div>
                    <?php } ?>
                    <form action="guru.php" method="POST" autocomplete="off">
                        <div class="form-group">
                            <input type="password" id="pin_input" name="pin"
                                   placeholder="Masukkan PIN"
                                   pattern="\d*" maxlength="8" inputmode="numeric"
                                   required autofocus>
                        </div>
                        <button type="submit" name="pin_submit">Masuk</button>
                    </form>
                    <div class="pin-hint">
                        <i class="fa-solid fa-circle-info"></i> Hanya untuk Guru. Hubungi admin jika lupa PIN.
                    </div>
                    <footer class="footer" style="margin-top:24px;">
                        <p style="font-size:0.98em;">&copy; <?php echo date('Y'); ?> MTsN 11 Majalengka.<br> Developed by A.T. Aditya</p>
                    </footer>
                </div>
            </section>
        <?php } ?>
    </div>
    <script>
        // JS untuk efek shake pada PIN form
        <?php if ($errorPin) { ?>
            const pinForm = document.querySelector('.pin-form');
            if (pinForm) {
                pinForm.classList.add('shake');
                pinForm.addEventListener('animationend', () => {
                    pinForm.classList.remove('shake');
                }, { once: true });
            }
        <?php } ?>

        // JS untuk fitur "Show More"
        document.addEventListener('DOMContentLoaded', function() {
            const showMoreButtons = document.querySelectorAll('.show-more-button');

            showMoreButtons.forEach(button => {
                const targetListId = button.dataset.target;
                const targetList = document.getElementById(targetListId);
                let currentOffset = parseInt(button.dataset.offset);
                const itemsPerPage = 5;

                // Fungsi untuk menampilkan item
                const showItems = (list, offset, limit) => {
                    // Mengambil semua item, termasuk yang sudah tidak hidden-item
                    const allItems = list.querySelectorAll('li'); // Target semua <li>
                    let itemsShown = 0;
                    for (let i = 0; i < allItems.length && itemsShown < limit; i++) {
                        if (allItems[i].classList.contains('hidden-item')) { // Hanya tampilkan yang hidden
                            allItems[i].classList.remove('hidden-item');
                            itemsShown++;
                        }
                    }
                    return itemsShown; // Mengembalikan jumlah item yang baru ditampilkan
                };

                // Inisialisasi: Sembunyikan tombol jika tidak ada lagi item yang bisa ditampilkan
                const initialHiddenItems = targetList.querySelectorAll('li.hidden-item').length;
                if (initialHiddenItems === 0 && targetList.querySelectorAll('li').length <= itemsPerPage) { // Juga cek jika total item sedikit
                    button.style.display = 'none';
                }

                button.addEventListener('click', () => {
                    const newlyShownCount = showItems(targetList, 0, itemsPerPage); // Tampilkan 5 item berikutnya

                    // Periksa apakah masih ada item yang tersembunyi
                    const remainingHiddenItems = targetList.querySelectorAll('.hidden-item').length;
                    if (remainingHiddenItems === 0) {
                        button.style.display = 'none'; // Sembunyikan tombol jika tidak ada lagi
                    }
                });
            });

            // --- JavaScript untuk Likes dan Comments (AJAX) ---
            document.body.addEventListener('click', async (event) => {
                // Handle Like Button
                if (event.target.closest('.like-button')) {
                    const button = event.target.closest('.like-button');
                    const itemId = button.dataset.id;
                    const itemType = button.dataset.type; // 'teacher_message' atau 'testimonial'
                    const likeCountSpan = button.querySelector('.like-count');
                    const icon = button.querySelector('.fa-heart');

                    // Perbaikan: Kirim itemId dan itemType saja (tanpa action)
                    try {
                        const response = await fetch('like_item.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `itemId=${encodeURIComponent(itemId)}&itemType=${encodeURIComponent(itemType)}`
                        });
                        const data = await response.json();

                        if (data.success) {
                            likeCountSpan.textContent = data.newLikesCount;
                            button.classList.add('liked');
                            button.disabled = true;
                        } else {
                            alert('Gagal like item: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error liking item:', error);
                        alert('Terjadi kesalahan saat like item.');
                    }
                }

                // Handle Comment Button (Toggle comments section)
                if (event.target.closest('.comment-toggle-button')) {
                    const button = event.target.closest('.comment-toggle-button');
                    const itemId = button.dataset.id;
                    const commentsSection = document.getElementById(`comments-section-${itemId}`);
                    if (commentsSection) {
                        commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
                    }
                }
            });

            // Handle Add Comment Form Submission
            document.body.addEventListener('submit', async (event) => {
                if (event.target.closest('.add-comment-form')) {
                    event.preventDefault(); // Prevent default form submission

                    const form = event.target.closest('.add-comment-form');
                    const itemId = form.dataset.id;
                    const itemType = form.dataset.type; // 'teacher' atau 'testimonial'
                    const commentAuthor = form.querySelector('[name="commentAuthor"]').value;
                    const commentText = form.querySelector('[name="commentText"]').value;
                    const commentsSection = document.getElementById(`comments-${itemId}`);
                    const existingCommentsDiv = commentsSection.querySelector('.existing-comments');
                    const commentCountSpan = document.querySelector(`.comment-button[data-id="${itemId}"] .comment-count`);

                    if (commentAuthor.trim() === '' || commentText.trim() === '') {
                        alert('Nama dan komentar tidak boleh kosong.');
                        return;
                    }

                    try {
                        const response = await fetch('add_comment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `itemId=${itemId}&itemType=${itemType}&commentAuthor=${encodeURIComponent(commentAuthor)}&commentText=${encodeURIComponent(commentText)}`
                        });
                        const data = await response.json();

                        if (data.success) {
                            alert('Komentar berhasil ditambahkan!');
                            // Clear form fields
                            form.querySelector('[name="commentAuthor"]').value = '';
                            form.querySelector('[name="commentText"]').value = '';

                            // Update comments display immediately
                            // Check if 'Belum ada komentar' paragraph exists and remove it
                            const noCommentPara = existingCommentsDiv.querySelector('p:contains("Belum ada komentar")');
                            if (noCommentPara) {
                                noCommentPara.remove();
                            }

                            // Prepend new comment
                            const newCommentHtml = `<p><strong>${htmlspecialchars(commentAuthor)}:</strong> ${htmlspecialchars(commentText)}<br><small>${htmlspecialchars(data.newCommentDate)}</small></p>`;
                            existingCommentsDiv.insertAdjacentHTML('afterbegin', newCommentHtml);
                            
                            // Update comment count
                            commentCountSpan.textContent = parseInt(commentCountSpan.textContent) + 1;

                        } else {
                            alert('Gagal menambahkan komentar: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error adding comment:', error);
                        alert('Terjadi kesalahan saat menambahkan komentar.');
                    }
                }
            });

            // Like button event listeners
            document.querySelectorAll('.like-button').forEach(button => {
                button.addEventListener('click', function() {
                    const itemId = this.dataset.id;
                    const itemType = this.dataset.type;
                    // Mirip index.php, gunakan FormData
                    const formData = new FormData();
                    formData.append('itemId', itemId);
                    formData.append('itemType', itemType);

                    fetch('like_item.php', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const likeCountSpan = this.querySelector('.like-count');
                            likeCountSpan.textContent = data.newLikesCount;
                            this.classList.add('liked');
                        } else {
                            alert('Gagal memperbarui suka: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Terjadi kesalahan jaringan: ' + error.message);
                    });
                });
            });

            // Comment toggle button event listeners
            document.querySelectorAll('.comment-toggle-button').forEach(button => {
                button.addEventListener('click', function() {
                    const itemId = this.dataset.id;
                    const commentsSection = document.getElementById(`comments-section-${itemId}`);
                    if (commentsSection) {
                        commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
                    }
                });
            });

            // Komentar: attach event listener submit pada setiap form komentar
            document.querySelectorAll('.comment-form').forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const li = this.closest('li');
                    const itemId = li.dataset.itemId;
                    const itemType = li.dataset.itemType;
                    const commentText = this.querySelector('textarea').value;
                    const authorName = this.querySelector('input[type="text"]').value;

                    if (!commentText || !authorName) {
                        alert('Nama dan komentar harus diisi.');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('itemId', itemId);
                    formData.append('itemType', itemType);
                    formData.append('commentMessage', commentText);
                    formData.append('commentAuthor', authorName);

                    fetch('add_comment.php', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const newComment = document.createElement('li');
                            newComment.classList.add('comment-item');
                            newComment.innerHTML = `
                                <span class="comment-author">${authorName}</span>
                                <span class="comment-date">Baru saja</span>
                                <p class="comment-text">${commentText}</p>
                            `;
                            this.closest('.comments-section').querySelector('.comments-list').appendChild(newComment);
                            this.querySelector('textarea').value = '';
                        } else {
                            alert('Gagal menambahkan komentar: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Terjadi kesalahan jaringan: ' + error.message);
                    });
                });
            });
        });

        // Scroll reveal animation for all .scroll-reveal elements
        document.addEventListener('DOMContentLoaded', function() {
            const revealEls = document.querySelectorAll('.scroll-reveal');
            const revealOnScroll = (entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            };
            const observer = new IntersectionObserver(revealOnScroll, {
                threshold: 0.13
            });
            revealEls.forEach(el => observer.observe(el));
        });
    </script>
    <script src="script.js"></script>
</body>
</html>