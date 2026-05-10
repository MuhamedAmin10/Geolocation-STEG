<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0369a1">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <link rel="manifest" href="{{ asset('manifest.json') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600,700&display=swap" rel="stylesheet" />

        @livewireStyles

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900" x-data="sidebarLayout('{{ auth()->user()?->role }}')" x-init="init()" :class="isDark ? 'theme-dark' : 'theme-light'">
        <div class="brand-shell min-h-screen lg:flex">
            <div class="hidden lg:sticky lg:top-0 lg:block lg:h-screen lg:shrink-0">
                @include('layouts.sidebar')
            </div>

            <div
                x-show="mobileOpen"
                x-transition.opacity
                class="fixed inset-0 z-40 bg-slate-950/60 lg:hidden"
                @click="mobileOpen = false"
            ></div>

            <div x-show="mobileOpen" x-transition class="fixed inset-y-0 left-0 z-50 lg:hidden">
                @include('layouts.sidebar')
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between border-b border-slate-200 bg-white/90 px-4 py-3 shadow-sm backdrop-blur lg:hidden">
                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 transition hover:bg-slate-50"
                        @click="mobileOpen = true"
                        aria-label="Open menu"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 6h16"></path>
                            <path d="M4 12h16"></path>
                            <path d="M4 18h10"></path>
                        </svg>
                    </button>
                    <div class="text-sm font-semibold tracking-wide text-slate-800">Mission Manager</div>
                    <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ auth()->user()?->name }}</a>
                </div>

                @isset($header)
                    <header class="border-b border-slate-200 bg-white/90 shadow-sm">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="pb-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').catch(() => {});
                });
            }
        </script>
        @stack('scripts')
    </body>
</html>
