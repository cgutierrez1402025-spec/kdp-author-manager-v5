<?php

namespace Tests\Feature;

use App\Models\Edition;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkLanguage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_work_with_language_and_edition(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $work = Work::create([
            'user_id' => $user->id,
            'title_internal' => 'Test Book',
            'title_public' => 'Test Book Public',
            'author_name' => 'Test Author',
            'original_language' => 'en',
            'status' => 'idea',
        ]);

        WorkLanguage::create([
            'work_id' => $work->id,
            'language_code' => 'en',
            'translation_status' => 'original',
        ]);

        Edition::create([
            'work_id' => $work->id,
            'work_language_id' => 1,
            'edition_number' => 1,
            'edition_type' => 'original',
        ]);

        $this->assertDatabaseHas('works', [
            'title_public' => 'Test Book Public',
        ]);

        $this->assertDatabaseHas('work_languages', [
            'work_id' => $work->id,
            'language_code' => 'en',
        ]);

        $this->assertDatabaseHas('editions', [
            'work_id' => $work->id,
            'edition_number' => 1,
        ]);
    }
}
