<?php
/**
 * API Simpan Testimoni (Kesan & Pesan) - MTsN 11 Majalengka
 * Menyimpan kesan & pesan dari siswa lulus ke database dengan status default 'pending'.
 */

date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');

require_once 'db.php';

$response = ['success' => false, 'message' => 'Terjadi kesalahan.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = trim($_POST['studentName'] ?? '');
    $testimonialMessage = trim($_POST['testimonialMessage'] ?? '');

    if (empty($studentName) || empty($testimonialMessage)) {
        $response['message'] = 'Nama dan Kesan/Pesan tidak boleh kosong.';
    } else {
        try {
            $newUid = 'ts-' . uniqid();
            $stmt = $pdo->prepare("INSERT INTO testimonials (uid, name, message, likes, status, date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $newUid,
                htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($testimonialMessage, ENT_QUOTES, 'UTF-8'),
                0,
                'pending', // Status 'pending' menunggu persetujuan/moderasi admin di dashboard
                date('Y-m-d H:i:s')
            ]);

            $response['success'] = true;
            $response['message'] = 'Terima kasih! Kesan & Pesan Anda berhasil dikirim dan akan tampil setelah disetujui administrator.';
        } catch (PDOException $e) {
            $response['message'] = 'Gagal menyimpan kesan & pesan: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
?>