<?php
/**
 * API Suka (Likes) - MTsN 11 Majalengka
 * Menambahkan jumlah "Suka" pada pesan atau testimoni di database.
 */

date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');

require_once 'db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = trim($_POST['itemId'] ?? '');
    $itemType = trim($_POST['itemType'] ?? '');

    if (empty($itemId) || empty($itemType)) {
        $response['message'] = 'ID item dan tipe item tidak valid.';
        echo json_encode($response);
        exit;
    }

    try {
        if ($itemType === 'teacher_message') {
            $stmt = $pdo->prepare("UPDATE teacher_messages SET likes = likes + 1 WHERE uid = ?");
            $stmt->execute([$itemId]);

            $stmtGet = $pdo->prepare("SELECT likes FROM teacher_messages WHERE uid = ?");
        } elseif ($itemType === 'testimonial') {
            $stmt = $pdo->prepare("UPDATE testimonials SET likes = likes + 1 WHERE uid = ?");
            $stmt->execute([$itemId]);

            $stmtGet = $pdo->prepare("SELECT likes FROM testimonials WHERE uid = ?");
        } else {
            $response['message'] = 'Tipe item tidak dikenal.';
            echo json_encode($response);
            exit;
        }

        $stmtGet->execute([$itemId]);
        $newLikesCount = $stmtGet->fetchColumn();

        if ($newLikesCount !== false) {
            $response['success'] = true;
            $response['newLikesCount'] = (int)$newLikesCount;
        } else {
            $response['message'] = 'Item tidak ditemukan di database.';
        }

    } catch (PDOException $e) {
        $response['message'] = 'Gagal memproses suka: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
?>