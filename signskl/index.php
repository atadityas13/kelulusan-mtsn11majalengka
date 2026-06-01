<?php
session_start();
$pinBenar = '20278893';
if (!isset($_SESSION['signskl_pin_ok'])) {
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pin'])) {
    if ($_POST['pin'] === $pinBenar) {
      $_SESSION['signskl_pin_ok'] = true;
      header("Location: " . $_SERVER['PHP_SELF']);
      exit;
    } else {
      $pinError = "PIN salah!";
    }
  }
  ?>
  <!DOCTYPE html>
  <html lang="id">
  <head>
    <meta charset="UTF-8">
    <title>Masukkan PIN - SIGN SKL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
      body {
        background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
        font-family: 'Poppins', Arial, sans-serif;
        min-height: 100vh;
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      .pin-container {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.13);
        padding: 36px 28px 28px 28px;
        max-width: 340px;
        width: 95%;
        text-align: center;
        animation: fadeIn 0.7s;
      }
      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(40px);}
        to { opacity: 1; transform: none;}
      }
      h2 {
        color: #007bff;
        margin-bottom: 18px;
        font-size: 1.3em;
        letter-spacing: 1px;
      }
      input[type="password"] {
        width: 90%;
        padding: 10px 12px;
        border-radius: 7px;
        border: 1px solid #b3d8ff;
        font-size: 1.1em;
        margin-bottom: 18px;
        background: #f7faff;
        transition: border 0.2s;
      }
      input[type="password"]:focus {
        border-color: #007bff;
        outline: none;
      }
      button[type="submit"] {
        background: linear-gradient(90deg, #007bff 60%, #00c6ff 100%);
        color: #fff;
        border: none;
        border-radius: 7px;
        padding: 10px 0;
        width: 100%;
        font-size: 1.13em;
        font-weight: bold;
        cursor: pointer;
        margin-top: 10px;
        box-shadow: 0 2px 8px rgba(0,123,255,0.08);
        transition: background 0.2s, box-shadow 0.2s;
      }
      button[type="submit"]:hover {
        background: linear-gradient(90deg, #0056b3 60%, #0099cc 100%);
        box-shadow: 0 4px 16px rgba(0,123,255,0.13);
      }
      .pin-error {
        color: #c0392b;
        margin-bottom: 10px;
        font-size: 1em;
      }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  </head>
  <body>
    <div class="pin-container">
      <h2>Masukkan PIN untuk akses SIGN SKL</h2>
      <?php if (!empty($pinError)) echo '<div class="pin-error">'.htmlspecialchars($pinError).'</div>'; ?>
      <form method="post" autocomplete="off">
        <input type="password" name="pin" placeholder="Masukkan PIN" required autofocus>
        <br>
        <button type="submit">Akses</button>
      </form>
    </div>
  </body>
  </html>
  <?php
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>GENERATE SIGN SKL</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
      font-family: 'Poppins', Arial, sans-serif;
      min-height: 100vh;
      margin: 0;
      padding: 0;
      color: #222;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
    }
    .main-layout {
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: flex-start;
      gap: 32px;
      width: 100%;
      max-width: 1100px;
      margin: 0 auto;
    }
    .container {
      background: #fff;
      margin-top: 48px;
      padding: 32px 28px 24px 28px;
      border-radius: 18px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.13);
      max-width: 420px;
      width: 95%;
      text-align: center;
      animation: fadeIn 0.7s;
      min-width: 320px;
      flex: 1 1 420px;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(40px);}
      to { opacity: 1; transform: none;}
    }
    h2 {
      color: #007bff;
      margin-bottom: 18px;
      font-size: 1.5em;
      letter-spacing: 1px;
    }
    label {
      font-weight: 500;
      color: #333;
      display: block;
      margin-bottom: 7px;
      text-align: left;
    }
    input[type="file"] {
      display: block;
      margin: 0 0 18px 0;
      width: 100%;
      padding: 8px 0;
      font-size: 1em;
      background: #f7faff;
      border: 1px solid #dbeafe;
      border-radius: 7px;
      transition: border 0.2s;
    }
    input[type="file"]:focus {
      border-color: #007bff;
      outline: none;
    }
    button[type="submit"] {
      background: linear-gradient(90deg, #007bff 60%, #00c6ff 100%);
      color: #fff;
      border: none;
      border-radius: 7px;
      padding: 12px 0;
      width: 100%;
      font-size: 1.13em;
      font-weight: bold;
      cursor: pointer;
      margin-top: 10px;
      box-shadow: 0 2px 8px rgba(0,123,255,0.08);
      transition: background 0.2s, box-shadow 0.2s;
    }
    button[type="submit"]:hover {
      background: linear-gradient(90deg, #0056b3 60%, #0099cc 100%);
      box-shadow: 0 4px 16px rgba(0,123,255,0.13);
    }
    .info {
      margin-top: 18px;
      font-size: 0.98em;
      color: #555;
      background: #f0f8ff;
      border-left: 4px solid #007bff;
      padding: 10px 14px;
      border-radius: 7px;
      text-align: left;
    }
    .illustration {
      width: 80px;
      margin-bottom: 18px;
      opacity: 0.93;
      filter: drop-shadow(0 2px 8px #b3d8ff55);
    }
    .sidebar-history {
      background: #f8fafc;
      border-radius: 14px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.07);
      padding: 22px 18px 18px 18px;
      min-width: 270px;
      max-width: 340px;
      margin-top: 48px;
      font-size: 1em;
      color: #333;
      height: fit-content;
      animation: fadeIn 0.9s;
      display: flex;
      flex-direction: column;
      align-items: stretch;
    }
    .sidebar-history h3 {
      margin-top: 0;
      color: #007b55;
      font-size: 1.13em;
      margin-bottom: 14px;
      letter-spacing: 0.5px;
      text-align: left;
    }
    .search-bar {
      margin-bottom: 12px;
      display: flex;
      gap: 8px;
      align-items: center;
    }
    .search-input {
      flex: 1;
      padding: 7px 12px;
      border-radius: 6px;
      border: 1px solid #b3d8ff;
      font-size: 1em;
      background: #f7faff;
      transition: border 0.2s;
    }
    .search-input:focus {
      border-color: #007bff;
      outline: none;
    }
    .history-list {
      list-style: none;
      padding: 0;
      margin: 0;
      max-height: 350px;
      overflow-y: auto;
      background: #f6fafd;
      border-radius: 10px;
      box-shadow: 0 1px 4px #e3eafc44;
      scrollbar-width: thin;
      scrollbar-color: #b3d8ff #f6fafd;
    }
    .history-list::-webkit-scrollbar {
      width: 7px;
      background: #f6fafd;
    }
    .history-list::-webkit-scrollbar-thumb {
      background: #b3d8ff;
      border-radius: 6px;
    }
    .history-item {
      padding: 13px 12px 13px 12px;
      border-bottom: 1px solid #e3e3e3;
      display: flex;
      flex-direction: column;
      gap: 2px;
      background: #fff;
      border-radius: 8px;
      margin: 7px 8px;
      box-shadow: 0 1px 4px #e3eafc22;
      transition: box-shadow 0.2s, background 0.2s;
      position: relative;
    }
    .history-item:last-child {
      border-bottom: none;
    }
    .history-filename {
      font-weight: 500;
      color: #007bff;
      word-break: break-all;
      font-size: 1.04em;
      margin-bottom: 2px;
    }
    .history-time {
      font-size: 0.97em;
      color: #888;
      margin-bottom: 2px;
    }
    .history-actions {
      display: flex;
      gap: 10px;
      margin-top: 4px;
    }
    .history-download {
      color: #28a745;
      text-decoration: none;
      font-weight: 500;
      font-size: 0.97em;
      transition: color 0.2s;
      background: none;
      border: none;
      cursor: pointer;
      padding: 0 2px;
    }
    .history-download:hover {
      color: #007b55;
      text-decoration: underline;
    }
    .history-preview, .history-delete {
      background: none;
      border: none;
      color: #007bff;
      cursor: pointer;
      font-size: 0.97em;
      padding: 0 2px;
      text-decoration: underline;
      transition: color 0.2s;
    }
    .history-preview:hover {
      color: #0056b3;
    }
    .history-delete {
      color: #e74c3c;
    }
    .history-delete:hover {
      color: #c0392b;
    }
    .history-empty {
      color: #888;
      text-align: center;
      margin: 24px 0 0 0;
      font-size: 1em;
    }
    .history-total {
      color: #007b55;
      font-weight: bold;
      background: #eafaf1;
      border-radius: 7px;
      margin-bottom: 7px;
      padding: 7px 0 7px 0;
      text-align: center;
      font-size: 1.04em;
      letter-spacing: 0.5px;
    }
    .modal-preview-overlay {
      position: fixed;
      z-index: 9999;
      left: 0; top: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.45);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .modal-preview-content {
      background: #fff;
      border-radius: 12px;
      max-width: 90vw;
      width: 700px;
      max-height: 90vh;
      padding: 0;
      box-shadow: 0 8px 32px rgba(0,0,0,0.18);
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: stretch;
    }
    .modal-preview-header {
      padding: 12px 20px;
      border-bottom: 1px solid #eee;
      font-weight: bold;
      color: #007bff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #f7faff;
      border-radius: 12px 12px 0 0;
    }
    .close-preview-modal {
      background: none;
      border: none;
      font-size: 1.5em;
      color: #888;
      cursor: pointer;
      margin-left: 10px;
    }
    .modal-preview-body {
      flex: 1;
      overflow: auto;
      background: #f9f9f9;
      padding: 0;
      border-radius: 0 0 12px 12px;
    }
    .modal-preview-body iframe {
      width: 100%;
      height: 70vh;
      border: none;
      border-radius: 0 0 12px 12px;
      background: #fff;
      display: block;
    }
    @media (max-width: 600px) {
      .container {
        margin-top: 18px;
        padding: 18px 4vw 18px 4vw;
      }
      h2 {
        font-size: 1.15em;
      }
      .illustration {
        width: 60px;
      }
      .sidebar-history {
        min-width: 0;
        max-width: 100vw;
        padding: 12px 2vw 12px 2vw;
      }
      .history-list {
        max-height: 220px;
      }
    }
    @media (max-width: 900px) {
      .main-layout {
        flex-direction: column;
        gap: 0;
        align-items: stretch;
      }
      .sidebar-history {
        margin: 28px auto 0 auto;
        max-width: 98vw;
      }
    }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="main-layout">
    <div class="container">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Upload" class="illustration">
      <h2>SIGN SKL</h2>
      <form action="proses.php" method="post" enctype="multipart/form-data" autocomplete="off" id="upload-form">
        <label for="skl">Upload SKL (PDF):</label>
        <input type="file" id="skl" name="skl" accept="application/pdf" required>
        
        <label for="foto">Upload Foto Siswa (JPG/PNG):</label>
        <input type="file" id="foto" name="foto" accept="image/*" required>

        <button type="submit">Proses & Lihat Preview</button>
        <div id="file-double-warning" style="color:#c0392b;font-size:0.98em;margin-top:8px;display:none;"></div>
      </form>
      <div class="info">
        <b>Tips:</b> Pastikan file SKL sudah benar (format PDF) dan foto siswa jelas (format JPG/PNG, rasio 3x4).<br>
        Setelah proses, Anda bisa melihat preview dan menyimpan hasil SKL final.
      </div>
    </div>
    <aside class="sidebar-history">
      <h3>Riwayat Penambahan File SKL</h3>
      <div class="search-bar">
        <input type="text" class="search-input" id="search-skl" placeholder="Cari nama file...">
      </div>
      <ul class="history-list" id="history-list">
        <?php
        $sklDir = __DIR__ . '/../assets/skl/';
        $files = [];
        $fileNamesLower = [];
        $duplicates = [];
        if (is_dir($sklDir)) {
          foreach (scandir($sklDir) as $file) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = $sklDir . $file;
            if (is_file($fullPath) && preg_match('/\.pdf$/i', $file)) {
              $files[] = [
                'name' => $file,
                'time' => filemtime($fullPath)
              ];
              $lower = strtolower($file);
              if (in_array($lower, $fileNamesLower)) {
                $duplicates[] = $file;
              } else {
                $fileNamesLower[] = $lower;
              }
            }
          }
        }
        usort($files, function($a, $b) { return $b['time'] - $a['time']; });
        $totalFiles = count($files);
        echo '<li class="history-total">Total file: <span id="total-skl-count">' . $totalFiles . '</span></li>';
        if (!empty($duplicates)) {
          echo '<li class="history-item" style="color:#c0392b;background:#fff3f3;border:1px solid #ffcfcf;border-radius:7px;margin-bottom:7px;"><b>Duplikat terdeteksi:</b> ';
          echo implode(', ', array_map('htmlspecialchars', $duplicates));
          echo '</li>';
        }
        if ($totalFiles === 0) {
          echo '<li class="history-empty">Belum ada file SKL.</li>';
        } else {
          // Batasi tampilan awal 10 file, sisanya tetap bisa di-scroll
          $maxShow = 10;
          $i = 0;
          foreach ($files as $f) {
            $i++;
            $tgl = date('d-m-Y H:i', $f['time']);
            $url = '/../assets/skl/' . rawurlencode($f['name']);
            echo '<li class="history-item" data-filename="' . htmlspecialchars($f['name']) . '"' . ($i > $maxShow ? ' style="display:none;"' : '') . '>';
            echo '<span class="history-filename">' . htmlspecialchars($f['name']) . '</span>';
            echo '<span class="history-time">Ditambahkan: ' . $tgl . '</span>';
            echo '<div class="history-actions">';
            echo '<a class="history-download" href="' . $url . '" target="_blank" download>⬇️ Download</a>';
            echo '<button class="history-preview" type="button" data-url="' . $url . '" data-filename="' . htmlspecialchars($f['name']) . '">👁️ Preview</button>';
            echo '<button class="history-delete" type="button" data-filename="' . htmlspecialchars($f['name']) . '">🗑️ Hapus</button>';
            echo '</div>';
            echo '</li>';
          }
        }
        ?>
      </ul>
      <!-- Modal Preview -->
      <div id="modal-preview-overlay" class="modal-preview-overlay" style="display:none;">
        <div class="modal-preview-content">
          <div class="modal-preview-header">
            <span id="modal-preview-title"></span>
            <button class="close-preview-modal" id="close-preview-modal" title="Tutup">&times;</button>
          </div>
          <div class="modal-preview-body">
            <iframe id="modal-preview-iframe" src="" allowfullscreen></iframe>
          </div>
        </div>
      </div>
    </aside>
  </div>
  <script>
    // Pencarian file
    document.getElementById('search-skl').addEventListener('input', function() {
      const q = this.value.trim().toLowerCase();
      const items = document.querySelectorAll('.history-item[data-filename]');
      let count = 0;
      items.forEach(function(item) {
        const name = item.getAttribute('data-filename').toLowerCase();
        if (name.includes(q)) {
          item.style.display = '';
          count++;
        } else {
          item.style.display = 'none';
        }
      });
      document.getElementById('total-skl-count').textContent = count;
    });

    // Preview file
    document.querySelectorAll('.history-preview').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const url = btn.getAttribute('data-url');
        const filename = btn.getAttribute('data-filename');
        document.getElementById('modal-preview-title').textContent = filename;
        document.getElementById('modal-preview-iframe').src = url;
        document.getElementById('modal-preview-overlay').style.display = 'flex';
      });
    });
    document.getElementById('close-preview-modal').onclick = function() {
      document.getElementById('modal-preview-overlay').style.display = 'none';
      document.getElementById('modal-preview-iframe').src = '';
    };
    document.getElementById('modal-preview-overlay').onclick = function(e) {
      if (e.target === this) {
        this.style.display = 'none';
        document.getElementById('modal-preview-iframe').src = '';
      }
    };

    // Hapus file
    document.querySelectorAll('.history-delete').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const filename = btn.getAttribute('data-filename');
        if (!confirm('Yakin ingin menghapus file: ' + filename + ' ?')) return;
        fetch('hapus_skl.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'filename=' + encodeURIComponent(filename)
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            btn.closest('.history-item').remove();
            // Update total count
            const total = document.querySelectorAll('.history-item[data-filename]').length;
            document.getElementById('total-skl-count').textContent = total;
          } else {
            alert('Gagal menghapus file: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(() => alert('Gagal menghapus file.'));
      });
    });

    // Deteksi nama file double sebelum submit
    (function() {
      // Ambil semua nama file PDF di assets/skl
      var existingFiles = [
        <?php
        foreach ($files as $f) {
          echo '"' . addslashes($f['name']) . '",';
        }
        ?>
      ];
      var sklInput = document.getElementById('skl');
      var form = document.getElementById('upload-form');
      var warning = document.getElementById('file-double-warning');
      sklInput.addEventListener('change', function() {
        warning.style.display = 'none';
        var file = sklInput.files[0];
        if (!file) return;
        var name = file.name;
        var found = existingFiles.some(function(f) {
          return f.toLowerCase() === name.toLowerCase();
        });
        if (found) {
          warning.textContent = "Nama file SKL sudah ada di sistem. Silakan gunakan nama file lain atau hapus file lama terlebih dahulu.";
          warning.style.display = 'block';
        }
      });
      form.addEventListener('submit', function(e) {
        var file = sklInput.files[0];
        if (!file) return;
        var name = file.name;
        var found = existingFiles.some(function(f) {
          return f.toLowerCase() === name.toLowerCase();
        });
        if (found) {
          warning.textContent = "Nama file SKL sudah ada di sistem. Silakan gunakan nama file lain atau hapus file lama terlebih dahulu.";
          warning.style.display = 'block';
          e.preventDefault();
        }
      });
    })();
  </script>
</body>
</html>