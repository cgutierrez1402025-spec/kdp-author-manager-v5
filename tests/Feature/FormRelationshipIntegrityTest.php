<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\BookEvents\Pages\CreateBookEvent;
use App\Filament\Admin\Resources\Checklists\Pages\CreateChecklist;
use App\Filament\Admin\Resources\Sources\Pages\CreateSource;
use App\Filament\Admin\Resources\Tasks\Pages\CreateTask;
use App\Filament\Admin\Resources\TaskTypes\Pages\CreateTaskType;
use App\Models\Publication;
use App\Models\TaskType;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FormRelationshipIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_form_requires_and_persists_its_work_relationship(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@kdpmanager.local')->firstOrFail();
        $work = Work::where('slug', 'demo-obra-01')->firstOrFail();
        $this->actingAs($admin);

        Livewire::test(CreateSource::class)
            ->fillForm([
                'work_id' => $work->id,
                'title' => 'Fuente vinculada desde formulario',
                'source_type' => 'book',
                'language_code' => 'es',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('sources', [
            'work_id' => $work->id,
            'title' => 'Fuente vinculada desde formulario',
        ]);
    }

    public function test_task_and_event_forms_fill_their_user_relationships(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@kdpmanager.local')->firstOrFail();
        $work = Work::where('slug', 'demo-obra-01')->firstOrFail();
        $this->actingAs($admin);

        Livewire::test(CreateTask::class)
            ->fillForm([
                'work_id' => $work->id,
                'title' => 'Tarea creada desde formulario',
                'status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::test(CreateBookEvent::class)
            ->fillForm([
                'title' => 'Evento creado desde formulario',
                'event_type' => 'presentation',
                'event_date' => now()->addMonth()->toDateString(),
                'status' => 'planned',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tasks', ['title' => 'Tarea creada desde formulario', 'created_by' => $admin->id]);
        $this->assertDatabaseHas('book_events', ['title' => 'Evento creado desde formulario', 'user_id' => $admin->id]);
    }

    public function test_checklist_can_be_created_for_a_publication(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@kdpmanager.local')->firstOrFail();
        $publication = Publication::query()->with('work')->firstOrFail();
        $this->actingAs($admin);

        Livewire::test(CreateChecklist::class)
            ->fillForm([
                'work_id' => $publication->work_id,
                'publication_id' => $publication->id,
                'name' => 'Checklist de publicación',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('checklists', [
            'work_id' => $publication->work_id,
            'publication_id' => $publication->id,
            'name' => 'Checklist de publicación',
        ]);
    }

    public function test_user_can_create_a_task_type_and_assign_it_to_a_task(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@kdpmanager.local')->firstOrFail();
        $work = Work::query()->firstOrFail();
        $this->actingAs($admin);

        Livewire::test(CreateTaskType::class)
            ->fillForm([
                'name' => 'Revisión de accesibilidad',
                'description' => 'Comprobaciones de accesibilidad editorial',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $type = TaskType::where('name', 'Revisión de accesibilidad')->firstOrFail();

        Livewire::test(CreateTask::class)
            ->fillForm([
                'work_id' => $work->id,
                'task_type_id' => $type->id,
                'title' => 'Comprobar accesibilidad',
                'status' => 'pending',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Comprobar accesibilidad',
            'task_type_id' => $type->id,
        ]);
    }
}
