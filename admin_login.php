<?php
/**
 * Halaman Masuk Admin - MTsN 11 Majalengka
 * Proteksi akses aman dengan verifikasi password terenkripsi.
 */

session_start();
require_once 'db.php';

// Jika sudah login, langsung dialihkan ke dashboard
if (isset($_SESSION['logged_in_admin']) && $_SESSION['logged_in_admin'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username dan Password tidak boleh kosong.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Set sesi login admin
                $_SESSION['logged_in_admin'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'] ?? 'admin';
                
                header('Location: admin_dashboard.php');
                exit;
            } else {
                $error = 'Username atau Password yang Anda masukkan salah.';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - MTsN 11 Majalengka</title>
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg-grad: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.25);
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --danger: #ef4444;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-grad);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        .login-wrapper {
            position: relative;
            width: 100%;
            max-width: 440px;
            padding: 20px;
            box-sizing: border-box;
            animation: fadeIn 0.6s ease-out;
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 40px 35px;
            box-shadow: var(--shadow);
            text-align: center;
            position: relative;
        }

        .logo-container {
            margin-bottom: 25px;
        }

        .logo-container img {
            width: 85px;
            height: auto;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.15));
        }

        .login-card h2 {
            margin: 0 0 8px 0;
            color: var(--text-main);
            font-weight: 700;
            font-size: 1.8em;
            letter-spacing: -0.5px;
        }

        .login-card p.subtitle {
            margin: 0 0 30px 0;
            color: var(--text-muted);
            font-size: 0.95em;
        }

        .form-group {
            margin-bottom: 22px;
            text-align: left;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.88em;
            font-weight: 500;
            color: var(--text-main);
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1em;
            transition: color 0.3s;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95em;
            font-family: inherit;
            box-sizing: border-box;
            background-color: rgba(255,255,255,0.7);
            color: var(--text-main);
            outline: none;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--primary);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
        }

        .form-group input:focus + i {
            color: var(--primary);
        }

        .error-container {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--danger);
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.88em;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
            animation: shake 0.4s ease;
        }

        .error-container i {
            font-size: 1.2em;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background-color: var(--primary);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1.05em;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45);
        }

        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
        }

        .footer-credit {
            margin-top: 30px;
            font-size: 0.8em;
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
        }

        /* --- Animations --- */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }
            .login-card h2 {
                font-size: 1.6em;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-container">
                <img src="assets/mtsn11majalengka-logo.png" alt="Logo MTsN 11 Majalengka">
            </div>
            <h2>Sistem Administrasi</h2>
            <p class="subtitle">Kelulusan MTsN 11 Majalengka</p>

            <?php if (!empty($error)): ?>
                <div class="error-container">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="admin_login.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="username">Nama Pengguna (Username)</label>
                    <div class="input-icon-wrapper">
                        <input type="text" id="username" name="username" placeholder="Masukkan username admin" required>
                        <i class="fa-solid fa-user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi (Password)</label>
                    <div class="input-icon-wrapper">
                        <input type="password" id="password" name="password" placeholder="Masukkan password admin" required>
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login">Masuk Halaman Admin</button>
            </form>
        </div>
        <div class="footer-credit">
            &copy; <?php echo date('Y'); ?> MTsN 11 Majalengka. All rights reserved.
        </div>
    </div>
</body>
</html>
