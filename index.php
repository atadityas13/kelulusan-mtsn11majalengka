<?php
date_default_timezone_set('Asia/Jakarta');
// Pesan dari Kepala Madrasah
$kepalaMadrasahMessage = [
    'message' => "Selamat dan sukses untuk seluruh siswa-siswi MTsN 11 Majalengka yang telah menyelesaikan pendidikan dengan baik. Teruslah berjuang meraih impian dan jadilah generasi penerus bangsa yang membanggakan. Ilmu yang kalian dapatkan semoga menjadi bekal untuk masa depan yang lebih cerah. Ingatlah selalu nilai-nilai agama dan budi pekerti luhur. Jaga nama baik almamater dan teruslah berprestasi di jenjang pendidikan berikutnya.",
    'name' => "H. Jajang Gunawan, S.Ag.,M.Pd.I.", // Ganti dengan nama Kepala Madrasah yang sebenarnya
    'position' => "Kepala Madrasah MTsN 11 Majalengka"
];

// Waktu target pengumuman kelulusan (2 Juni 2025, 15:00 WIB)
$targetDate = '2025-06-02 15:00:00';
$currentTime = date('Y-m-d H:i:s'); // Waktu saat ini di server

// Tentukan apakah waktu pengumuman sudah tiba
// Jika waktu saat ini sudah melewati atau sama dengan waktu target, tampilkan formulir/hasil kelulusan
// Jika belum, tampilkan hitung mundur
$showResult = (strtotime($currentTime) >= strtotime($targetDate));

// Inisialisasi variabel untuk penanganan form
$foundStudent = null;
$isGraduated = false;
$errorMessage = '';
$sklDownloadError = ''; // Variabel untuk pesan error SKL

// Ganti file JSON utama menjadi file batch release
$jsonFile = 'data_kelulusan_with_release_time.json';

// Logika penanganan form hanya jika waktu pengumuman sudah tiba
if ($_SERVER["REQUEST_METHOD"] == "POST" && $showResult) {
    $nomorPesertaInput = trim($_POST['nomorPeserta'] ?? '');
    $nisnInput = trim($_POST['nisn'] ?? '');
    $tanggalLahirInput = trim($_POST['tanggalLahir'] ?? '');

    $foundStudent = null;
    $isGraduated = false;
    $errorMessage = '';
    $sklDownloadError = '';
    $batchReleaseNotYet = false;
    $batchReleaseTime = '';

    if (file_exists($jsonFile)) {
        $jsonData = file_get_contents($jsonFile);
        $students = json_decode($jsonData, true);

        if (is_array($students)) {
            foreach ($students as $student) {
                if ($student['nomor_peserta'] == $nomorPesertaInput &&
                    (string)$student['nisn'] == $nisnInput &&
                    $student['tanggal_lahir'] == $tanggalLahirInput) {
                    $foundStudent = $student;
                    $isGraduated = ($student['status_kelulusan'] == 'Lulus');
                    // Cek batch release
                    $batchReleaseTime = $student['release_timestamp'] ?? null;
                    if ($batchReleaseTime && strtotime($currentTime) < strtotime($batchReleaseTime)) {
                        $batchReleaseNotYet = true;
                    }
                    break;
                }
            }
        } else {
            $errorMessage = "Error: Data siswa tidak valid atau kosong.";
        }
    } else {
        $errorMessage = "Error: File data_kelulusan_with_release_time.json tidak ditemukan.";
    }

    if ($foundStudent === null && empty($errorMessage)) {
        $errorMessage = "Maaf, kombinasi Nomor Peserta, NISN, dan Tanggal Lahir yang Anda masukkan tidak ditemukan dalam data kami.";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !$showResult) {
    // Jika form disubmit sebelum waktu target
    $errorMessage = "Pengumuman kelulusan baru akan dibuka pada " . date('d F Y', strtotime($targetDate)) . " pukul " . date('H:i', strtotime($targetDate)) . " WIB. Mohon ditunggu.";
    // Pastikan $showResult tetap false agar hitung mundur tetap terlihat
    $showResult = false;
}

// Membaca pesan guru (akan selalu ditampilkan di sidebar)
$teacherMessages = [];
$jsonFileTeacherMessages = 'teacher_messages.json';
if (file_exists($jsonFileTeacherMessages)) {
    $jsonDataTeacherMessages = file_get_contents($jsonFileTeacherMessages);
    $teacherMessages = json_decode($jsonDataTeacherMessages, true);
    if (!is_array($teacherMessages)) {
        $teacherMessages = [];
    }
    // Urutkan pesan berdasarkan tanggal terbaru
    usort($teacherMessages, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

// Membaca dan menampilkan testimoni terbaru (akan selalu ditampilkan di sidebar)
$testimonials = [];
$testimonialFile = 'testimonials.json';
if (file_exists($testimonialFile)) {
    $testimonials = json_decode(file_get_contents($testimonialFile), true);
    if (!is_array($testimonials)) {
        $testimonials = [];
    }
    // Urutkan testimoni berdasarkan tanggal terbaru
    usort($testimonials, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}

// Membaca dan menampilkan riwayat pengecekan terbaru (akan selalu ditampilkan di sidebar)
$checkHistory = [];
$historyFile = 'check_history.json';
if (file_exists($historyFile)) {
    $checkHistory = json_decode(file_get_contents($historyFile), true);
    if (!is_array($checkHistory)) {
        $checkHistory = [];
    }
    // Riwayat sudah diurutkan (terbaru di atas) saat disimpan, jadi tidak perlu usort lagi
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman Kelulusan MTsN 11 Majalengka</title>
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Gaya untuk foto kepala madrasah */
        .head-teacher-photo {
            text-align: center;
            margin-bottom: 15px;
        }
        .head-teacher-photo .photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007bff;
        }
        /* Gaya untuk pesan notifikasi */
        .download-notice {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            background-color: #f8d7da; /* Warna latar belakang untuk error/peringatan */
            color: #721c24; /* Warna teks untuk error/peringatan */
            border: 1px solid #f5c6cb;
            text-align: center;
        }
        .info-download-skL {
            margin-top: 15px;
            font-size: 0.9em;
            color: #555;
            text-align: center;
        }
        /* Gaya untuk tombol download yang dinonaktifkan */
        .btn-download.disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Gaya tambahan untuk tampilan countdown */
        .countdown-section {
            background-color: #f0f8ff; /* Warna latar belakang biru muda */
            border: 1px solid #cce5ff;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .countdown-section h2 {
            color: #0056b3;
            margin-bottom: 15px;
            font-size: 1.8em;
        }
        .countdown-timer {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap; /* Agar responsif di layar kecil */
        }
        .countdown-item {
            background-color: #007bff;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            min-width: 90px;
            font-size: 2em;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
            flex-grow: 1; /* Agar item mengisi ruang */
            max-width: 120px; /* Batasi lebar maksimum item */
        }
        .countdown-item span {
            display: block;
            font-size: 0.5em;
            font-weight: normal;
            margin-top: 5px;
            opacity: 0.8;
        }
        .countdown-message {
            margin-top: 20px;
            font-size: 1.1em;
            color: #333;
        }

        .like-button {
            background: #fff;
            border: 1px solid #e0e0e0;
            color: #e74c3c;
            border-radius: 6px;
            padding: 6px 14px 6px 10px;
            font-size: 1em;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            cursor: pointer;
            margin-right: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .like-button .fa-heart {
            color: #e74c3c;
            font-size: 1.1em;
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
        }
        .like-button .like-label {
            color: #888;
            font-size: 0.95em;
            margin-left: 2px;
        }
        .like-button:hover, .like-button.liked {
            background: #ffeaea;
            color: #c0392b;
            border-color: #e74c3c;
        }

        /* Tambahkan CSS animasi scroll reveal */
        .scroll-reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.7s cubic-bezier(.22,1,.36,1);
            will-change: opacity, transform;
        }
        .scroll-reveal.is-visible {
            opacity: 1;
            transform: none;
        }

        /* Modal warning styles */
        .modal-warning-overlay {
            position: fixed;
            z-index: 9999;
            left: 0; top: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.55);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-warning {
            background: #fff;
            border-radius: 10px;
            max-width: 480px;
            width: 95%;
            padding: 32px 22px 22px 22px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            text-align: left;
            position: relative;
            animation: modalIn 0.3s;
            box-sizing: border-box;
        }
        @media (max-width: 600px) {
            .modal-warning {
                max-width: 98vw;
                width: 98vw;
                padding: 18px 7vw 18px 7vw;
                font-size: 0.98em;
            }
            .modal-warning h2 {
                font-size: 1em;
            }
            .modal-warning .btn-modal-confirm {
                font-size: 0.98em;
                padding: 9px 0;
                width: 100%;
            }
            .modal-warning ol {
                margin-left: 14px;
            }
        }
        @media (max-width: 400px) {
            .modal-warning {
                padding: 10px 2vw 10px 2vw;
                font-size: 0.95em;
            }
            .modal-warning h2 {
                font-size: 0.95em;
            }
        }
        .modal-warning h2 {
            margin-top: 0;
            color: #c0392b;
            font-size: 1.25em;
            margin-bottom: 12px;
            font-weight: bold;
            word-break: break-word;
        }
        .modal-warning ol {
            margin: 0 0 0 18px;
            padding: 0;
        }
        .modal-warning li {
            margin-bottom: 7px;
            font-size: 1em;
            word-break: break-word;
        }
        .modal-warning .modal-warning-note {
            margin-top: 12px;
            font-size: 0.98em;
            color: #b71c1c;
            font-weight: 500;
            word-break: break-word;
        }
        .modal-warning .btn-modal-confirm {
            margin-top: 18px;
            background: #c0392b;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 28px;
            font-size: 1em;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .modal-warning .btn-modal-confirm:hover {
            background: #a93226;
        }
        .modal-warning-checkbox-group {
            margin-top: 16px;
            margin-bottom: 0;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 0.98em;
        }
        .modal-warning-checkbox-group input[type="checkbox"] {
            margin-top: 3px;
            accent-color: #c0392b;
            width: 18px;
            height: 18px;
        }
        .modal-warning .btn-modal-confirm:disabled {
            background: #ccc;
            color: #888;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Modal Cek Nomor Peserta & NISN */
        .modal-nopes-overlay, .modal-nisn-overlay {
            position: fixed;
            z-index: 9999;
            left: 0; top: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.45);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-nopes, .modal-nisn {
            background: #fff;
            border-radius: 10px;
            max-width: 420px;
            width: 95%;
            padding: 28px 18px 18px 18px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            text-align: left;
            position: relative;
            animation: modalIn 0.3s;
            box-sizing: border-box;
        }
        .modal-nopes h2, .modal-nisn h2 {
            margin-top: 0;
            color: #007bff;
            font-size: 1.15em;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .modal-nopes .close-modal-nopes, .modal-nisn .close-modal-nisn {
            position: absolute;
            top: 10px; right: 16px;
            background: none;
            border: none;
            font-size: 1.3em;
            color: #888;
            cursor: pointer;
        }
        .modal-nopes ul, .modal-nisn ul {
            margin: 0 0 0 18px;
            padding: 0;
            font-size: 1em;
        }
        .modal-nopes li, .modal-nisn li {
            margin-bottom: 7px;
        }
        .btn-cek-nopes, .btn-cek-nisn {
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 18px;
            font-size: 1em;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 10px;
            margin-top: 6px;
            transition: background 0.2s;
        }
        .btn-cek-nopes:hover, .btn-cek-nisn:hover {
            background: #0056b3;
        }
        .modal-nopes-result, .modal-nisn-result {
            margin-top: 10px;
            font-size: 1em;
            color: #007b55;
            font-weight: bold;
            word-break: break-all;
        }
        .modal-nopes-error, .modal-nisn-error {
            margin-top: 10px;
            font-size: 1em;
            color: #c0392b;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Modal Cek Nomor Peserta -->
    <div class="modal-nopes-overlay" id="modal-nopes-overlay" style="display:none;">
        <div class="modal-nopes">
            <button class="close-modal-nopes" id="close-modal-nopes" title="Tutup">&times;</button>
            <h2>Cek Nomor Peserta Ujian</h2>
            <form id="form-cek-nopes" autocomplete="off">
                <label for="nopes-nisn">NISN:</label>
                <input type="text" id="nopes-nisn" name="nisn" required style="width:98%;margin-bottom:8px;">
                <label for="nopes-tgl">Tanggal Lahir:</label>
                <input type="date" id="nopes-tgl" name="tanggal_lahir" required style="width:98%;margin-bottom:8px;">
                <button type="submit" class="btn-cek-nopes">Cek Nomor Peserta</button>
            </form>
            <div class="modal-nopes-result" id="modal-nopes-result"></div>
            <div class="modal-nopes-error" id="modal-nopes-error"></div>
            <div style="margin-top:12px;font-size:0.98em;color:#b71c1c;">
                Jika masih kesulitan, silakan hubungi panitia kelulusan di madrasah.
            </div>
        </div>
    </div>
    <!-- Modal Cek NISN -->
    <div class="modal-nisn-overlay" id="modal-nisn-overlay" style="display:none;">
        <div class="modal-nisn">
            <button class="close-modal-nisn" id="close-modal-nisn" title="Tutup">&times;</button>
            <h2>Cek NISN</h2>
            <form id="form-cek-nisn" autocomplete="off">
                <label for="nisn-nama">Nama Lengkap:</label>
                <input type="text" id="nisn-nama" name="nama" required style="width:98%;margin-bottom:8px;">
                <label for="nisn-tgl">Tanggal Lahir:</label>
                <input type="date" id="nisn-tgl" name="tanggal_lahir" required style="width:98%;margin-bottom:8px;">
                <button type="submit" class="btn-cek-nisn">Cek NISN</button>
            </form>
            <div class="modal-nisn-result" id="modal-nisn-result"></div>
            <div class="modal-nisn-error" id="modal-nisn-error"></div>
            <div style="margin-top:12px;font-size:0.98em;color:#b71c1c;">
                Jika masih kesulitan, silakan hubungi panitia kelulusan di madrasah.
            </div>
        </div>
    </div>
    <!-- Modal Warning -->
    <div class="modal-warning-overlay" id="modal-warning-overlay">
        <div class="modal-warning">
            <h2>HIMBAUAN DALAM MENYIKAPI PENGUMUMAN KELULUSAN</h2>
            <ol>
                <li>Membuka link kelulusan sesuai jam yang ditentukan;</li>
                <li>Mensyukuri karunia Alloh SWT atas kelulusan yang diperoleh;</li>
                <li>Tidak melakukan konvoi, hura hura, kumpul bareng merayakan kelulusan;</li>
                <li>Dilarang melakukan coret-coret pakaian seragam atau apapun;</li>
                <li>Dilarang melakukan vandalisme (mencoret coret pasilitas umum).</li>
            </ol>
            <div class="modal-warning-note">
                Jika melanggar himbauan tersebut maka:<br>
                a. Kelulusan akan dibatalkan karena melanggar nilai akhlak (minimal B);<br>
                b. Proses pendaftaran ke sekolah menengah atas tidak akan diproses akun PPDB-nya tidak akan diaktifkan;<br>
                c. Ijazah tidak akan diberikan.
            </div>
            <div class="modal-warning-checkbox-group">
                <input type="checkbox" id="modal-warning-checkbox">
                <label for="modal-warning-checkbox" style="user-select: none;">
                    Saya menyatakan dengan sungguh-sungguh bahwa saya akan menaati semua himbauan, dan apabila melanggar saya siap menanggung segala konsekuensinya.
                </label>
            </div>
            <button class="btn-modal-confirm" id="btn-modal-confirm" disabled>Mengerti</button>
        </div>
    </div>
    <canvas id="confetti-canvas"></canvas>
    <div id="app">
        <header class="header">
            <img src="assets/mtsn11majalengka-logo.png" alt="Logo MTsN 11 Majalengka" class="logo">
            <h1>Pengumuman Kelulusan <br> MTsN 11 Majalengka</h1>
            <p class="tagline">Lulusan Tahun Pelajaran 2024/2025</p>
        </header>

        <main class="main-content">
            <div class="content-wrapper">
                <div class="main-column">
                    <?php if (!$showResult) { ?>
                        <section id="countdown-section" class="card fade-in scroll-reveal">
                            <h2>Pengumuman Kelulusan Belum Dibuka!</h2>
                            <p class="countdown-message">
                                Mohon bersabar, masih menunggu hasil rapat dewan Guru. <br>Hasil kelulusan akan tersedia pada tanggal<br> <strong><?php echo date('d F Y', strtotime($targetDate)); ?></strong> pukul <strong><?php echo date('H:i', strtotime($targetDate)); ?> WIB</strong>.
                            </p>
                            <h2>Sisa Waktu :</h2>
                            <div class="countdown-timer" id="countdown-timer">
                                <div class="countdown-item">
                                    <span id="days">00</span>
                                    <span>Hari</span>
                                </div>
                                <div class="countdown-item">
                                    <span id="hours">00</span>
                                    <span>Jam</span>
                                </div>
                                <div class="countdown-item">
                                    <span id="minutes">00</span>
                                    <span>Menit</span>
                                </div>
                                <div class="countdown-item">
                                    <span id="seconds">00</span>
                                    <span>Detik</span>
                                </div>
                            </div>
                            <p class="info-text" style="margin-top: 20px;">Jika halaman tidak otomatis menampilkan formulir pengecekan saat waktu tiba, <br><strong>silahkan refresh halaman.</strong></p>
                            <?php if (!empty($errorMessage)): // Menampilkan error jika form disubmit sebelum waktu ?>
                                <p class="message-danger"><?php echo htmlspecialchars($errorMessage); ?></p>
                            <?php endif; ?>
                        </section>
                        <?php if (!$foundStudent): ?>
                        <section id="input-section" class="card fade-in scroll-reveal" style="display: none;">
                            <h2>Cek Status Kelulusanmu!</h2>
                            <div class="illustration-container">
                                <img src="assets/graduation_illustration.png" alt="Ilustrasi Siswa Belajar">
                            </div>

                            <!-- Tombol cek nomor peserta dan NISN -->
                            <button type="button" class="btn-cek-nopes" id="btn-cek-nopes">Cek Nomor Peserta?</button>
                            <button type="button" class="btn-cek-nisn" id="btn-cek-nisn">Cek NISN?</button>

                            <form action="index.php" method="POST" id="graduation-form">
                                <div class="form-group">
                                    <label for="nomorPeserta">Nomor Peserta Ujian:</label>
                                    <input type="text" id="nomorPeserta" name="nomorPeserta" placeholder="Contoh: 25-10-10-2-0089-0001" required>
                                </div>
                                <div class="form-group">
                                    <label for="nisn">NISN:</label>
                                    <input type="text" id="nisn" name="nisn" placeholder="Contoh: 0098765432" required>
                                </div>
                                <div class="form-group">
                                    <label for="tanggalLahir">Tanggal Lahir:</label>
                                    <input type="date" id="tanggalLahir" name="tanggalLahir" required>
                                </div>
                                <button type="submit" class="btn-check">Cek Kelulusan</button>
                            </form>
                            <p class="info-text">Masukkan data dengan benar untuk mengetahui status kelulusan Anda.</p>
                            <?php if (empty($errorMessage) && $_SERVER["REQUEST_METHOD"] != "POST"): // Hanya tampilkan error awal jika tidak ada form submit ?>
                                <p class="message-danger"><?php echo htmlspecialchars($errorMessage); ?></p>
                            <?php endif; ?>
                        </section>
                        <section class="testimoni-section card fade-in scroll-reveal">
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
                        </section>
                        <?php endif; ?>
                    <?php } else { // Tampilkan formulir dan hasil kelulusan jika waktu sudah tiba ?>

                        <?php if (!$foundStudent): ?>
                            <section id="input-section" class="card fade-in scroll-reveal">
                                <h2>Cek Status Kelulusanmu!</h2>
                                <div class="illustration-container">
                                    <img src="assets/graduation_illustration.png" alt="Ilustrasi Siswa Belajar">
                                </div>
                                <!-- Tombol cek nomor peserta dan NISN -->
                                <button type="button" class="btn-cek-nopes" id="btn-cek-nopes">Cek Nomor Peserta?</button>
                                <button type="button" class="btn-cek-nisn" id="btn-cek-nisn">Cek NISN?</button>
                                <form action="index.php" method="POST" id="graduation-form">
                                    <div class="form-group">
                                        <label for="nomorPeserta">Nomor Peserta Ujian:</label>
                                        <input type="text" id="nomorPeserta" name="nomorPeserta" placeholder="Contoh: 25-10-10-2-0089-0001" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="nisn">NISN:</label>
                                        <input type="text" id="nisn" name="nisn" placeholder="Contoh: 0098765432" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggalLahir">Tanggal Lahir:</label>
                                        <input type="date" id="tanggalLahir" name="tanggalLahir" required>
                                    </div>
                                    <button type="submit" class="btn-check">Cek Kelulusan</button>
                                </form>
                                <p class="info-text">Masukkan data dengan benar untuk mengetahui status kelulusan Anda.</p>
                                <?php if (!empty($errorMessage)): ?>
                                    <p class="message-danger"><?php echo htmlspecialchars($errorMessage); ?></p>
                                <?php endif; ?>
                            </section>
                            <section class="testimoni-section card fade-in scroll-reveal">
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
                                    </section>
                        <?php endif; ?>
                        <?php
                        // Tambahkan pengecekan batch release sebelum menampilkan hasil kelulusan
                        if ($showResult && $foundStudent) {
                            if (!empty($batchReleaseNotYet) && !empty($batchReleaseTime)) {
                                // Jika batch release belum waktunya
                                ?>
                                <section id="result-section" class="card fade-in scroll-reveal" style="margin-top: 20px;">
                                    <h2 class="status-tidak-ditemukan">Tunggu Antrian!</h2>
                                    <p class="message-danger">
                                        Silahkan menunggu giliran anda pada
                                        </strong> pukul <strong><?php echo date('H:i', strtotime($batchReleaseTime)); ?> WIB</strong>.<br>
                                        Silakan cek kembali setelah waktu tersebut.
                                    </p>
                                    <button class="btn-back" onclick="window.location.href='index.php'">Cek Lagi</button>
                                </section>
                                <?php
                            } else {
                                // Tampilkan hasil kelulusan seperti biasa
                                ?>
                                <section id="result-section" class="card fade-in scroll-reveal" style="margin-top: 20px;">
                                    <div class="result-content">
                                        <?php if ($isGraduated) { ?>
                                            <h2 class="status-lulus">Selamat!<br> <?php echo htmlspecialchars($foundStudent['nama']); ?></h2>
                                            <?php
                                            // Format tanggal lahir ke dd-mm-yyyy
                                            $tanggalLahir = $foundStudent['tanggal_lahir'];
                                            $dateObj = DateTime::createFromFormat('Y-m-d', $tanggalLahir);
                                            $formattedTanggalLahir = $dateObj ? $dateObj->format('d-m-Y') : htmlspecialchars($tanggalLahir);
                                            ?>
                                            <p class="student-info"><span class="info-label">Nomor Peserta</span> <span class="info-value"><strong>: <?php echo htmlspecialchars($foundStudent['nomor_peserta']); ?></strong></span></p>
                                            <p class="student-info"><span class="info-label">NISN</span> <span class="info-value"><strong>: <?php echo htmlspecialchars($foundStudent['nisn']); ?></strong></span></p>
                                            <p class="student-info"><span class="info-label">TTL</span> <span class="info-value"><strong>: <?php echo htmlspecialchars($foundStudent['tempat_lahir']); ?></strong>, <strong><?php echo $formattedTanggalLahir; ?></strong></span></p>
                                            <p class="student-info"><span class="info-label">Jenis Kelamin</span> <span class="info-value"><strong>: <?php echo htmlspecialchars($foundStudent['jenis_kelamin']); ?></strong></span></p>
                                            <p class="student-info"><span class="info-label">Kelas</span> <span class="info-value"><strong>: <?php echo htmlspecialchars($foundStudent['kelas']); ?></strong></span></p>
                                            <p class="status-text">Anda dinyatakan <strong>LULUS</strong> dari MTsN 11 Majalengka.</p>
                                            <p class="message-success">Kami bangga dengan pencapaian Anda! Teruslah belajar dan raih cita-cita Anda.<br>Rayakan kelulusan dengan bijak, silahkan luapkan rasa syukur anda dan berbagi kebahagiaan dengan mengisi pesan dan kesan!</p>

                                            <?php
                                            // Penyesuaian format nama file SKL
                                            $nisn = $foundStudent['nisn'];
                                            $nama = strtoupper(str_replace(['.',','], '', $foundStudent['nama'])); // Hilangkan karakter aneh
                                            $namaFile = preg_replace('/\s+/', ' ', $nama); // Normalisasi spasi
                                            $namaFile = str_replace(' ', '_', $namaFile); // Ganti spasi dengan underscore untuk nama file
                                            $sklFileName = "SKL_{$nisn}-{$namaFile}.pdf"; // Penamaan: SKL_NISN-NAMA.pdf
                                            $sklPath = "assets/skl/{$sklFileName}";
                                            $downloadFileName = $sklFileName;

                                            if (file_exists($sklPath)) {
                                                echo '<a href="' . htmlspecialchars($sklPath) . '" class="btn-download" download="' . htmlspecialchars($downloadFileName) . '">Unduh Surat Keterangan Lulus (PDF)</a>';
                                            } else {
                                                $sklDownloadError = "Surat Keterangan Lulus belum di-generate, coba lagi nanti.";
                                                echo '<span class="btn-download disabled">Unduh Surat Keterangan Lulus (PDF)</span>';
                                                echo '<p class="download-notice">' . htmlspecialchars($sklDownloadError) . '</p>';
                                            }
                                            ?>
                                            <p class="info-download-skL">Surat Keterangan Lulus (fisik) dapat diambil di Madrasah pada saat jam kerja.</p>

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

                                            <div class="student-testimonial-form-section">
                                                <h3>Bagikan Pesan dan Kesanmu!</h3>
                                                <form id="student-testimonial-form" class="good-form">
                                                    <p class="form-description">Kami akan sangat senang mendengar kesan dan pesan Anda selama belajar di MTsN 11 Majalengka.</p>
                                                    <input type="hidden" id="studentNameForTestimonial" name="studentName" value="<?php echo htmlspecialchars($foundStudent['nama']); ?>">
                                                    <div class="form-group">
                                                        <label for="testimonialMessage">Pesan dan Kesan Anda:</label>
                                                        <textarea id="testimonialMessage" name="testimonialMessage" rows="5" placeholder="Tulis pesan dan kesan Anda di sini..." required></textarea>
                                                    </div>
                                                    <button type="submit" class="btn-submit-testimonial">Kirim Kesan & Pesan</button>
                                                    <div id="testimonial-response" class="info-text" style="margin-top: 10px;"></div>
                                                </form>
                                            </div>

                                            <script>
                                                // Memanggil fungsi JS setelah DOM dimuat (untuk confetti dan save history)
                                                document.addEventListener('DOMContentLoaded', () => {
                                                    startConfetti(); // Efek confetti hanya untuk yang LULUS
                                                    saveCheckHistory(
                                                        "<?php echo htmlspecialchars($foundStudent['nomor_peserta']); ?>",
                                                        "<?php echo htmlspecialchars($foundStudent['nama']); ?>",
                                                        "<?php echo htmlspecialchars($foundStudent['status_kelulusan']); ?>"
                                                    );
                                                });
                                            </script>

                                        <?php } elseif (!$isGraduated) { ?>
                                            <h2 class="status-tidak-lulus">Mohon Maaf, <?php echo htmlspecialchars($foundStudent['nama']); ?></h2>
                                            <p class="status-text">Berdasarkan pertimbangan pada rapat dewan guru, Anda <strong>BELUM DINYATAKAN LULUS/DITANGGUHKAN</strong> dari MTsN 11 Majalengka.</p>
                                            <p class="message-danger">Untuk informasi lebih lanjut mengenai status Anda, silakan hubungi Wakil Kepala Madrasah bidang Kurikulum dan atau Wali Kelas pada jam kerja.</p>
                                            <p class="message-info">Terus semangat dan jangan menyerah!</p>
                                            <script>
                                                document.addEventListener('DOMContentLoaded', () => {
                                                    saveCheckHistory(
                                                        "<?php echo htmlspecialchars($foundStudent['nomor_peserta']); ?>",
                                                        "<?php echo htmlspecialchars($foundStudent['nama']); ?>",
                                                        "Tidak Lulus"
                                                    );
                                                });
                                            </script>
                                        <?php } ?>
                                        <button class="btn-back" onclick="window.location.href='index.php'">Cek Lagi</button>
                                    </div>
                                </section>
                                <?php
                            }
                        } elseif ($showResult && !$foundStudent && !empty($errorMessage)) { ?>
                            <section id="result-section" class="card fade-in scroll-reveal" style="margin-top: 20px;">
                                <h2 class="status-tidak-ditemukan">Data Tidak Ditemukan 😔</h2>
                                <p class="message-danger"><?php echo htmlspecialchars($errorMessage); ?></p>
                                <p class="message-info">Pastikan Anda telah memasukkan data dengan benar.</p>
                                <p class="message-info">Jika Anda yakin data yang dimasukkan benar, mohon hubungi pihak MTsN 11 Majalengka untuk verifikasi.</p>
                                <script>
                                    document.addEventListener('DOMContentLoaded', () => {
                                        // Tidak menyimpan riwayat untuk "data tidak ditemukan"
                                    });
                                </script>
                                <button class="btn-back" onclick="window.location.href='index.php'">Cek Lagi</button>
                            </section>
                        } ?>
                    <?php } ?>
                <?php } // <-- Tambahkan penutup untuk blok if utama ?>
                </div>
                <div class="sidebar-column">
                    <section class="teacher-message-display-section card fade-in scroll-reveal">
                        <h2>Pesan dari Guru</h2>
                        <?php if (!empty($teacherMessages)): ?>
                            <ul class="teacher-message-list-public">
                                <?php foreach ($teacherMessages as $msg):
                                    $messageId = htmlspecialchars($msg['id'] ?? '');
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
                                                data-type="teacher_message">
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
                                                <?php endif; ?>
                                            </ul>
                                            <form class="comment-form" onsubmit="addComment(event, '<?php echo $messageId; ?>', 'teacher_message')">
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
                    <section class="testimonial-display-section card fade-in scroll-reveal">
                        <h2>Pesan dan Kesan Siswa</h2>
                        <?php if (!empty($testimonials)): ?>
                            <ul id="testimonial-list-public-live" class="testimonial-list-public">
                                <?php foreach ($testimonials as $t):
                                    $testimonialId = htmlspecialchars($t['id'] ?? '');
                                    $likesCount = $t['likes'] ?? 0;
                                    $comments = $t['comments'] ?? [];
                                ?>
                                    <li class="testimonial-list-public-item scroll-reveal" data-item-id="<?php echo $testimonialId; ?>" data-item-type="testimonial">
                                        <span class="date"><?php echo date('d M Y H:i', strtotime($t['date'])); ?></span>
                                        <p>"<?php echo htmlspecialchars($t['message']); ?>"</p>
                                        <span class="author">- <?php echo htmlspecialchars($t['name']); ?></span>
                                        <div class="feedback-actions">
                                            <button class="like-button"
                                                data-id="<?php echo $testimonialId; ?>"
                                                data-type="testimonial">
                                                <i class="fa-solid fa-heart"></i>
                                                <span class="like-count"><?php echo $likesCount; ?></span>
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
                            <p id="no-testimonials-msg" style="text-align: center; color: #777;">Belum ada pesan dan kesan dari siswa lain.</p>
                        <?php endif; ?>
                    </section>
                    <section class="check-history-section card fade-in scroll-reveal">
                        <h2>Riwayat Pengecekan Terbaru</h2>
                        <ul class="check-history-list">
                            <?php if (!empty($checkHistory)): ?>
                                <?php foreach ($checkHistory as $entry): ?>
                                    <li class="check-history-item scroll-reveal">
                                        <span class="timestamp"><?php echo date('d M Y H:i', strtotime($entry['timestamp'])); ?></span>
                                        <span class="details">No. Peserta: <strong><?php echo htmlspecialchars($entry['nomor_peserta']); ?></strong></span><br>
                                        <span class="details">Nama: <strong><?php echo htmlspecialchars($entry['student_name']); ?></strong> telah mengecek kelulusan</span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="text-align: center; color: #777;">Belum ada riwayat pengecekan.</p>
                            <?php endif; ?>
                        </ul>
                    </section>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> MTsN 11 Majalengka. Semua Hak Dilindungi. Developed by A.T. Aditya</p>
            <div class="social-links">
            </div>
        </footer>
    </div>
    <script>
        const targetDate = "<?php echo $targetDate; ?>";
        const isPageReady = <?php echo $showResult ? 'true' : 'false'; ?>;
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

        // Modal warning logic
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('modal-warning-overlay');
            var btn = document.getElementById('btn-modal-confirm');
            var checkbox = document.getElementById('modal-warning-checkbox');
            if (modal && btn && checkbox) {
                btn.disabled = !checkbox.checked;
                checkbox.addEventListener('change', function() {
                    btn.disabled = !checkbox.checked;
                });
                btn.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            }

            // Modal Cek Nomor Peserta
            var btnNopes = document.getElementById('btn-cek-nopes');
            var modalNopes = document.getElementById('modal-nopes-overlay');
            var closeNopes = document.getElementById('close-modal-nopes');
            if (btnNopes && modalNopes && closeNopes) {
                btnNopes.addEventListener('click', function() {
                    modalNopes.style.display = 'flex';
                });
                closeNopes.addEventListener('click', function() {
                    modalNopes.style.display = 'none';
                });
                modalNopes.addEventListener('click', function(e) {
                    if (e.target === modalNopes) modalNopes.style.display = 'none';
                });
            }

            // Modal Cek NISN
            var btnNisn = document.getElementById('btn-cek-nisn');
            var modalNisn = document.getElementById('modal-nisn-overlay');
            var closeNisn = document.getElementById('close-modal-nisn');
            if (btnNisn && modalNisn && closeNisn) {
                btnNisn.addEventListener('click', function() {
                    modalNisn.style.display = 'flex';
                });
                closeNisn.addEventListener('click', function() {
                    modalNisn.style.display = 'none';
                });
                modalNisn.addEventListener('click', function(e) {
                    if (e.target === modalNisn) modalNisn.style.display = 'none';
                });
            }

            // AJAX Cek Nomor Peserta (ambil dari data_kelulusan_with_release_time.json)
            var formCekNopes = document.getElementById('form-cek-nopes');
            if (formCekNopes) {
                formCekNopes.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var nisn = document.getElementById('nopes-nisn').value.trim();
                    var tgl = document.getElementById('nopes-tgl').value.trim();
                    var result = document.getElementById('modal-nopes-result');
                    var error = document.getElementById('modal-nopes-error');
                    result.textContent = '';
                    error.textContent = '';
                    fetch('data_kelulusan_with_release_time.json')
                        .then(res => res.json())
                        .then(data => {
                            var found = data.find(s =>
                                String(s.nisn) === nisn &&
                                s.tanggal_lahir === tgl
                            );
                            if (found) {
                                result.textContent = "Nomor Peserta Anda: " + found.nomor_peserta;
                            } else {
                                error.textContent = "Data tidak ditemukan. Pastikan NISN dan tanggal lahir benar.";
                            }
                        })
                        .catch(() => {
                            error.textContent = "Gagal membaca data.";
                        });
                });
            }

            // AJAX Cek NISN (ambil dari data_kelulusan_with_release_time.json)
            var formCekNisn = document.getElementById('form-cek-nisn');
            if (formCekNisn) {
                formCekNisn.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var nama = document.getElementById('nisn-nama').value.trim().toUpperCase();
                    var tgl = document.getElementById('nisn-tgl').value.trim();
                    var result = document.getElementById('modal-nisn-result');
                    var error = document.getElementById('modal-nisn-error');
                    result.textContent = '';
                    error.textContent = '';
                    fetch('data_kelulusan_with_release_time.json')
                        .then(res => res.json())
                        .then(data => {
                            var found = data.find(s =>
                                s.nama.toUpperCase() === nama &&
                                s.tanggal_lahir === tgl
                            );
                            if (found) {
                                result.textContent = "NISN Anda: " + found.nisn;
                            } else {
                                error.textContent = "Data tidak ditemukan. Pastikan nama lengkap dan tanggal lahir benar.";
                            }
                        })
                        .catch(() => {
                            error.textContent = "Gagal membaca data.";
                        });
                });
            }
        });
    </script>
    <script src="script.js"></script>
</body>
</html>