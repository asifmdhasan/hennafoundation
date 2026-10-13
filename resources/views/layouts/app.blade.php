<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
    @endif
    @hasSection('canonical_url')
        <link rel="canonical" href="@yield('canonical_url')">
    @endif
    <title>@yield('title', $settings->get('site_name', ''))</title>
    <meta property="og:site_name" content="{{ $settings->get('site_name', '') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('og_title', trim($__env->yieldContent('title', 'Henna Foundation')))">
    <meta property="og:url" content="@yield('canonical_url', url('/'))">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @endif
    @hasSection('og_description')
        <meta property="og:description" content="@yield('og_description')">
    @else
        @hasSection('meta_description')
            <meta property="og:description" content="@yield('meta_description')">
        @endif
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', trim($__env->yieldContent('title', 'Henna Foundation')))">
    @hasSection('meta_description')
        <meta name="twitter:description" content="@yield('meta_description')">
    @endif
    <meta name="theme-color" content="#c01c40">
    @if ($settings->get('favicon_url'))<link rel="icon" href="{{ $settings->get('favicon_url') }}">@endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700;800&family=Mukta+Vaani:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/public-site.css') }}?v={{ filemtime(public_path('css/public-site.css')) }}">
    <script src="{{ asset('js/public-site.js') }}" defer></script>
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <x-site-header :settings="$settings ?? collect()" />

    <main id="main-content">
        @yield('content')
    </main>

    <x-site-footer :settings="$settings ?? collect()" />
</body>
</html>
