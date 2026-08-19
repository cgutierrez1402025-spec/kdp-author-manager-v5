<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Widgets\ActivePromotionsWidget;
use App\Filament\Admin\Widgets\ExpiringKdpSelectWidget;
use App\Filament\Admin\Widgets\MyTasksWidget;
use App\Filament\Admin\Widgets\RevenueChartWidget;
use App\Filament\Admin\Widgets\SummaryCardsWidget;
use App\Filament\Admin\Widgets\TopWorksByRevenueWidget;
use App\Filament\Admin\Widgets\UpcomingEventsWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        Livewire::test(ExpiringKdpSelectWidget::class)
            ->assertSee('Obra de demostración 01')
            ->assertSee('20 días');
        Livewire::test(RevenueChartWidget::class)
            ->assertSee('Regalías acumuladas durante los últimos seis meses')
            ->assertSee('118.60 €');
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
}
