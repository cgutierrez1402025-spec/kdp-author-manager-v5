<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Widgets\ActivePromotionsWidget;
use App\Filament\Admin\Widgets\ExpiringKdpSelectWidget;
use App\Filament\Admin\Widgets\KdpImportedDataWidget;
use App\Filament\Admin\Widgets\MyTasksWidget;
use App\Filament\Admin\Widgets\RevenueChartWidget;
use App\Filament\Admin\Widgets\SummaryCardsWidget;
use App\Filament\Admin\Widgets\TopWorksByRevenueWidget;
use App\Filament\Admin\Widgets\UpcomingEventsWidget;
use App\Models\KdpReportRow;
use App\Models\RoyaltyEntry;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardDemoDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_seeded_business_data(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@kdpmanager.local')->firstOrFail();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->get('/admin/book-events')
            ->assertOk()
            ->assertSeeText('Presentación y firma demo');

        Livewire::test(ActivePromotionsWidget::class)->assertSee('Campaña demo 1');
        Livewire::test(MyTasksWidget::class)->assertSee('Revisar traducción inglesa');
        Livewire::test(UpcomingEventsWidget::class)->assertSee('Presentación y firma demo');
        Livewire::test(TopWorksByRevenueWidget::class)->assertSee('Obra de demostración 20');
        Livewire::test(KdpImportedDataWidget::class)
            ->assertSee('Regalías acumuladas por obra')
            ->assertSee('Obra de demostración 01');
        Livewire::test(ExpiringKdpSelectWidget::class)
            ->assertSee('Obra de demostración 01')
            ->assertSee('20 días');
        Livewire::test(RevenueChartWidget::class)
            ->assertSee('Regalías de los últimos seis periodos')
            ->assertSee('118.60 EUR');
    }

    public function test_dashboard_uses_a_bounded_grid_without_implicit_columns(): void
    {
        $this->assertSame(1, app(Dashboard::class)->getColumns());
        $this->assertSame('full', app(SummaryCardsWidget::class)->getColumnSpan());

        foreach ([
            MyTasksWidget::class,
            UpcomingEventsWidget::class,
            RevenueChartWidget::class,
            TopWorksByRevenueWidget::class,
        ] as $widget) {
            $this->assertSame(1, app($widget)->getColumnSpan());
        }
    }

    public function test_dashboard_renders_long_work_and_publication_titles_without_truncating_them(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@kdpmanager.local')->firstOrFail();
        $this->actingAs($admin);
        $longWorkTitle = 'Una obra con un título extraordinariamente largo que debe poder leerse completo en varias líneas dentro del panel';
        $longPublicationTitle = 'Una publicación importada desde Amazon KDP cuyo título completo no debe quedar oculto mediante puntos suspensivos';
        Work::where('slug', 'demo-obra-20')->update(['title_public' => $longWorkTitle]);
        KdpReportRow::query()->firstOrFail()->update(['title' => $longPublicationTitle]);

        Livewire::test(TopWorksByRevenueWidget::class)
            ->assertSee($longWorkTitle)
            ->assertSee('editorial-title', false);
        Livewire::test(KdpImportedDataWidget::class)
            ->assertSee($longPublicationTitle)
            ->assertDontSee('class="truncate"', false);
    }

    public function test_dashboard_only_shows_non_zero_kdp_rows_and_full_breakdown_orders_the_rest_after_them(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@kdpmanager.local')->firstOrFail();
        $this->actingAs($admin);
        $source = KdpReportRow::where('row_kind', 'royalty')->firstOrFail();
        $zeroRow = $source->replicate();
        $zeroRow->fill([
            'row_fingerprint' => hash('sha256', 'zero-dashboard-row'), 'title' => 'Obra todavía sin actividad',
            'units_sold' => 0, 'units_refunded' => 0, 'net_units_sold' => 0, 'kenp_read' => 0,
            'total_earnings' => 0, 'income_amount' => 0, 'normalized_data' => [],
        ])->save();

        Livewire::test(KdpImportedDataWidget::class)
            ->assertDontSee('Obra todavía sin actividad')
            ->assertSee('Ver desglose completo');
        Livewire::test(TopWorksByRevenueWidget::class)
            ->assertSeeInOrder(['Obra de demostración 20', 'Obra de demostración 01']);
        $this->get('/admin/desglose-informes-kdp')
            ->assertOk()
            ->assertSeeInOrder([$source->title, 'Obra todavía sin actividad']);
    }

    public function test_author_dashboard_renders_imported_kdp_data_and_separates_currencies(): void
    {
        $this->seed();
        $author = User::where('email', 'author@example.com')->firstOrFail();
        RoyaltyEntry::query()->delete();
        Cache::flush();
        $this->actingAs($author);

        $stats = app(SummaryCardsWidget::class)->getStats();

        $this->assertSame('kdp_report_rows', $stats['revenue_source']);
        $this->assertNotEmpty($stats['revenue_by_currency']);
        $this->assertArrayHasKey('EUR', $stats['revenue_by_currency']);

        Livewire::test(SummaryCardsWidget::class)
            ->assertSee('Regalías del periodo')
            ->assertSee(number_format($stats['revenue_by_currency']['EUR'], 2).' EUR')
            ->assertSee('Informes KDP');

        Livewire::test(KdpImportedDataWidget::class)
            ->assertSee('Rendimiento importado desde Amazon KDP')
            ->assertSee('Regalías acumuladas por obra');

        Livewire::test(RevenueChartWidget::class)
            ->assertSee('Informes KDP')
            ->assertSee('EUR');
    }

    public function test_admin_dashboard_renders_imported_data_from_all_authors(): void
    {
        $this->seed();
        RoyaltyEntry::query()->delete();
        Cache::flush();
        $admin = User::where('email', 'admin@kdpmanager.local')->firstOrFail();
        $this->actingAs($admin);

        $stats = app(SummaryCardsWidget::class)->getStats();
        $chart = app(RevenueChartWidget::class)->getChartData();

        $this->assertSame('kdp_report_rows', $stats['revenue_source']);
        $this->assertArrayHasKey('EUR', $stats['revenue_by_currency']);
        $this->assertArrayHasKey('EUR', $chart['series']);

        Livewire::test(SummaryCardsWidget::class)
            ->assertSee('Regalías del periodo')
            ->assertSee('Informes KDP');
        Livewire::test(RevenueChartWidget::class)
            ->assertSee('Informes KDP')
            ->assertSee('EUR');
        Livewire::test(KdpImportedDataWidget::class)
            ->assertSee('Rendimiento importado desde Amazon KDP')
            ->assertSee('Regalías acumuladas por obra');
    }
}
