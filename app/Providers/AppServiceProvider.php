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
        Gate::define('access-admin', function (User $user): bool {
            return $user->role === 'Admin';
        });

        Gate::define('manage-techniciens', function (User $user): bool {
            return $user->role === 'Admin';
        });

        Gate::define('manage-missions', function (User $user): bool {
            return in_array($user->role, ['Admin', 'Dispatcher'], true);
        });

        Gate::define('manage-references', function (User $user): bool {
            return in_array($user->role, ['Admin', 'Dispatcher'], true);
        });

        Gate::define('view-mission', function (User $user, Mission $mission): bool {
            if (in_array($user->role, ['Admin', 'Dispatcher'], true)) {
                return true;
            }

            if ($user->role !== 'Technicien') {
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
            if ($user->role !== 'Technicien') {
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
