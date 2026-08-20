<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\KdpReportRows\Pages\ListKdpReportRows;
use App\Models\ImportBatch;
use App\Models\KdpReportRow;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KdpReportRowsSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_royalty_amount_is_the_primary_sort_column_when_clicked(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $user->roles()->attach($role);
        $this->actingAs($user);

        $batch = ImportBatch::create([
            'user_id' => $user->id,
            'import_type' => 'prior_royalties',
            'source_system' => 'amazon_kdp',
            'original_file_path' => 'private/test.csv',
            'original_file_name' => 'test.csv',
            'file_hash' => hash('sha256', 'sorting-test'),
            'detected_format' => 'csv',
            'status' => 'completed',
            'processed_by_ai' => false,
        ]);

        KdpReportRow::create([
            'user_id' => $user->id,
            'import_batch_id' => $batch->id,
            'row_fingerprint' => hash('sha256', 'small'),
            'report_type' => 'prior_royalties',
            'title' => 'Regalía pequeña',
            'total_earnings' => 10,
            'transaction_date' => '2026-08-20',
            'raw_data' => [],
            'normalized_data' => [],
        ]);
        KdpReportRow::create([
            'user_id' => $user->id,
            'import_batch_id' => $batch->id,
            'row_fingerprint' => hash('sha256', 'large'),
            'report_type' => 'prior_royalties',
            'title' => 'Regalía grande',
            'total_earnings' => 100,
            'transaction_date' => '2026-08-01',
            'raw_data' => [],
            'normalized_data' => [],
        ]);

        $component = Livewire::test(ListKdpReportRows::class);
        $component->call('sortTable', 'total_earnings');
        $component->call('sortTable', 'total_earnings');

        $this->assertSame(
            ['Regalía grande', 'Regalía pequeña'],
            $component->instance()->getTableRecords()->pluck('title')->all(),
        );
    }
}
