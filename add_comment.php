<?php
/**
 * API Tambah Komentar - MTsN 11 Majalengka
 * Menyimpan komentar ke database SQL secara real-time.
 */

date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');

require_once 'db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = trim($_POST['itemId'] ?? '');
    $itemType = trim($_POST['itemType'] ?? ''); // 'teacher_message' atau 'testimonial'
    $commentAuthor = htmlspecialchars(trim($_POST['commentAuthor'] ?? 'Anonim'));
    $commentMessage = htmlspecialchars(trim($_POST['commentMessage'] ?? ''));

    if (empty($itemId) || empty($itemType) || empty($commentMessage)) {
        $response['message'] = 'Data komentar tidak lengkap.';
        echo json_encode($response);
        exit;
    }

    if ($commentAuthor === '') {
        $commentAuthor = 'Anonim';
    }

    // Validasi tipe item dan keberadaan item di database
    try {
        if ($itemType === 'teacher_message') {
            $check = $pdo->prepare("SELECT COUNT(*) FROM teacher_messages WHERE uid = ?");
        } elseif ($itemType === 'testimonial') {
            $check = $pdo->prepare("SELECT COUNT(*) FROM testimonials WHERE uid = ?");
        } else {
            $response['message'] = 'Tipe item tidak dikenal.';
            echo json_encode($response);
            exit;
        }

        $check->execute([$itemId]);
        if ($check->fetchColumn() == 0) {
            $response['message'] = 'Item asal tidak ditemukan di database.';
            echo json_encode($response);
            exit;
        }

        // Simpan komentar ke database
        $newComment = [
            'author' => $commentAuthor,
            'comment' => $commentMessage,
            'date' => date('Y-m-d H:i:s')
        ];

        $stmt = $pdo->prepare("INSERT INTO comments (item_uid, item_type, author, comment, date, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $itemId,
            $itemType,
            $commentAuthor,
            $commentMessage,
            $newComment['date'],
            'approved' // Komentar langsung otomatis disetujui (moderasi bisa dilakukan di dashboard)
        ]);

        $response['success'] = true;
        $response['newComment'] = $newComment;

    } catch (PDOException $e) {
        $response['message'] = 'Gagal menyimpan komentar ke database: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
?>