<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Mission;
use App\Models\ReferencePoint;
use App\Models\Technicien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionAnalysisFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_technicien_analysis_custom_period_filters_recent_missions_list(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $techUser = User::factory()->create(['role' => 'Technicien']);

        $tech = Technicien::query()->create([
            'user_id' => $techUser->id,
            'nom' => 'Tech',
            'prenom' => 'Filter',
            'telephone' => '55555555',
            'zone_intervention' => 'Sfax',
            'disponible' => true,
        ]);

        $inRangeReference = ReferencePoint::query()->create([
            'reference' => 'REF-IN-RANGE',
            'latitude' => 34.74060000,
            'longitude' => 10.76030000,
            'statut' => 'validé',
            'updated_by' => $admin->id,
        ]);

        $outRangeReference = ReferencePoint::query()->create([
            'reference' => 'REF-OUT-RANGE',
            'latitude' => 34.74070000,
            'longitude' => 10.76040000,
            'statut' => 'validé',
            'updated_by' => $admin->id,
        ]);

        $inRangeMission = Mission::query()->create([
            'reference_id' => $inRangeReference->id,
            'type_mission' => 'Réparation',
            'priorite' => 'Normale',
            'statut' => 'Terminée',
            'created_by' => $admin->id,
            'started_at' => now()->subDay(),
            'completed_at' => now(),
        ]);

        $outRangeMission = Mission::query()->create([
            'reference_id' => $outRangeReference->id,
            'type_mission' => 'Réparation',
            'priorite' => 'Normale',
            'statut' => 'Terminée',
            'created_by' => $admin->id,
            'started_at' => now()->subDays(70),
            'completed_at' => now()->subDays(69),
        ]);

        $outRangeMission->created_at = now()->subDays(70);
        $outRangeMission->save();

        Affectation::query()->create([
            'mission_id' => $inRangeMission->id,
            'technicien_id' => $tech->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);

        Affectation::query()->create([
            'mission_id' => $outRangeMission->id,
            'technicien_id' => $tech->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($techUser)->get(route('missions.analysis', [
            'period' => 'custom',
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee('REF-IN-RANGE');
        $response->assertDontSee('REF-OUT-RANGE');
    }

    public function test_custom_period_requires_start_and_end_dates(): void
    {
        $techUser = User::factory()->create(['role' => 'Technicien']);

        Technicien::query()->create([
            'user_id' => $techUser->id,
            'nom' => 'Tech',
            'prenom' => 'Validation',
            'telephone' => '66666666',
            'zone_intervention' => 'Sfax',
            'disponible' => true,
        ]);

        $response = $this->actingAs($techUser)
            ->from(route('missions.analysis'))
            ->get(route('missions.analysis', [
                'period' => 'custom',
            ]));

        $response->assertRedirect(route('missions.analysis'));
        $response->assertSessionHasErrors(['start_date', 'end_date']);
    }

    public function test_technicien_can_export_analysis_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $techUser = User::factory()->create(['role' => 'Technicien']);

        $tech = Technicien::query()->create([
            'user_id' => $techUser->id,
            'nom' => 'Tech',
            'prenom' => 'Export',
            'telephone' => '77777777',
            'zone_intervention' => 'Sfax',
            'disponible' => true,
        ]);

        $reference = ReferencePoint::query()->create([
            'reference' => 'REF-EXPORT-001',
            'latitude' => 34.74060000,
            'longitude' => 10.76030000,
            'statut' => 'validé',
            'updated_by' => $admin->id,
        ]);

        $mission = Mission::query()->create([
            'reference_id' => $reference->id,
            'type_mission' => 'Réparation',
            'priorite' => 'Normale',
            'statut' => 'Terminée',
            'created_by' => $admin->id,
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);

        Affectation::query()->create([
            'mission_id' => $mission->id,
            'technicien_id' => $tech->id,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($techUser)->get(route('missions.analysis.export', [
            'period' => '30d',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
