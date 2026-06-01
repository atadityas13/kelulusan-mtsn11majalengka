<?php
// Simple delete handler for AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filename'])) {
    $filename = basename($_POST['filename']);
    $target = __DIR__ . '/../assets/skl/' . $filename;
    if (is_file($target) && preg_match('/\.pdf$/i', $filename)) {
        if (@unlink($target)) {
            echo json_encode(['success' => true]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Tidak bisa menghapus file.']);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'File tidak ditemukan.']);
    exit;
}
echo json_encode(['success' => false, 'message' => 'Permintaan tidak valid.']);
