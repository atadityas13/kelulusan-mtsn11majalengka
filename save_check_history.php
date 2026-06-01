<?php
date_default_timezone_set('Asia/Jakarta'); // Mengatur zona waktu ke WIB

header('Content-Type: application/json'); // Beritahu browser bahwa response ini adalah JSON

$response = ['success' => false, 'message' => 'Terjadi kesalahan.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari POST request
    $studentName = trim($_POST['studentName'] ?? '');
    $checkResult = trim($_POST['checkResult'] ?? ''); // 'Lulus' atau 'Tidak Lulus'
    $nomorPeserta = trim($_POST['nomorPeserta'] ?? '');

    // Validasi input sederhana
    if (empty($studentName) || empty($checkResult) || empty($nomorPeserta)) {
        $response['message'] = 'Data riwayat tidak lengkap.';
    } else {
        $historyFile = 'check_history.json';
        $currentHistory = [];

        // Baca data riwayat yang sudah ada
        if (file_exists($historyFile)) {
            $jsonData = file_get_contents($historyFile);
            $currentHistory = json_decode($jsonData, true);
            if (!is_array($currentHistory)) {
                $currentHistory = []; // Pastikan ini adalah array jika ada masalah decoding
            }
        }

        // Buat data riwayat baru
        $newEntry = [
            'timestamp' => date('Y-m-d H:i:s'), // Sekarang akan menggunakan Asia/Jakarta
            'nomor_peserta' => htmlspecialchars($nomorPeserta, ENT_QUOTES, 'UTF-8'),
            'student_name' => htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'),
            'result' => htmlspecialchars($checkResult, ENT_QUOTES, 'UTF-8')
        ];

        // Tambahkan entri baru ke array (di awal array agar yang terbaru di atas)
        array_unshift($currentHistory, $newEntry); // Menambahkan ke awal array

        // Batasi jumlah entri, misalnya 100 entri terbaru
        $currentHistory = array_slice($currentHistory, 0, 100);

        // Simpan kembali ke file JSON
        if (file_put_contents($historyFile, json_encode($currentHistory, JSON_PRETTY_PRINT))) {
            $response['success'] = true;
            $response['message'] = 'Riwayat berhasil disimpan.';
        } else {
            $response['message'] = 'Gagal menyimpan riwayat ke file.';
        }
    }
} else {
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
?>