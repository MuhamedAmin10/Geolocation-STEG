<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Mission;
use App\Models\MissionReferenceScan;
use App\Models\ReferencePoint;
use App\Models\Technicien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionReferenceScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_technicien_can_store_reference_scan_with_gps_and_meter_type(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $techUser = User::factory()->create(['role' => 'Technicien']);

        $technicien = Technicien::query()->create([
            'user_id' => $techUser->id,
            'nom' => 'Tech',
            'prenom' => 'Scan',
            'telephone' => '55555555',
            'zone_intervention' => 'Sfax',
            'disponible' => true,
        ]);

        $reference = ReferencePoint::query()->create([
            'reference' => 'COMP-10001',
            'latitude' => 34.74060000,
            'longitude' => 10.76030000,
            'adresse' => 'Rue test',
            'gouvernorat' => 'Sfax',
            'delegation' => 'Sfax ville',
            'precision_m' => 5,
            'statut' => 'validé',
            'updated_by' => $admin->id,
        ]);

        $mission = Mission::query()->create([
            'reference_id' => $reference->id,
            'type_mission' => 'Inspection',
            'priorite' => 'Normale',
            'statut' => 'En cours',
            'created_by' => $admin->id,
        ]);

        Affectation::query()->create([
            'mission_id' => $mission->id,
            'technicien_id' => $technicien->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($techUser)->postJson(route('missions.reference-scans', $mission), [
            'qr_code' => 'COMP-10001',
            'compteur_type' => 'electrique',
            'latitude' => 34.74061000,
            'longitude' => 10.76031000,
            'accuracy_m' => 8.5,
            'notes' => 'Boitier propre',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('valid', true);

        $scan = MissionReferenceScan::query()->first();
        $this->assertNotNull($scan);
        $this->assertSame($mission->id, $scan->mission_id);
        $this->assertSame($technicien->id, $scan->technicien_id);
        $this->assertSame('COMP-10001', $scan->reference_code);
        $this->assertSame('electrique', $scan->compteur_type);
        $this->assertTrue($scan->is_match);
        $this->assertNotNull($scan->distance_m);
    }
}