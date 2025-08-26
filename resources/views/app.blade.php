<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>
    {{ isset($page['props']['post']) ? $page['props']['post']['title'] . ' - ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
    </title>

    <!-- Open Graph Tags -->
    @if (isset($page['props']['post']))
        <meta property="og:type" content="article" />
        <meta property="og:title" content="{{ $page['props']['post']['title'] ?? config('app.name', 'Laravel') }}" />
        <meta property="og:description"
            content="{{ Str::limit(strip_tags($page['props']['post']['body'] ?? ''), 200) }}" />
        @if (isset($page['props']['post']['images']) && count($page['props']['post']['images']) > 0)
            <meta property="og:image"
                content="{{ url('/storage/' . $page['props']['post']['images'][0]['image_path']) }}" />
            <meta property="og:image:width" content="1200" />
            <meta property="og:image:height" content="630" />
            <meta property="og:image:alt" content="{{ $page['props']['post']['title'] }}" />
        @endif
        <meta property="og:url" content="{{ url()->current() }}" />
        <meta property="og:site_name" content="Fappify" />
        <meta property="og:locale" content="en_US" />
        <meta property="article:published_time" content="{{ $page['props']['post']['created_at'] }}" />
        <meta property="fb:app_id" content="{{ config('services.facebook.app_id') }}" />

        <!-- Twitter Card Tags -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="{{ $page['props']['post']['title'] ?? config('app.name', 'Laravel') }}" />
        <meta name="twitter:description"
            content="{{ Str::limit(strip_tags($page['props']['post']['body'] ?? ''), 200) }}" />
        @if (isset($page['props']['post']['images']) && count($page['props']['post']['images']) > 0)
            <meta name="twitter:image"
                content="{{ url('/storage/' . $page['props']['post']['images'][0]['image_path']) }}" />
        @endif
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Visit tracking is handled by middleware - no need for client-side tracking
            // This prevents JSON parsing errors from missing /track-visit route
            console.log('Page loaded:', window.location.pathname);
        });
    </script>
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
