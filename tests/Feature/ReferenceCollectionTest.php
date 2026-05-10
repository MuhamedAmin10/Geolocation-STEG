<?php

namespace Tests\Feature;

use App\Models\ReferenceCollectionScan;
use App\Models\ReferencePoint;
use App\Models\Technicien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceCollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_technician_scan_creates_reference_when_missing(): void
    {
        $technicianUser = User::factory()->create(['role' => 'Technicien']);

        Technicien::query()->create([
            'user_id' => $technicianUser->id,
            'nom' => 'Collector',
            'prenom' => 'One',
            'telephone' => '11111111',
            'zone_intervention' => 'Sfax',
            'disponible' => true,
        ]);

        $response = $this->actingAs($technicianUser)->postJson(route('references.collect.store'), [
            'reference_code' => 'REF-NEW-100',
            'meter_type' => 'electrique',
            'latitude' => 34.7406,
            'longitude' => 10.7603,
            'accuracy_m' => 6.3,
            'notes' => 'Nouveau compteur detecte',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('was_created', true);

        $reference = ReferencePoint::query()->where('reference', 'REF-NEW-100')->first();
        $this->assertNotNull($reference);
        $this->assertSame('electrique', $reference->meter_type);

        $scan = ReferenceCollectionScan::query()->first();
        $this->assertNotNull($scan);
        $this->assertTrue($scan->was_created);
    }

    public function test_technician_scan_logs_existing_reference_without_creating_duplicate(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $technicianUser = User::factory()->create(['role' => 'Technicien']);

        Technicien::query()->create([
            'user_id' => $technicianUser->id,
            'nom' => 'Collector',
            'prenom' => 'Two',
            'telephone' => '22222222',
            'zone_intervention' => 'Sfax',
            'disponible' => true,
        ]);

        ReferencePoint::query()->create([
            'reference' => 'REF-EXIST-200',
            'meter_type' => 'mechanique',
            'latitude' => 34.7406,
            'longitude' => 10.7603,
            'statut' => 'validé',
            'updated_by' => $admin->id,
        ]);

        $response = $this->actingAs($technicianUser)->postJson(route('references.collect.store'), [
            'reference_code' => 'REF-EXIST-200',
            'meter_type' => 'autre',
            'latitude' => 34.7407,
            'longitude' => 10.7604,
            'accuracy_m' => 8.1,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('was_created', false);

        $this->assertSame(1, ReferencePoint::query()->where('reference', 'REF-EXIST-200')->count());

        $scan = ReferenceCollectionScan::query()->first();
        $this->assertNotNull($scan);
        $this->assertFalse($scan->was_created);
    }
}