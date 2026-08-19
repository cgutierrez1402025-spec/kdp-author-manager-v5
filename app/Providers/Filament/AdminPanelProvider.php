<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Resources\AiTasks\AiTaskResource;
use App\Filament\Admin\Resources\BookEvents\BookEventResource;
use App\Filament\Admin\Resources\BookPromotions\BookPromotionResource;
use App\Filament\Admin\Resources\Checklists\ChecklistResource;
use App\Filament\Admin\Resources\EventBooks\EventBookResource;
use App\Filament\Admin\Resources\IllustrationAnchors\IllustrationAnchorResource;
use App\Filament\Admin\Resources\ImportBatches\ImportBatchResource;
use App\Filament\Admin\Resources\KdpMetadatas\KdpMetadataResource;
use App\Filament\Admin\Resources\KdpSelectPeriods\KdpSelectPeriodResource;
use App\Filament\Admin\Resources\ManuscriptVersions\ManuscriptVersionResource;
use App\Filament\Admin\Resources\Marketplaces\MarketplaceResource;
use App\Filament\Admin\Resources\Platforms\PlatformResource;
use App\Filament\Admin\Resources\PromotionCosts\PromotionCostResource;
use App\Filament\Admin\Resources\PromotionDailyResults\PromotionDailyResultResource;
use App\Filament\Admin\Resources\Prompts\PromptResource;
use App\Filament\Admin\Resources\Publications\PublicationResource;
use App\Filament\Admin\Resources\Sources\SourceResource;
use App\Filament\Admin\Resources\SourceUsages\SourceUsageResource;
use App\Filament\Admin\Resources\Tasks\TaskResource;
use App\Filament\Admin\Resources\Works\WorkResource;
use App\Filament\Admin\Widgets\ActivePromotionsWidget;
use App\Filament\Admin\Widgets\ExpiringKdpSelectWidget;
use App\Filament\Admin\Widgets\MyTasksWidget;
use App\Filament\Admin\Widgets\RecentActivityWidget;
use App\Filament\Admin\Widgets\RevenueChartWidget;
use App\Filament\Admin\Widgets\SummaryCardsWidget;
use App\Filament\Admin\Widgets\TopWorksByRevenueWidget;
use App\Filament\Admin\Widgets\UpcomingEventsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('KDP Author Manager')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18rem')
            ->maxContentWidth(MaxWidth::ScreenTwoExtraLarge)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationGroups([
                NavigationGroup::make()->label('Catálogo editorial'),
                NavigationGroup::make()->label('Publicaciones'),
                NavigationGroup::make()->label('Marketing'),
                NavigationGroup::make()->label('Eventos')->collapsed(),
                NavigationGroup::make()->label('Documentación')->collapsed(),
                NavigationGroup::make()->label('Ilustraciones')->collapsed(),
                NavigationGroup::make()->label('Inteligencia artificial')->collapsed(),
                NavigationGroup::make()->label('Logs')->collapsed(),
            ])
            ->resources([
                AiTaskResource::class,
                BookEventResource::class,
                BookPromotionResource::class,
                ChecklistResource::class,
                EventBookResource::class,
                IllustrationAnchorResource::class,
                ImportBatchResource::class,
                KdpMetadataResource::class,
                KdpSelectPeriodResource::class,
                ManuscriptVersionResource::class,
                MarketplaceResource::class,
                PlatformResource::class,
                PromotionCostResource::class,
                PromotionDailyResultResource::class,
                PromptResource::class,
                PublicationResource::class,
                SourceUsageResource::class,
                SourceResource::class,
                TaskResource::class,
                WorkResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                SummaryCardsWidget::class,
                MyTasksWidget::class,
                UpcomingEventsWidget::class,
                RevenueChartWidget::class,
                TopWorksByRevenueWidget::class,
                ActivePromotionsWidget::class,
                ExpiringKdpSelectWidget::class,
                RecentActivityWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
