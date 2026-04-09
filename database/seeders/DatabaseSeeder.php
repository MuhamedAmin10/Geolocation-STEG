<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ReferencePoint;
use App\Models\Technicien;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultPassword = env('DEFAULT_USER_PASSWORD', 'password');

        $adminEmail = env('DEFAULT_ADMIN_EMAIL', 'admin@steg.tn');
        $adminName = env('DEFAULT_ADMIN_NAME', 'Admin STEG');

        $dispatcherEmail = env('DEFAULT_DISPATCHER_EMAIL', 'dispatcher@steg.tn');
        $dispatcherName = env('DEFAULT_DISPATCHER_NAME', 'Dispatcher STEG');

        $tech1Email = env('DEFAULT_TECH1_EMAIL', 'tech1@steg.tn');
        $tech1Name = env('DEFAULT_TECH1_NAME', 'Technicien 1');

        $tech2Email = env('DEFAULT_TECH2_EMAIL', 'tech2@steg.tn');
        $tech2Name = env('DEFAULT_TECH2_NAME', 'Technicien 2');

        $admin = User::query()->updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($defaultPassword),
                'role' => 'Admin',
                'active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => $dispatcherEmail],
            [
                'name' => $dispatcherName,
                'password' => Hash::make($defaultPassword),
                'role' => 'Dispatcher',
                'active' => true,
            ]
        );

        $techUser1 = User::query()->updateOrCreate(
            ['email' => $tech1Email],
            [
                'name' => $tech1Name,
                'password' => Hash::make($defaultPassword),
                'role' => 'Technicien',
                'active' => true,
            ]
        );

        $techUser2 = User::query()->updateOrCreate(
            ['email' => $tech2Email],
            [
                'name' => $tech2Name,
                'password' => Hash::make($defaultPassword),
                'role' => 'Technicien',
                'active' => true,
            ]
        );

        Technicien::query()->updateOrCreate(
            ['user_id' => $techUser1->id],
            [
                'nom' => 'Ali',
                'prenom' => 'Mohamed',
                'telephone' => '+21620123456',
                'zone_intervention' => 'Sfax Ville',
                'competences' => 'Électricité, Gaz',
                'disponible' => true,
            ]
        );

        Technicien::query()->updateOrCreate(
            ['user_id' => $techUser2->id],
            [
                'nom' => 'Ben Salah',
                'prenom' => 'Ahmed',
                'telephone' => '+21620654321',
                'zone_intervention' => 'Sfax Sud',
                'competences' => 'Électricité',
                'disponible' => true,
            ]
        );

        $references = [
            ['717717770', 34.7406, 10.7603, 'Avenue Habib Bourguiba, Sfax', 'Sfax', 'Sfax Ville'],
            ['717717771', 34.7500, 10.7700, 'Route de Tunis, Sfax', 'Sfax', 'Sfax Nord'],
            ['717717772', 34.7300, 10.7500, 'Avenue de la Liberté, Sfax', 'Sfax', 'Sfax Ville'],
            ['717717773', 34.7450, 10.7650, 'Rue Ali Bach Hamba, Sfax', 'Sfax', 'Sfax Ville'],
            ['717717774', 34.7550, 10.7750, 'Route Menzel Chaker, Sfax', 'Sfax', 'Sfax Nord'],
        ];

        foreach ($references as $ref) {
            ReferencePoint::query()->updateOrCreate(
                ['reference' => $ref[0]],
                [
                    'latitude' => $ref[1],
                    'longitude' => $ref[2],
                    'adresse' => $ref[3],
                    'gouvernorat' => $ref[4],
                    'delegation' => $ref[5],
                    'precision_m' => random_int(5, 20),
                    'statut' => 'validé',
                    'updated_by' => $admin->id,
                ]
            );
        }

        if (App::environment('production')) {
            $this->command?->warn('Seed completed in production. Rotate DEFAULT_USER_PASSWORD or remove it after first bootstrap.');
        }
    }
}
