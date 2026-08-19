<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeededAdminResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_registered_resource_renders_its_seeded_list_and_edit_form(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'admin@kdpmanager.local')->firstOrFail());

        $resources = [
            'ai-tasks' => 'ai_tasks',
            'book-events' => 'book_events',
            'book-promotions' => 'book_promotions',
            'checklists' => 'checklists',
            'event-books' => 'event_books',
            'illustration-anchors' => 'illustration_anchors',
            'kdp-metadata' => 'kdp_metadata',
            'kdp-select-periods' => 'kdp_select_periods',
            'manuscript-versions' => 'manuscript_versions',
            'marketplaces' => 'marketplaces',
            'platforms' => 'platforms',
            'promotion-costs' => 'promotion_costs',
            'promotion-daily-results' => 'promotion_daily_results',
            'prompts' => 'prompts',
            'publications' => 'publications',
            'source-usages' => 'source_usages',
            'sources' => 'sources',
            'tasks' => 'tasks',
            'works' => 'works',
        ];

        foreach ($resources as $slug => $table) {
            $id = DB::table($table)->min('id');
            $this->assertNotNull($id, "No seeded record exists in {$table}.");
            $this->get("/admin/{$slug}")->assertOk();
            $this->get("/admin/{$slug}/{$id}/edit")->assertOk();
        }
    }
}
