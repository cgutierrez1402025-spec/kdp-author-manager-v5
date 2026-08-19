<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\ManuscriptVersions\Pages\EditManuscriptVersion;
use App\Filament\Admin\Resources\ManuscriptVersions\RelationManagers\ChaptersRelationManager;
use App\Filament\Admin\Resources\ManuscriptVersions\Widgets\VersionTreeWidget;
use App\Filament\Admin\Resources\Works\Pages\ViewWork;
use App\Filament\Admin\Resources\Works\RelationManagers\ManuscriptVersionsRelationManager;
use App\Filament\Admin\Resources\Works\RelationManagers\PublicationsRelationManager;
use App\Filament\Admin\Resources\Works\RelationManagers\SourcesRelationManager;
use App\Filament\Admin\Resources\Works\RelationManagers\TasksRelationManager;
use App\Models\ManuscriptVersion;
use App\Models\Role;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminResourcesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_render_every_registered_resource_index(): void
    {
        $user = User::factory()->create();
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $user->roles()->attach($admin);

        $this->actingAs($user);

        $paths = [
            '/admin/ai-tasks',
            '/admin/book-events',
            '/admin/book-promotions',
            '/admin/checklists',
            '/admin/event-books',
            '/admin/illustration-anchors',
            '/admin/kdp-metadata',
            '/admin/kdp-select-periods',
            '/admin/manuscript-versions',
            '/admin/marketplaces',
            '/admin/platforms',
            '/admin/promotion-costs',
            '/admin/promotion-daily-results',
            '/admin/prompts',
            '/admin/publications',
            '/admin/source-usages',
            '/admin/sources',
            '/admin/tasks',
            '/admin/works',
        ];

        foreach ($paths as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_admin_can_render_dashboard_and_every_resource_create_form(): void
    {
        $user = User::factory()->create();
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $user->roles()->attach($admin);

        $this->actingAs($user);

        $this->get('/admin')->assertOk();

        $paths = [
            '/admin/ai-tasks/create',
            '/admin/book-events/create',
            '/admin/book-promotions/create',
            '/admin/checklists/create',
            '/admin/event-books/create',
            '/admin/illustration-anchors/create',
            '/admin/kdp-metadata/create',
            '/admin/kdp-select-periods/create',
            '/admin/manuscript-versions/create',
            '/admin/marketplaces/create',
            '/admin/platforms/create',
            '/admin/promotion-costs/create',
            '/admin/promotion-daily-results/create',
            '/admin/prompts/create',
            '/admin/publications/create',
            '/admin/source-usages/create',
            '/admin/sources/create',
            '/admin/tasks/create',
            '/admin/works/create',
        ];

        foreach ($paths as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_author_only_sees_their_own_works_in_the_admin_panel(): void
    {
        $author = User::factory()->create();
        $otherAuthor = User::factory()->create();
        $role = Role::create(['name' => 'author', 'guard_name' => 'web']);
        $author->roles()->attach($role);

        Work::factory()->create([
            'user_id' => $author->id,
            'title_public' => 'Visible Author Work',
        ]);
        $otherWork = Work::factory()->create([
            'user_id' => $otherAuthor->id,
            'title_public' => 'Private Other Work',
        ]);

        $this->actingAs($author)
            ->get('/admin/works')
            ->assertOk()
            ->assertSee('Visible Author Work')
            ->assertDontSee('Private Other Work');

        $this->get("/admin/works/{$otherWork->id}")->assertNotFound();
        $this->get("/admin/works/{$otherWork->id}/edit")->assertNotFound();
    }

    public function test_user_without_an_application_role_cannot_access_filament(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_chapters_relation_manager_can_be_rendered(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'admin@kdpmanager.local')->firstOrFail());

        Livewire::test(ChaptersRelationManager::class, [
            'ownerRecord' => ManuscriptVersion::query()->firstOrFail(),
            'pageClass' => EditManuscriptVersion::class,
        ])->assertOk();
    }

    public function test_work_workspace_relation_tabs_can_be_rendered(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'admin@kdpmanager.local')->firstOrFail());
        $work = Work::query()->firstOrFail();

        foreach ([
            ManuscriptVersionsRelationManager::class,
            PublicationsRelationManager::class,
            TasksRelationManager::class,
            SourcesRelationManager::class,
        ] as $relationManager) {
            Livewire::test($relationManager, [
                'ownerRecord' => $work,
                'pageClass' => ViewWork::class,
            ])->assertOk();
        }
    }

    public function test_version_tree_widget_can_be_rendered(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'admin@kdpmanager.local')->firstOrFail());

        Livewire::test(VersionTreeWidget::class, [
            'record' => ManuscriptVersion::query()->firstOrFail(),
        ])->assertOk();
    }
}
