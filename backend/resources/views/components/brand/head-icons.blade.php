{{-- Favicons, touch icon, manifest and default social image. Include once in every layout <head>. --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="16x16 32x32 48x48">
<link rel="icon" type="image/png" href="{{ asset('images/brand/favicon-96.png') }}" sizes="96x96">
<link rel="apple-touch-icon" href="{{ asset('images/brand/icon-180.png') }}" sizes="180x180">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<meta name="theme-color" content="{{ config('brand.theme_color') }}">
<meta name="application-name" content="{{ config('brand.name') }}">
<meta property="og:site_name" content="{{ config('brand.name') }}">
<meta property="og:image" content="{{ asset(config('brand.og_image')) }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
