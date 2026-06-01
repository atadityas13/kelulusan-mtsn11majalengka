<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Kelulusan MTsN 11 Majalengka</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 36px 32px;
            width: 100%;
            max-width: 380px;
        }
        .login-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }
        .login-logo img { width: 44px; height: auto; }
        .login-logo div { line-height: 1.3; }
        .login-logo strong { display: block; font-size: 0.95em; color: #111; }
        .login-logo span { font-size: 0.78em; color: #666; }
        h1 { font-size: 1.15em; font-weight: 600; color: #111; margin-bottom: 6px; }
        .subtitle { font-size: 0.82em; color: #777; margin-bottom: 24px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 0.83em; font-weight: 500; color: #333; margin-bottom: 5px; }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 0.9em;
            font-family: inherit;
            color: #111;
            outline: none;
        }
        input:focus { border-color: #3b4ecc; }
        .alert-error {
            background: #fff5f5;
            border: 1px solid #f5c6c6;
            color: #b91c1c;
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 0.84em;
            margin-bottom: 18px;
        }
        .btn-submit {
            width: 100%;
            padding: 10px;
            background: #3b4ecc;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            margin-top: 4px;
        }
        .btn-submit:hover { background: #2f3fa5; }
        .footer { text-align: center; font-size: 0.75em; color: #aaa; margin-top: 20px; }
    </style>
</head>
<body>
    <div>
        <div class="login-box">
            <div class="login-logo">
                <img src="{{ asset('assets/mtsn11majalengka-logo.png') }}" alt="Logo">
                <div>
                    <strong>MTsN 11 Majalengka</strong>
                    <span>Panel Administrasi</span>
                </div>
            </div>

            <h1>Masuk ke Sistem</h1>
            <p class="subtitle">Hanya untuk administrator yang berwenang.</p>

            @if($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login') }}" method="POST" autocomplete="off">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="admin@mtsn11majalengka.sch.id" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Kata sandi" required>
                </div>
                <button type="submit" class="btn-submit">Masuk</button>
            </form>
        </div>
        <p class="footer">&copy; {{ date('Y') }} MTsN 11 Majalengka</p>
    </div>
</body>
</html>
