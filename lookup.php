<?php
/**
 * API Pencarian Data (Lookup) - MTsN 11 Majalengka
 * Menyediakan verifikasi NISN & Nomor Peserta secara aman di sisi server.
 */

header('Content-Type: application/json');
require_once 'db.php';

$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

if ($action === 'cek_nopes') {
    $nisn = trim($_GET['nisn'] ?? '');
    $tgl = trim($_GET['tanggal_lahir'] ?? '');

    if (empty($nisn) || empty($tgl)) {
        $response['message'] = 'NISN dan Tanggal Lahir wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT nomor_peserta FROM students WHERE nisn = ? AND tanggal_lahir = ?");
            $stmt->execute([$nisn, $tgl]);
            $nopes = $stmt->fetchColumn();

            if ($nopes) {
                $response['success'] = true;
                $response['nomor_peserta'] = $nopes;
            } else {
                $response['message'] = 'Data tidak ditemukan. Pastikan NISN dan tanggal lahir benar.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Terjadi kesalahan sistem database.';
        }
    }
} elseif ($action === 'cek_nisn') {
    $nama = trim($_GET['nama'] ?? '');
    $tgl = trim($_GET['tanggal_lahir'] ?? '');

    if (empty($nama) || empty($tgl)) {
        $response['message'] = 'Nama Lengkap dan Tanggal Lahir wajib diisi.';
    } else {
        try {
            // Pencarian case-insensitive untuk nama lengkap
            $stmt = $pdo->prepare("SELECT nisn FROM students WHERE UPPER(nama) = UPPER(?) AND tanggal_lahir = ?");
            $stmt->execute([$nama, $tgl]);
            $nisn = $stmt->fetchColumn();

            if ($nisn) {
                $response['success'] = true;
                $response['nisn'] = $nisn;
            } else {
                $response['message'] = 'Data tidak ditemukan. Pastikan nama lengkap dan tanggal lahir benar.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Terjadi kesalahan sistem database.';
        }
    }
} else {
    $response['message'] = 'Aksi tidak dikenal.';
}

echo json_encode($response);
?>
