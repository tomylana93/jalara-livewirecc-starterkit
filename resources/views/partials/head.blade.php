<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? __($title).' - '.config('app.name', 'Jalara') : config('app.name', 'Jalara') }}
</title>

<style id="brand-theme">
    :root {
        @foreach ($brand['themeTokens']['light'] as $token => $value)
            {{ $token }}: {{ $value }};
        @endforeach
    }

    .dark {
        @foreach ($brand['themeTokens']['dark'] as $token => $value)
            {{ $token }}: {{ $value }};
        @endforeach
    }
</style>

<link rel="icon" href="{{ $brand['favicon'] ?? '/favicon.ico' }}" sizes="any">
@unless ($brand['favicon'])
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
@endunless
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
@if ($brand['ogImage'])
    <meta property="og:image" content="{{ $brand['ogImage'] }}">
@endif

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
