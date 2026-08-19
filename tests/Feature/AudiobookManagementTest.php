<?php

namespace Tests\Feature;

use App\Models\AudiobookEdition;
use App\Models\User;
use App\Services\Audiobooks\AudiobookWorkflowService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AudiobookManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_demo_audiobook_has_complete_traceability(): void
    {
        $edition = AudiobookEdition::with(['work', 'narrators', 'productions', 'chapters.assets', 'distributions', 'costs', 'royalties', 'qualityChecks'])->firstOrFail();

        $this->assertNotNull($edition->work);
        $this->assertCount(1, $edition->narrators);
        $this->assertCount(1, $edition->productions);
        $this->assertCount(1, $edition->chapters);
        $this->assertCount(1, $edition->chapters->first()->assets);
        $this->assertCount(1, $edition->distributions);
        $this->assertCount(1, $edition->costs);
        $this->assertCount(1, $edition->royalties);
        $this->assertCount(1, $edition->qualityChecks);
    }

    public function test_ready_edition_can_be_published_after_quality_validation(): void
    {
        $edition = AudiobookEdition::firstOrFail();
        $edition->update(['status' => 'ready']);

        app(AudiobookWorkflowService::class)->transition($edition, 'published');

        $this->assertSame('published', $edition->fresh()->status);
        $this->assertNotNull($edition->fresh()->published_at);
    }

    public function test_voice_replica_cannot_be_published_without_current_consent(): void
    {
        $edition = AudiobookEdition::firstOrFail();
        $edition->update(['status' => 'ready', 'production_method' => 'voice_replica']);
        $edition->narrators()->update(['voice_consent' => false]);

        $this->expectException(ValidationException::class);
        app(AudiobookWorkflowService::class)->transition($edition, 'published');
    }

    public function test_author_can_open_audiobook_and_narrator_management(): void
    {
        $author = User::where('email', 'author@example.com')->firstOrFail();

        $this->actingAs($author)->get('/admin/audiolibros')->assertOk();
        $this->actingAs($author)->get('/admin/narradores')->assertOk();
    }
}
