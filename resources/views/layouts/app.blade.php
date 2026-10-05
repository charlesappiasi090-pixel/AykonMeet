<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Aykon Meet' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @endif
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ auth()->check() ? route('admin.dashboard') : route('login') }}"><span class="brand-mark">A</span><span>Aykon<span class="brand-accent">Meet</span></span></a>
        <nav>
            @auth
                <a href="{{ route('admin.dashboard') }}">Admin console</a>
                <form method="post" action="{{ route('logout') }}" class="logout-form">@csrf<button type="submit" class="text-button">Logout</button></form>
            @else
                <a href="{{ route('login') }}" class="admin-link">Admin login</a>
            @endauth
        </nav>
    </header>
    <main class="page-shell">
        @if (session('success'))<div class="flash success">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="flash error">{{ session('error') }}</div>@endif
        @if ($errors->any())<div class="flash error"><strong>Check the form:</strong> {{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
    <footer class="footer">AykonMeet <span>•</span> Make every minute count.</footer>
</body>
</html>
