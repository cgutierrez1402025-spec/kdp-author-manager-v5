<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\ManuscriptVersion;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkLanguage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManuscriptVersioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_manuscript_versions_and_chapters(): void
    {
        $user = User::factory()->create();
        $work = Work::factory()->create(['user_id' => $user->id]);
        $workLanguage = WorkLanguage::create([
            'work_id' => $work->id,
            'language_code' => 'en',
            'translation_status' => 'original',
        ]);

        $version = ManuscriptVersion::create([
            'work_id' => $work->id,
            'work_language_id' => $workLanguage->id,
            'version_number' => '1',
            'name' => 'Draft 1',
            'status' => 'draft',
            'is_candidate' => false,
            'is_final' => false,
            'is_published' => false,
            'created_by' => $user->id,
        ]);

        $childVersion = $version->createChildVersion([
            'name' => 'Draft 2',
            'created_by' => $user->id,
        ]);

        $this->assertEquals('2', $childVersion->version_number);

        $chapter = Chapter::create([
            'manuscript_version_id' => $childVersion->id,
            'work_id' => $work->id,
            'chapter_order' => 1,
            'title' => 'Chapter 1',
        ]);

        $this->assertDatabaseHas('chapters', [
            'manuscript_version_id' => $childVersion->id,
            'title' => 'Chapter 1',
        ]);
    }
}
