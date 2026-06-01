<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pengumuman Kelulusan MTsN 11 Majalengka')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('styles')
</head>
<body>
    @yield('content')
    
    <script>
        // Set CSRF Token default untuk seluruh request AJAX Fetch
        window.csrfToken = '{{ csrf_token() }}';
    </script>
    @yield('scripts')
</body>
</html>
