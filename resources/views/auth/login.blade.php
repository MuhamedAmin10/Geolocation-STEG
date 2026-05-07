<x-guest-layout>
    <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="bg-gradient-to-r from-sky-50 via-white to-rose-50 px-5 py-4">
            <div class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50/80 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.22em] text-sky-700">
                Espace client
            </div>
            <h2 class="mt-3 text-[1.9rem] font-extrabold leading-tight text-slate-900 sm:text-[2.1rem]">
                Portail Client STEG
            </h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                Créez votre compte pour envoyer vos réclamations et suivre le statut de traitement en temps réel.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('register') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                Créer un compte client
            </a>
            <button
                type="button"
                onclick="document.getElementById('login-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); document.getElementById('email')?.focus();"
                class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                Se connecter
            </button>
        </div>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form id="login-form" method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
