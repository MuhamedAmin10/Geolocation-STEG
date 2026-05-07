<x-app-layout>
    <x-slot name="header">
        <div class="rounded-2xl bg-gradient-to-r from-slate-950 via-slate-800 to-amber-700 px-6 py-5 text-white shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-100">Notifications</p>
            <h2 class="mt-2 text-2xl font-bold">Préférences de notification</h2>
            <p class="mt-2 max-w-3xl text-sm text-amber-100/90">Choisissez les canaux et événements à recevoir pour les missions et les délais.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('notification-preferences.update') }}" class="panel-card overflow-hidden">
                @csrf
                @method('PUT')
                <div class="panel-head">
                    <h3>Canaux</h3>
                    <p>Les envois seront filtrés par ces options.</p>
                </div>
                <div class="grid gap-4 p-5 md:grid-cols-2">
                    @php
                        $toggles = [
                            'in_app' => 'Alertes dans l’application',
                            'email' => 'Email',
                            'sms' => 'SMS',
                            'whatsapp' => 'WhatsApp',
                            'mission_assigned' => 'Missions assignées',
                            'status_changed' => 'Changement de statut',
                            'sla_breached' => 'Dépassement SLA',
                            'time_reminder' => 'Rappel temps / timer',
                        ];
                    @endphp
                    @foreach ($toggles as $key => $label)
                        <label class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <span class="text-sm font-medium text-slate-800">{{ $label }}</span>
                            <input type="checkbox" name="{{ $key }}" value="1" @checked($preferences->$key) class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                        </label>
                    @endforeach
                </div>
                <div class="border-t border-slate-200 bg-slate-50 px-5 py-4">
                    <button type="submit" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
