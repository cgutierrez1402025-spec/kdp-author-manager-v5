<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivityLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_changes_are_written_once_to_the_canonical_activity_log(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $work = Work::factory()->create(['user_id' => $user->id, 'status' => 'idea']);
        $work->update(['status' => 'review']);

        $this->assertSame(2, DB::table('activity_log')->where('subject_type', Work::class)->count());
        $this->assertSame(0, DB::table('activity_logs')->count());
    }
}
