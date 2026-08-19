<?php

namespace Tests\Feature;

use App\Models\ManuscriptVersion;
use App\Models\Marketplace;
use App\Models\Platform;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkLanguage;
use App\Services\EditorialIntegrityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EditorialIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_cannot_use_another_authors_work(): void
    {
        $author = User::factory()->create();
        $otherWork = Work::factory()->create();
        $language = WorkLanguage::create([
            'work_id' => $otherWork->id,
            'language_code' => 'es',
            'translation_status' => 'original',
        ]);

        $this->expectException(ValidationException::class);

        app(EditorialIntegrityService::class)->validateManuscript([
            'work_id' => $otherWork->id,
            'work_language_id' => $language->id,
        ], $author);
    }

    public function test_manuscript_language_must_belong_to_the_selected_work(): void
    {
        $author = User::factory()->create();
        $work = Work::factory()->create(['user_id' => $author->id]);
        $otherLanguage = WorkLanguage::create([
            'work_id' => Work::factory()->create()->id,
            'language_code' => 'en',
            'translation_status' => 'original',
        ]);

        $this->expectException(ValidationException::class);

        app(EditorialIntegrityService::class)->validateManuscript([
            'work_id' => $work->id,
            'work_language_id' => $otherLanguage->id,
        ], $author);
    }

    public function test_publication_requires_matching_final_manuscript_and_marketplace(): void
    {
        $author = User::factory()->create();
        $work = Work::factory()->create(['user_id' => $author->id]);
        $language = WorkLanguage::create([
            'work_id' => $work->id,
            'language_code' => 'es',
            'translation_status' => 'original',
        ]);
        $draft = ManuscriptVersion::create([
            'work_id' => $work->id,
            'work_language_id' => $language->id,
            'version_number' => '1',
            'status' => 'draft',
            'created_by' => $author->id,
        ]);
        $platform = Platform::factory()->create();
        $otherMarketplace = Marketplace::factory()->create();

        try {
            app(EditorialIntegrityService::class)->validatePublication([
                'work_id' => $work->id,
                'work_language_id' => $language->id,
                'manuscript_version_id' => $draft->id,
                'platform_id' => $platform->id,
                'marketplace_id' => $otherMarketplace->id,
            ], $author);

            $this->fail('Expected publication integrity validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('manuscript_version_id', $exception->errors());
        }

        $draft->update(['is_final' => true, 'status' => 'final']);

        try {
            app(EditorialIntegrityService::class)->validatePublication([
                'work_id' => $work->id,
                'work_language_id' => $language->id,
                'manuscript_version_id' => $draft->id,
                'platform_id' => $platform->id,
                'marketplace_id' => $otherMarketplace->id,
            ], $author);

            $this->fail('Expected marketplace integrity validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('marketplace_id', $exception->errors());
        }
    }
}
