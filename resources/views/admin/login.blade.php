@extends('layouts.app')

@section('title', 'Login Administrator - MTsN 11 Majalengka')

@section('styles')
<style>
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --bg-grad: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
        --text-main: #1f2937;
        --text-muted: #6b7280;
        --danger: #ef4444;
        --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }

    body {
        background: var(--bg-grad) !important;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
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

    .footer-credit {
        margin-top: 30px;
        font-size: 0.8em;
        color: rgba(255, 255, 255, 0.7);
        text-align: center;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-6px); }
        75% { transform: translateX(6px); }
    }
</style>
@endsection

@section('content')
    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-container">
                <img src="{{ asset('assets/mtsn11majalengka-logo.png') }}" alt="Logo MTsN 11 Majalengka">
            </div>
            <h2>Sistem Administrasi</h2>
            <p class="subtitle">Kelulusan MTsN 11 Majalengka</p>

            @if($errors->any())
                <div class="error-container">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" autocomplete="off">
                @csrf
                <div class="form-group">
                    <label for="email">Surel Pengguna (Email)</label>
                    <div class="input-icon-wrapper">
                        <input type="email" id="email" name="email" placeholder="Contoh: admin@mtsn11majalengka.sch.id" required value="{{ old('email') }}">
                        <i class="fa-solid fa-envelope"></i>
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
            &copy; {{ date('Y') }} MTsN 11 Majalengka. All rights reserved.
        </div>
    </div>
@endsection
