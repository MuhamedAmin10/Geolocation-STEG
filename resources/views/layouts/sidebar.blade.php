@php
    use App\Models\Mission;
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();
    $role = $user?->role;
    $normalizedRole = strtolower(trim((string) $role));
    $unassignedCount = in_array($role, ['Admin', 'Dispatcher'], true)
        ? Mission::query()->whereDoesntHave('affectations')->count()
        : 0;

    $statusLabel = app()->environment('production') ? 'Online' : 'Maintenance';
    $statusClass = app()->environment('production') ? 'bg-emerald-400' : 'bg-amber-400';

    $adminMenu = [
        ['label' => 'Dashboard', 'icon' => 'DB', 'route' => 'admin.dashboard', 'fallback' => 'dashboard'],
        ['label' => 'Missions', 'icon' => 'MS', 'route' => 'missions.index', 'badge' => $unassignedCount],
        ['label' => 'Reference Search', 'icon' => 'RS', 'route' => 'reference.search'],
        ['label' => 'Reference Points', 'icon' => 'RF', 'route' => 'dashboard'],
        ['label' => 'Mission Map', 'icon' => 'MP', 'route' => 'missions.map'],
        ['label' => 'Technicians', 'icon' => 'TC', 'route' => 'admin.techniciens.index'],
        ['label' => 'Analysis', 'icon' => 'AN', 'route' => 'missions.analysis'],
        ['label' => 'Audit Logs', 'icon' => 'AL', 'route' => 'admin.analysis'],
        ['label' => 'Notifications', 'icon' => 'NT', 'route' => 'notification-preferences.edit'],
        ['label' => 'Settings', 'icon' => 'ST', 'route' => 'profile.edit'],
    ];

    $dispatcherMenu = [
        ['label' => 'Dashboard', 'icon' => 'DB', 'route' => 'dashboard'],
        ['label' => 'Missions', 'icon' => 'MS', 'route' => 'missions.index', 'badge' => $unassignedCount],
        ['label' => 'Create Mission', 'icon' => 'NM', 'route' => 'missions.create'],
        ['label' => 'Reference Search', 'icon' => 'RS', 'route' => 'reference.search'],
        ['label' => 'Reference Points', 'icon' => 'RF', 'route' => 'dashboard'],
        ['label' => 'Mission Map', 'icon' => 'MP', 'route' => 'missions.map'],
        ['label' => 'Analysis', 'icon' => 'AN', 'route' => 'missions.analysis'],
        ['label' => 'Notifications', 'icon' => 'NT', 'route' => 'notification-preferences.edit'],
        ['label' => 'My Team', 'icon' => 'TM', 'route' => 'admin.techniciens.index', 'fallback' => 'missions.index'],
    ];

    $technicianMenu = [
        ['label' => 'My Missions', 'icon' => 'MS', 'route' => 'missions.index', 'params' => ['mine' => 1], 'activeQuery' => ['mine' => '1', 'active_scope' => null, 'active_status' => null]],
        ['label' => 'Reference Search', 'icon' => 'RS', 'route' => 'reference.search'],
        ['label' => 'Collect References', 'icon' => 'CR', 'route' => 'references.collect'],
        ['label' => "Today's Schedule", 'icon' => 'SC', 'route' => 'technician.schedule'],
        ['label' => 'Time Tracker', 'icon' => 'TT', 'route' => 'technician.tracker'],
        ['label' => 'My Performance', 'icon' => 'PR', 'route' => 'missions.analysis'],
        ['label' => 'Notifications', 'icon' => 'NT', 'route' => 'notification-preferences.edit'],
        ['label' => 'Profile', 'icon' => 'PF', 'route' => 'profile.edit'],
    ];

    $clientMenu = [
        ['label' => 'Portal', 'icon' => 'CP', 'route' => 'client.portal'],
        ['label' => 'Notifications', 'icon' => 'NT', 'route' => 'notification-preferences.edit'],
        ['label' => 'Profile', 'icon' => 'PF', 'route' => 'profile.edit'],
    ];

    $menu = match ($normalizedRole) {
        'admin' => $adminMenu,
        'dispatcher' => $dispatcherMenu,
        'client' => $clientMenu,
        default => $technicianMenu,
    };
@endphp

<aside
    x-cloak
    class="flex h-screen border-r border-slate-200 bg-slate-900 text-slate-100"
>
    <div
        :class="sidebarCollapsed ? 'w-12 sm:w-16' : 'w-64'"
        class="relative flex h-full flex-col transition-all duration-300"
    >
        <div class="flex items-center justify-between border-b border-slate-800 px-3 py-3">
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 bg-slate-800 text-sm transition hover:bg-slate-700"
                @click="toggleSidebar()"
                aria-label="Toggle sidebar"
                :aria-expanded="(!sidebarCollapsed).toString()"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 6h16"></path>
                    <path d="M4 12h16"></path>
                    <path d="M4 18h10"></path>
                </svg>
            </button>

            <div class="overflow-hidden text-right" x-show="!sidebarCollapsed" x-transition>
                <div class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Mission Manager</div>
                <div class="truncate text-sm font-semibold">{{ $user?->name }}</div>
            </div>
        </div>

        @if (in_array($role, ['Admin', 'Dispatcher'], true) || $normalizedRole === 'client')
            <div class="border-b border-slate-800 p-3">
                @if (in_array($role, ['Admin', 'Dispatcher'], true))
                <a
                    href="{{ route('missions.create') }}"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-brand-primary px-3 py-2 text-sm font-semibold text-white transition hover:bg-brand-primary-dark"
                    :class="sidebarCollapsed ? 'px-0' : ''"
                    title="New Mission"
                >
                    <span>+</span>
                    <span class="ml-2" x-show="!sidebarCollapsed" x-transition>New Mission</span>
                </a>
                @elseif ($normalizedRole === 'client')
                <a
                    href="{{ route('client.portal') }}"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-brand-primary px-3 py-2 text-sm font-semibold text-white transition hover:bg-brand-primary-dark"
                    :class="sidebarCollapsed ? 'px-0' : ''"
                    title="Portal"
                >
                    <span>CP</span>
                    <span class="ml-2" x-show="!sidebarCollapsed" x-transition>Client Portal</span>
                </a>
                @endif
            </div>
        @endif

        <nav class="flex-1 space-y-1 overflow-y-auto p-2">
            @foreach ($menu as $item)
                @php
                    $targetRoute = Route::has($item['route']) ? $item['route'] : ($item['fallback'] ?? 'dashboard');
                    $routeParams = $item['params'] ?? [];
                    $activeQuery = $item['activeQuery'] ?? null;
                    $isActive = request()->routeIs($targetRoute);

                    if ($isActive && is_array($activeQuery)) {
                        foreach ($activeQuery as $queryKey => $expectedValue) {
                            $actualValue = request()->query($queryKey);

                            if ($expectedValue === null) {
                                if ($actualValue !== null && $actualValue !== '') {
                                    $isActive = false;
                                    break;
                                }
                                continue;
                            }

                            if ((string) $actualValue !== (string) $expectedValue) {
                                $isActive = false;
                                break;
                            }
                        }
                    }
                @endphp
                <a
                    href="{{ route($targetRoute, $routeParams) }}"
                    class="group flex items-center rounded-xl px-2 py-2 text-sm font-medium transition"
                    :class="sidebarCollapsed ? 'justify-center' : 'justify-between'"
                    @class([
                        'bg-brand-primary/90 text-white' => $isActive,
                        'text-slate-200 hover:bg-slate-800 hover:text-white' => !$isActive,
                    ])
                    title="{{ $item['label'] }}"
                >
                    <span class="inline-flex items-center gap-2">
                        <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-slate-800 text-[11px] font-semibold text-slate-100 group-hover:bg-slate-700">{{ $item['icon'] }}</span>
                        <span x-show="!sidebarCollapsed" x-transition>{{ $item['label'] }}</span>
                    </span>

                    @if (!empty($item['badge']))
                        <span
                            x-show="!sidebarCollapsed"
                            x-transition
                            class="rounded-full bg-amber-400 px-2 py-0.5 text-[11px] font-semibold text-slate-900"
                        >
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="border-t border-slate-800 p-3">
            <div class="mb-3 flex items-center gap-2" :class="sidebarCollapsed ? 'justify-center' : ''">
                <span class="h-2.5 w-2.5 rounded-full {{ $statusClass }}"></span>
                <span class="text-xs font-semibold text-slate-300" x-show="!sidebarCollapsed" x-transition>{{ $statusLabel }}</span>
            </div>

            <button
                type="button"
                @click="toggleTheme()"
                class="inline-flex w-full items-center justify-center rounded-xl border border-slate-700 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-200 transition hover:bg-slate-800"
                :class="sidebarCollapsed ? 'px-0' : ''"
                title="Toggle theme"
            >
                <span x-text="isDark ? 'Mode clair' : 'Mode sombre'"></span>
            </button>

            <div class="mt-3 text-center text-xs text-slate-400" x-show="!sidebarCollapsed" x-transition>
                <a href="{{ route('profile.edit') }}" class="hover:text-white">Profil</a>
                <span class="mx-1">•</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="hover:text-white">Déconnexion</button>
                </form>
            </div>
        </div>
    </div>

</aside>
