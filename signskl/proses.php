<?php
require 'vendor/autoload.php';

use setasign\Fpdi\Fpdi;

// Initialize a variable to hold the message to display
$message = '';
$messageType = ''; // 'success', 'error', 'info'

// Handle the "simpan" action first, as it's a POST request for saving
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'simpan') {
    $outputFile = $_POST['output_file'] ?? '';
    $originalName = $_POST['original_name'] ?? '';
    $targetDir = __DIR__ . '/../assets/skl/';
    
    if (!file_exists($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            $message = "<b>Gagal membuat folder tujuan!</b><br>Pastikan memiliki izin untuk membuat folder <b>assets/skl/</b>.";
            $messageType = 'error';
        }
    }

    if ($messageType !== 'error' && file_exists($outputFile)) {
        // Sanitize the original name to be safe for a filename
        $sanitizedOriginalName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', pathinfo($originalName, PATHINFO_FILENAME)) . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
        $targetPath = $targetDir . $sanitizedOriginalName;

        if (@copy($outputFile, $targetPath)) {
            // Unlink temporary file after successful copy
            if (file_exists($outputFile)) {
                unlink($outputFile);
            }
            $message = "<b>File berhasil disimpan ke <code>assets/skl/</code> dengan nama:<br>$sanitizedOriginalName</b>";
            $messageType = 'success';
        } else {
            $message = "<b>Gagal memindahkan file!</b><br>Pastikan folder <b>assets/skl/</b> dapat ditulis (CHMOD 777).";
            $messageType = 'error';
        }
    } else if ($messageType !== 'error') {
        $message = "<b>File output tidak ditemukan.</b>";
        $messageType = 'error';
    }
    // No exit here, we will render the message below
} 
// Handle initial file uploads and PDF generation
else if (
    isset($_FILES['skl'], $_FILES['foto']) &&
    $_FILES['skl']['error'] === 0 &&
    $_FILES['foto']['error'] === 0
) {
    // Folder
    $tempFolder = 'temp/';
    $outputFolder = 'output/';
    if (!file_exists($tempFolder)) {
        if (!mkdir($tempFolder, 0777, true)) {
            $message = "<b>Upload gagal.</b><br>Folder <b>temp/</b> tidak dapat dibuat.<br>Pastikan memiliki izin untuk membuat folder.";
            $messageType = 'error';
        }
    }
    if (!file_exists($outputFolder)) {
        if (!mkdir($outputFolder, 0777, true)) {
            $message = "<b>Upload gagal.</b><br>Folder <b>output/</b> tidak dapat dibuat.<br>Pastikan memiliki izin untuk membuat folder.";
            $messageType = 'error';
        }
    }

    // Only proceed if no folder creation errors
    if ($messageType === '') {
        $uniq = uniqid();
        $pdfPath = $tempFolder . $uniq . '_skl.pdf';
        $fotoPath = $tempFolder . $uniq . '_foto.jpg';

        // Debug tambahan: cek apakah file sementara ada dan bisa diakses
        if (!is_uploaded_file($_FILES['skl']['tmp_name'])) {
            $message = "<b>Upload gagal.</b><br>File SKL tidak ditemukan di temporary upload.<br>Cek konfigurasi PHP <b>upload_tmp_dir</b> dan pastikan server tidak kehabisan ruang.";
            $messageType = 'error';
        } else if (!is_uploaded_file($_FILES['foto']['tmp_name'])) {
            $message = "<b>Upload gagal.</b><br>File Foto tidak ditemukan di temporary upload.<br>Cek konfigurasi PHP <b>upload_tmp_dir</b> dan pastikan server tidak kehabisan ruang.";
            $messageType = 'error';
        } else if (!is_dir($tempFolder) || !is_writable($tempFolder)) {
            $message = "<b>Upload gagal.</b><br>Folder <b>temp/</b> tidak dapat diakses/tidak writable.<br>Pastikan folder <b>temp/</b> sudah ada dan CHMOD 777.";
            $messageType = 'error';
        } else {
            $movePdf = move_uploaded_file($_FILES['skl']['tmp_name'], $pdfPath);
            $moveFoto = move_uploaded_file($_FILES['foto']['tmp_name'], $fotoPath);

            if (!$movePdf || !$moveFoto || !file_exists($pdfPath) || !file_exists($fotoPath)) {
                $message = "<b>Upload gagal.</b><br>Gagal menyimpan file ke server. File tidak ditemukan setelah upload.<br>Pastikan folder <b>temp/</b> memiliki izin tulis (CHMOD 777) dan tidak ada pembatasan quota.";
                $messageType = 'error';
            } else {
                $ttdPath = 'ttd.png'; // TTD Kamad fix

                // If user hasn't submitted positions (no drag feature implemented in this snippet)
                if (!isset($_POST['foto_x'])) { // This condition implies the first upload attempt
                    $default_foto_x = 74;
                    $default_foto_y = 142;
                    $default_ttd_x = 97;
                    $default_ttd_y = 140;
                    $default_ttd2_x = 97;
                    $default_ttd2_y = 220;

                    try {
                        $pdf = new FPDI();
                        $totalPages = $pdf->setSourceFile($pdfPath);

                        for ($i = 1; $i <= $totalPages; $i++) {
                            $tpl = $pdf->importPage($i);
                            $pdf->AddPage();
                            $pdf->useTemplate($tpl);

                            if ($i === 1) {
                                $pdf->Image($fotoPath, $default_foto_x, $default_foto_y, 27, 37); // Adjusted sizes to be proportional 3x4
                                $pdf->Image($ttdPath, $default_ttd_x, $default_ttd_y, 60, 40);
                            }
                            if ($i === 2) {
                                $pdf->Image($ttdPath, $default_ttd2_x, $default_ttd2_y, 60, 40);
                            }
                        }

                        $outputFile = $outputFolder . 'SKL_final_' . time() . '.pdf';
                        $pdf->Output($outputFile, 'F');

                        // Clean up temp files immediately after PDF generation if successful
                        unlink($pdfPath);
                        unlink($fotoPath);

                        // Ambil nama file asli dari upload SKL
                        $originalName = $_FILES['skl']['name'];
                        $originalName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName); // sanitize

                        // Set message and type for displaying the PDF and save button
                        $message = "<h3>SKL berhasil digenerate.</h3>" .
                                   "<iframe src='$outputFile' width='100%' height='600px' style='border: 1px solid #ccc; box-shadow: 0 4px 8px rgba(0,0,0,0.1);'></iframe>" .
                                   "<form method='post' style='margin-top:20px;'>
                                        <input type='hidden' name='action' value='simpan'>
                                        <input type='hidden' name='output_file' value='" . htmlspecialchars($outputFile) . "'>
                                        <input type='hidden' name='original_name' value='" . htmlspecialchars($originalName) . "'>
                                        <button type='submit'>💾 Simpan ke assets/skl</button>
                                    </form>";
                        $messageType = 'generated_pdf'; // Custom type to indicate PDF display
                    } catch (\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e) {
                        $message = "<b>Gagal memproses PDF.</b><br>File PDF yang diupload menggunakan teknik kompresi yang tidak didukung oleh FPDI versi gratis.<br>Silakan gunakan file PDF hasil export dari Word, LibreOffice, atau PDF printer standar.<br>Jika Anda membuat PDF dari scanner, gunakan mode PDF/A atau non-compressed.<br><br>Detail error:<br><code>" . htmlspecialchars($e->getMessage()) . "</code>";
                        $messageType = 'error';
                        // Clean up temp files in case of error
                        if (file_exists($pdfPath)) unlink($pdfPath);
                        if (file_exists($fotoPath)) unlink($fotoPath);
                    } catch (Exception $e) {
                        $message = "<b>Terjadi kesalahan saat memproses PDF.</b><br>Pastikan file PDF tidak rusak dan formatnya kompatibel.<br><br>Detail error:<br><code>" . htmlspecialchars($e->getMessage()) . "</code>";
                        $messageType = 'error';
                        // Clean up temp files in case of error
                        if (file_exists($pdfPath)) unlink($pdfPath);
                        if (file_exists($fotoPath)) unlink($fotoPath);
                    }
                } else { // This block handles if drag positions were submitted, but your current form doesn't seem to have a drag feature
                    // This block might be redundant if you're not implementing drag-and-drop
                    // For now, I'm assuming you're relying on the first block for PDF generation
                    // If you *do* have a drag feature, ensure these _POST variables are passed correctly
                    // and then implement this part
                    $message = "Debug: Drag positions were submitted, but this code path needs review.";
                    $messageType = 'info';
                }
            }
        }
    }
} else {
    // Initial upload failed or no files were uploaded
    $message = "<b>Upload gagal.</b><br>";
    if (isset($_FILES['skl'])) {
        $message .= "SKL error code: " . $_FILES['skl']['error'] . "<br>";
        if ($_FILES['skl']['error'] == 1) {
            $message .= "<span style='color:#c0392b'>File SKL melebihi batas <b>upload_max_filesize</b> di konfigurasi PHP.<br>Silakan upload file PDF yang lebih kecil atau minta admin menaikkan <b>upload_max_filesize</b> dan <b>post_max_size</b> di php.ini.</span><br>";
        }
    }
    if (isset($_FILES['foto'])) {
        $message .= "Foto error code: " . $_FILES['foto']['error'] . "<br>";
    }
    $message .= "Pastikan file SKL (PDF) dan Foto Siswa sudah dipilih dan tidak ada masalah saat upload.<br>";
    $message .= "Silakan ulangi proses upload.";
    $messageType = 'error';
}

// Start HTML output
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>SKL Generator</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; text-align: center; }
        h3 { color: #0056b3; }
        button { padding: 10px 20px; font-size: 1.1em; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px; }
        button:hover { opacity: 0.9; }

        .message-box {
            padding: 16px;
            border-radius: 8px;
            max-width: 500px;
            margin: 40px auto;
            text-align: center;
            font-size: 1.1em;
        }
        .error-box {
            color: #b71c1c;
            background: #ffeaea;
            border: 1px solid #f5c6cb;
        }
        .success-box {
            color: #155724;
            background: #e6ffed;
            border: 1px solid #b7eb8f;
        }
        .info-box {
            color: #0c5460;
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
        }
        .upload-tips {
            margin: 32px auto;
            max-width: 600px;
            background: #fffbe6;
            border: 1px solid #ffe58f;
            padding: 18px 20px 10px 20px;
            border-radius: 8px;
            color: #b8860b;
            font-size: 1.08em;
            text-align: left; /* Align tips left for better readability */
        }
        .upload-tips ol {
            margin-left: 18px;
        }
        .upload-tips ul {
            margin-left: 18px;
        }
        .btn-green {
            background-color: #28a745;
            color: white;
        }
        .btn-blue {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>

<?php
// Display the message based on its type
if ($message !== '') {
    if ($messageType === 'error') {
        echo "<div class='message-box error-box'>";
        echo $message;
        echo "<br><br><a href='index.php'><button class='btn-blue'>Kembali ke Form Upload</button></a>";
        echo "</div>";
    } elseif ($messageType === 'success') {
        echo "<div class='message-box success-box'>";
        echo $message;
        echo "<br><a href='index.php'><button class='btn-blue'>Kembali ke Form Upload</button></a>";
        echo "</div>";
    } elseif ($messageType === 'generated_pdf') {
        // This is for displaying the generated PDF and the save button
        echo $message; // The message variable already contains the full HTML for this
    } elseif ($messageType === 'info') {
        echo "<div class='message-box info-box'>";
        echo $message;
        echo "<br><br><a href='index.php'><button class='btn-blue'>Kembali ke Form Upload</button></a>";
        echo "</div>";
    }
}

// Only display the upload tips if it's an initial load or an upload error
if (!isset($_FILES['skl']) && $messageType !== 'generated_pdf' && $messageType !== 'success') {
    echo "<div class='upload-tips'>";
    echo "<b>Cara menaikkan limit upload file di PHP:</b><br>";
    echo "<ol style='margin-left:18px;'>";
    echo "<li><b>Edit file <code>php.ini</code> di server Anda:</b></li>";
    echo "<ul>";
    echo "<li>Ubah/naikkan nilai berikut:<br>";
    echo "<code>upload_max_filesize = 10M</code><br>";
    echo "<code>post_max_size = 12M</code><br>";
    echo "<code>max_file_uploads = 20</code></li>";
    echo "</ul>";
    echo "<li><b>Setelah mengubah <code>php.ini</code>, restart web server (apache/nginx) jika perlu.</b></li>";
    echo "<li>Jika di shared hosting, cari menu <b>PHP Selector</b> atau <b>PHP Options</b> di cPanel/Plesk lalu ubah <b>upload_max_filesize</b> dan <b>post_max_size</b> di sana.</li>";
    echo "<li>Untuk sementara, Anda bisa menyesuaikan ukuran file PDF agar lebih kecil dari limit saat ini.</li>";
    echo "</ol>";
    echo "Setelah menaikkan limit, coba upload ulang file PDF.<br>";
    echo "</div>";
}
?>

</body>
</html>