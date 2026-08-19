<?php

namespace Tests\Feature;

use App\Filament\Admin\Widgets\RevenueChartWidget;
use App\Filament\Admin\Widgets\SummaryCardsWidget;
use App\Models\Publication;
use App\Models\Role;
use App\Models\RoyaltyEntry;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_dashboard_aggregates_only_their_data_and_uses_private_cache_keys(): void
    {
        $authorRole = Role::create(['name' => 'author', 'guard_name' => 'web']);
        $author = User::factory()->create();
        $otherAuthor = User::factory()->create();
        $author->roles()->attach($authorRole);
        $otherAuthor->roles()->attach($authorRole);

        $ownWork = Work::factory()->create(['user_id' => $author->id]);
        $otherWork = Work::factory()->create(['user_id' => $otherAuthor->id]);
        $ownPublication = Publication::factory()->published()->create(['work_id' => $ownWork->id]);
        $otherPublication = Publication::factory()->published()->create(['work_id' => $otherWork->id]);

        RoyaltyEntry::factory()->create([
            'publication_id' => $ownPublication->id,
            'year' => now()->year,
            'month' => now()->month,
            'total_royalty' => 125.50,
        ]);
        RoyaltyEntry::factory()->create([
            'publication_id' => $otherPublication->id,
            'year' => now()->year,
            'month' => now()->month,
            'total_royalty' => 999.99,
        ]);

        $this->actingAs($author);

        $stats = app(SummaryCardsWidget::class)->getStats();
        $chart = app(RevenueChartWidget::class)->getChartData();

        $this->assertSame(1, $stats['total_works']);
        $this->assertSame(125.5, (float) $stats['monthly_revenue']);
        $this->assertSame(125.5, (float) collect($chart['data'])->last());
        $this->assertTrue(Cache::has("dashboard:user:{$author->id}:summary"));
        $this->assertFalse(Cache::has("dashboard:user:{$otherAuthor->id}:summary"));
    }

    public function test_admin_dashboard_can_aggregate_all_authors(): void
    {
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        Work::factory()->count(2)->create();

        $this->actingAs($admin);

        $stats = app(SummaryCardsWidget::class)->getStats();

        $this->assertSame(2, $stats['total_works']);
    }
}
