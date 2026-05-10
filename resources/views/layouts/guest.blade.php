<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @livewireStyles

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="brand-shell flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:px-6">
            <div class="mb-4">
                <a href="/">
                    <x-application-logo class="h-20 w-auto drop-shadow-sm" />
                </a>
            </div>

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white/95 p-6 shadow-xl backdrop-blur sm:p-8">
                {{ $slot }}
            </div>
        </div>

        @livewireScripts
    </body>
</html>
