<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Mission;
use App\Models\ReferencePoint;
use App\Models\Technicien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionAuthorizationAndFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_technicien_sees_only_his_assigned_missions(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        $techUser = User::factory()->create(['role' => 'Technicien']);
        $tech = Technicien::query()->create([
            'user_id' => $techUser->id,
            'nom' => 'Tech',
            'prenom' => 'One',
            'telephone' => '11111111',
            'zone_intervention' => 'Sfax',
            'disponible' => true,
        ]);

        $otherTechUser = User::factory()->create(['role' => 'Technicien']);
        $otherTech = Technicien::query()->create([
            'user_id' => $otherTechUser->id,
            'nom' => 'Tech',
            'prenom' => 'Two',
            'telephone' => '22222222',
            'zone_intervention' => 'Sfax',
            'disponible' => true,
        ]);

        $mineRef = ReferencePoint::query()->create([
            'reference' => 'REF-MINE-001',
            'latitude' => 34.74060000,
            'longitude' => 10.76030000,
            'statut' => 'validé',
            'updated_by' => $admin->id,
        ]);

        $otherRef = ReferencePoint::query()->create([
            'reference' => 'REF-OTHER-001',
            'latitude' => 34.75060000,
            'longitude' => 10.77030000,
            'statut' => 'validé',
            'updated_by' => $admin->id,
        ]);

        $mineMission = Mission::query()->create([
            'reference_id' => $mineRef->id,
            'type_mission' => 'Réparation',
            'priorite' => 'Normale',
            'statut' => 'Assignée',
            'created_by' => $admin->id,
        ]);

        $otherMission = Mission::query()->create([
            'reference_id' => $otherRef->id,
            'type_mission' => 'Coupure',
            'priorite' => 'Haute',
            'statut' => 'Assignée',
            'created_by' => $admin->id,
        ]);

        Affectation::query()->create([
            'mission_id' => $mineMission->id,
            'technicien_id' => $tech->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);

        Affectation::query()->create([
            'mission_id' => $otherMission->id,
            'technicien_id' => $otherTech->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($techUser)->get(route('missions.index'));

        $response->assertOk();
        $response->assertSee('REF-MINE-001');
        $response->assertDontSee('REF-OTHER-001');
    }

    public function test_technicien_cannot_create_a_mission(): void
    {
        $techUser = User::factory()->create(['role' => 'Technicien']);
        $assignableTechUser = User::factory()->create(['role' => 'Technicien']);
        $assignableTech = Technicien::query()->create([
            'user_id' => $assignableTechUser->id,
            'nom' => 'Assignable',
            'prenom' => 'Tech',
            'telephone' => '33333333',
            'zone_intervention' => 'Sfax',
            'disponible' => true,
        ]);

        $reference = ReferencePoint::query()->create([
            'reference' => 'REF-NEW-001',
            'latitude' => 34.74060000,
            'longitude' => 10.76030000,
            'statut' => 'validé',
        ]);

        $response = $this->actingAs($techUser)->post(route('missions.store'), [
            'reference_id' => $reference->id,
            'type_mission' => 'Réparation',
            'priorite' => 'Normale',
            'description' => 'Unauthorized create attempt',
            'statut' => 'Créée',
            'technicien_id' => $assignableTech->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('missions', 0);
    }

    public function test_manage_references_is_required_on_reference_edit_route(): void
    {
        $techUser = User::factory()->create(['role' => 'Technicien']);

        $reference = ReferencePoint::query()->create([
            'reference' => 'REF-EDIT-001',
            'latitude' => 34.74060000,
            'longitude' => 10.76030000,
            'statut' => 'validé',
        ]);

        $response = $this->actingAs($techUser)->get(route('reference-points.edit', $reference));

        $response->assertForbidden();
    }

    public function test_dispatcher_can_access_reference_edit_route(): void
    {
        $dispatcher = User::factory()->create(['role' => 'Dispatcher']);

        $reference = ReferencePoint::query()->create([
            'reference' => 'REF-EDIT-OK-001',
            'latitude' => 34.74060000,
            'longitude' => 10.76030000,
            'statut' => 'validé',
            'updated_by' => $dispatcher->id,
        ]);

        $response = $this->actingAs($dispatcher)->get(route('reference-points.edit', $reference));

        $response->assertOk();
    }

    public function test_store_mission_rejects_unknown_status(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        $reference = ReferencePoint::query()->create([
            'reference' => 'REF-VALIDATE-001',
            'latitude' => 34.74060000,
            'longitude' => 10.76030000,
            'statut' => 'validé',
            'updated_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('missions.store'), [
            'reference_id' => $reference->id,
            'type_mission' => 'Réparation',
            'priorite' => 'Normale',
            'description' => 'Validation test',
            'statut' => 'UNKNOWN_STATUS',
        ]);

        $response->assertSessionHasErrors('statut');
        $this->assertDatabaseCount('missions', 0);
    }
}
