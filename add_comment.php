<?php
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = $_POST['itemId'] ?? '';
    $itemType = $_POST['itemType'] ?? ''; // 'teacher_message' atau 'testimonial'
    $commentAuthor = htmlspecialchars($_POST['commentAuthor'] ?? 'Anonim');
    $commentMessage = htmlspecialchars(trim($_POST['commentMessage'] ?? ''));

    if (empty($itemId) || empty($itemType) || empty($commentMessage)) {
        $response['message'] = 'Data komentar tidak lengkap.';
        echo json_encode($response);
        exit;
    }

    $jsonFile = '';
    if ($itemType === 'teacher_message') {
        $jsonFile = 'teacher_messages.json';
    } elseif ($itemType === 'testimonial') {
        $jsonFile = 'testimonials.json';
    } else {
        $response['message'] = 'Tipe item tidak dikenal.';
        echo json_encode($response);
        exit;
    }

    if (file_exists($jsonFile)) {
        $jsonData = file_get_contents($jsonFile);
        $items = json_decode($jsonData, true);

        if (is_array($items)) {
            foreach ($items as $key => $item) {
                if (($item['id'] ?? '') === $itemId) {
                    $newComment = [
                        'author' => $commentAuthor,
                        'comment' => $commentMessage,
                        'date' => date('Y-m-d H:i:s')
                    ];
                    $items[$key]['comments'][] = $newComment;
                    file_put_contents($jsonFile, json_encode($items, JSON_PRETTY_PRINT));
                    $response['success'] = true;
                    $response['newComment'] = $newComment; // Kirim kembali komentar baru untuk ditampilkan
                    echo json_encode($response);
                    exit;
                }
            }
        }
    }
    $response['message'] = 'Item tidak ditemukan.';
} else {
    $response['message'] = 'Metode request tidak diizinkan.';
}

echo json_encode($response);
?>