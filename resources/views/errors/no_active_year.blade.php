<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Belum Aktif - MTsN 11 Majalengka</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent-color: #6366f1;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            overflow-x: hidden;
        }

        .container {
            width: 90%;
            max-width: 550px;
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2.2em;
            color: var(--accent-color);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 20px rgba(99, 102, 241, 0.2); }
            50% { transform: scale(1.05); box-shadow: 0 0 30px rgba(99, 102, 241, 0.4); }
            100% { transform: scale(1); box-shadow: 0 0 20px rgba(99, 102, 241, 0.2); }
        }

        h1 {
            font-size: 1.8em;
            margin-bottom: 15px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        p {
            font-size: 0.98em;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        .btn-home {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            border: none;
            padding: 12px 28px;
            font-size: 0.95em;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
        }

        .footer {
            margin-top: 40px;
            font-size: 0.8em;
            color: rgba(255, 255, 255, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-box">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <h1>Sistem Pengecekan Belum Aktif</h1>
        <p>
            Mohon maaf, panitia kelulusan MTsN 11 Majalengka belum mengaktifkan periode tahun pelajaran utama untuk pengumuman saat ini.<br>
            Silakan cek kembali secara berkala atau hubungi panitia kelulusan madrasah jika Anda merasa ini adalah kesalahan.
        </p>
        <a href="{{ route('login') }}" class="btn-home">
            <i class="fa-solid fa-user-shield"></i> Masuk Panel Admin
        </a>
        <div class="footer">
            &copy; {{ date('Y') }} MTsN 11 Majalengka. All Rights Reserved.
        </div>
    </div>
</body>
</html>
