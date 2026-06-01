<?php
date_default_timezone_set('Asia/Jakarta'); // Mengatur zona waktu ke WIB

header('Content-Type: application/json'); // Beritahu browser bahwa response ini adalah JSON

$response = ['success' => false, 'message' => 'Terjadi kesalahan.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari POST request
    $studentName = trim($_POST['studentName'] ?? '');
    $testimonialMessage = trim($_POST['testimonialMessage'] ?? '');
    
    // Validasi input sederhana
    if (empty($studentName) || empty($testimonialMessage)) {
        $response['message'] = 'Nama dan Testimoni tidak boleh kosong.';
    } else {
        $testimonialFile = 'testimonials.json';
        $currentTestimonials = [];

        // Baca data testimoni yang sudah ada
        if (file_exists($testimonialFile)) {
            $jsonData = file_get_contents($testimonialFile);
            $currentTestimonials = json_decode($jsonData, true);
            // Pastikan $currentTestimonials adalah array jika ada masalah decoding
            if (!is_array($currentTestimonials)) {
                $currentTestimonials = [];
            }
        }

        // --- Perubahan dimulai di sini ---
        // Buat ID unik untuk testimoni baru
        // Anda bisa menggunakan fungsi uniqid() yang lebih sederhana, atau UUID yang lebih robust
        // Untuk contoh ini, saya gunakan uniqid()
        $newId = 'ts-' . uniqid(); // Contoh: ts-66572f3e8b0a9

        // Buat data testimoni baru
        $newTestimonial = [
            'id' => $newId, // <<< TAMBAHKAN ID UNIK DI SINI
            'name' => htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'),
            'message' => htmlspecialchars($testimonialMessage, ENT_QUOTES, 'UTF-8'),
            'date' => date('Y-m-d H:i:s'), // Tanggal dan waktu saat ini
            'likes' => 0, // <<< INISIASI JUMLAH LIKES DENGAN 0
            'comments' => [] // <<< INISIASI ARRAY COMMENTS KOSONG
        ];
        // --- Perubahan berakhir di sini ---

        // *** PERUBAHAN KRUSIAL: Tambahkan testimoni baru ke AWAL array (terbaru di atas) ***
        array_unshift($currentTestimonials, $newTestimonial);

        // Opsional: Batasi jumlah testimoni yang disimpan untuk menghindari file terlalu besar
        // Misalnya, hanya simpan 100 testimoni terbaru
        $currentTestimonials = array_slice($currentTestimonials, 0, 100); 

        // Simpan kembali data JSON ke file
        // Pastikan direktori tempat file berada memiliki izin tulis oleh web server
        if (file_put_contents($testimonialFile, json_encode($currentTestimonials, JSON_PRETTY_PRINT))) {
            $response['success'] = true;
            $response['message'] = 'Terima kasih! Testimoni Anda berhasil disimpan.';
        } else {
            // Lebih informatif jika gagal menyimpan file
            $response['message'] = 'Gagal menyimpan testimoni ke file. Pastikan server memiliki izin tulis untuk `testimonials.json`.';
        }
    }
} else {
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
?>