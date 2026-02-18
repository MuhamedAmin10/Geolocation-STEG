<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ReferencePoint;
use App\Models\Technicien;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@steg.tn'],
            [
                'name' => 'Admin STEG',
                'password' => Hash::make('password'),
                'role' => 'Admin',
                'active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'dispatcher@steg.tn'],
            [
                'name' => 'Dispatcher STEG',
                'password' => Hash::make('password'),
                'role' => 'Dispatcher',
                'active' => true,
            ]
        );

        $techUser1 = User::query()->updateOrCreate(
            ['email' => 'tech1@steg.tn'],
            [
                'name' => 'Technicien 1',
                'password' => Hash::make('password'),
                'role' => 'Technicien',
                'active' => true,
            ]
        );

        $techUser2 = User::query()->updateOrCreate(
            ['email' => 'tech2@steg.tn'],
            [
                'name' => 'Technicien 2',
                'password' => Hash::make('password'),
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
    }
}
