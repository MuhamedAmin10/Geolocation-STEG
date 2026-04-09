<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_analysis_page(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        $response = $this->actingAs($admin)->get(route('admin.analysis'));

        $response->assertOk();
        $response->assertSee('Analyse admin');
    }

    public function test_non_admin_cannot_access_analysis_page(): void
    {
        $technicien = User::factory()->create(['role' => 'Technicien']);

        $response = $this->actingAs($technicien)->get(route('admin.analysis'));

        $response->assertForbidden();
    }
}