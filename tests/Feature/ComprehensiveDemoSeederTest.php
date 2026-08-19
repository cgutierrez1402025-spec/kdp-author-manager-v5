<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComprehensiveDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_functional_table_receives_coherent_demo_data(): void
    {
        $this->seed();

        $technicalTables = [
            'activity_logs',
            'cache',
            'cache_locks',
            'failed_jobs',
            'job_batches',
            'jobs',
            'migrations',
            'password_reset_tokens',
            'sessions',
            'sqlite_sequence',
        ];

        $tables = collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table'"))
            ->pluck('name')
            ->diff($technicalTables);

        foreach ($tables as $table) {
            $this->assertGreaterThan(0, DB::table($table)->count(), "The {$table} table has no demo data.");
        }

        $this->assertEmpty(DB::select('PRAGMA foreign_key_check'));
    }
}
