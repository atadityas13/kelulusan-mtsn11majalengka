<?php
/**
 * API Simpan Riwayat Pengecekan - MTsN 11 Majalengka
 * Mencatat siswa yang mengecek kelulusan ke database check_history.
 */

date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');

require_once 'db.php';

$response = ['success' => false, 'message' => 'Terjadi kesalahan.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = trim($_POST['studentName'] ?? '');
    $checkResult = trim($_POST['checkResult'] ?? ''); // 'Lulus' atau 'Tidak Lulus'
    $nomorPeserta = trim($_POST['nomorPeserta'] ?? '');

    if (empty($studentName) || empty($checkResult) || empty($nomorPeserta)) {
        $response['message'] = 'Data riwayat tidak lengkap.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO check_history (nomor_peserta, student_name, result, timestamp) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                htmlspecialchars($nomorPeserta, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($checkResult, ENT_QUOTES, 'UTF-8'),
                date('Y-m-d H:i:s')
            ]);

            $response['success'] = true;
            $response['message'] = 'Riwayat berhasil disimpan.';
        } catch (PDOException $e) {
            $response['message'] = 'Gagal menyimpan riwayat: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
?>