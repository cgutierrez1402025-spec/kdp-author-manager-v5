<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\BookEvents\Pages\CreateBookEvent;
use App\Filament\Admin\Resources\Sources\Pages\CreateSource;
use App\Filament\Admin\Resources\Tasks\Pages\CreateTask;
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
}
