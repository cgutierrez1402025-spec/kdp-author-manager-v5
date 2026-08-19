<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\ManuscriptVersions\Pages\CreateManuscriptVersion;
use App\Filament\Admin\Resources\Publications\Pages\CreatePublication;
use App\Filament\Admin\Resources\Works\Pages\CreateWork;
use App\Models\ManuscriptVersion;
use App\Models\Marketplace;
use App\Models\Permission;
use App\Models\Platform;
use App\Models\Role;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditorialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_work_from_filament_assigns_the_author_and_original_language(): void
    {
        $author = User::factory()->create();
        $role = Role::create(['name' => 'author', 'guard_name' => 'web']);
        $permissions = collect(['view_works', 'create_works', 'edit_works', 'delete_works'])
            ->map(fn (string $name) => Permission::create(['name' => $name, 'guard_name' => 'web']));
        $role->permissions()->attach($permissions->pluck('id'));
        $author->roles()->attach($role);

        $this->actingAs($author);

        Livewire::test(CreateWork::class)
            ->fillForm([
                'title' => 'Manual Laravel para autores',
                'title_internal' => 'Manual Laravel',
                'title_public' => 'Manual Laravel para autores',
                'author_name' => 'Autora de prueba',
                'original_language' => 'es',
                'status' => 'idea',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $work = Work::where('title_public', 'Manual Laravel para autores')->firstOrFail();

        $this->assertSame($author->id, $work->user_id);
        $this->assertDatabaseHas('work_languages', [
            'work_id' => $work->id,
            'language_code' => 'es',
            'translation_status' => 'original',
        ]);
    }

    public function test_author_can_create_a_final_manuscript_and_publication_for_their_work(): void
    {
        $author = User::factory()->create();
        $role = Role::create(['name' => 'author', 'guard_name' => 'web']);
        $permissions = collect(['view_works', 'create_works', 'edit_works', 'delete_works'])
            ->map(fn (string $name) => Permission::create(['name' => $name, 'guard_name' => 'web']));
        $role->permissions()->attach($permissions->pluck('id'));
        $author->roles()->attach($role);
        $work = Work::factory()->create(['user_id' => $author->id]);
        $language = $work->workLanguages()->create([
            'language_code' => $work->original_language,
            'translation_status' => 'original',
        ]);
        $platform = Platform::factory()->create();
        $marketplace = Marketplace::factory()->create(['platform_id' => $platform->id]);

        $this->actingAs($author);

        Livewire::test(CreateManuscriptVersion::class)
            ->fillForm([
                'work_id' => $work->id,
                'work_language_id' => $language->id,
                'version_number' => '1',
                'name' => 'Versión final',
                'status' => 'final',
                'html_content' => '<h1>Capítulo inicial</h1><p>Contenido de prueba.</p>',
                'is_final' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $manuscript = ManuscriptVersion::where('work_id', $work->id)->firstOrFail();
        $this->assertSame($author->id, $manuscript->created_by);

        Livewire::test(CreatePublication::class)
            ->fillForm([
                'work_id' => $work->id,
                'work_language_id' => $language->id,
                'manuscript_version_id' => $manuscript->id,
                'platform_id' => $platform->id,
                'marketplace_id' => $marketplace->id,
                'format' => 'ebook',
                'status' => 'draft',
                'price' => 4.99,
                'currency' => 'EUR',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('publications', [
            'work_id' => $work->id,
            'work_language_id' => $language->id,
            'manuscript_version_id' => $manuscript->id,
            'platform_id' => $platform->id,
            'marketplace_id' => $marketplace->id,
            'format' => 'ebook',
            'status' => 'draft',
        ]);
    }
}
