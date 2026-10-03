<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">

@fluxAppearance

@vite(['resources/css/app.css', 'resources/js/app.js'])
