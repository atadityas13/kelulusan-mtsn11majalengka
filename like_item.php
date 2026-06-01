<?php
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = $_POST['itemId'] ?? '';
    $itemType = $_POST['itemType'] ?? '';

    if (empty($itemId) || empty($itemType)) {
        $response['message'] = 'ID item dan tipe item tidak valid.';
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
                if ((isset($item['id']) && $item['id'] === $itemId) ||
                    (!isset($item['id']) && md5(($item['message'] ?? '') . ($item['date'] ?? '') . ($item['name'] ?? '')) === $itemId)
                ) {
                    $items[$key]['likes'] = ($items[$key]['likes'] ?? 0) + 1;
                    file_put_contents($jsonFile, json_encode($items, JSON_PRETTY_PRINT));
                    $response['success'] = true;
                    $response['newLikesCount'] = $items[$key]['likes'];
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