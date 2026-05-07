<?php

namespace App\Providers;

use App\Models\Mission;
use App\Models\Technicien;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            return strcasecmp($user->role ?? '', 'Admin') === 0 ? true : null;
        });

        Gate::define('access-admin', function (User $user): bool {
            return strcasecmp($user->role ?? '', 'Admin') === 0;
        });

        Gate::define('manage-techniciens', function (User $user): bool {
            return strcasecmp($user->role ?? '', 'Admin') === 0;
        });

        Gate::define('manage-missions', function (User $user): bool {
            $role = strtolower(trim((string) ($user->role ?? '')));
            return in_array($role, ['admin', 'dispatcher'], true);
        });

        Gate::define('manage-references', function (User $user): bool {
            $role = strtolower(trim((string) ($user->role ?? '')));
            return in_array($role, ['admin', 'dispatcher'], true);
        });

        Gate::define('view-mission', function (User $user, Mission $mission): bool {
            $role = strtolower(trim((string) ($user->role ?? '')));
            if (in_array($role, ['admin', 'dispatcher'], true)) {
                return true;
            }

            if ($role !== 'technicien') {
                return false;
            }

            $technicienId = Technicien::query()
                ->where('user_id', $user->id)
                ->value('id');

            if (!$technicienId) {
                return false;
            }

            return $mission->affectations()
                ->where('technicien_id', $technicienId)
                ->exists();
        });

        Gate::define('work-mission', function (User $user, Mission $mission): bool {
            $role = strtolower(trim((string) ($user->role ?? '')));
            if ($role !== 'technicien') {
                return false;
            }

            $technicienId = Technicien::query()
                ->where('user_id', $user->id)
                ->value('id');

            if (!$technicienId) {
                return false;
            }

            return $mission->affectations()
                ->where('technicien_id', $technicienId)
                ->exists();
        });
    }
}
