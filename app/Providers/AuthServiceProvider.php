<?php

namespace App\Providers;

use App\Models\BookPromotion;
use App\Models\Chapter;
use App\Models\KdpSelectPeriod;
use App\Models\ManuscriptVersion;
use App\Models\Marketplace;
use App\Models\Platform;
use App\Models\Publication;
use App\Models\Source;
use App\Models\SourceUsage;
use App\Models\Work;
use App\Policies\BookPromotionPolicy;
use App\Policies\ChapterPolicy;
use App\Policies\ManuscriptVersionPolicy;
use App\Policies\MarketplacePolicy;
use App\Policies\PlatformPolicy;
use App\Policies\PublicationPolicy;
use App\Policies\SourcePolicy;
use App\Policies\SourceUsagePolicy;
use App\Policies\WorkPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Work::class => WorkPolicy::class,
        Publication::class => PublicationPolicy::class,
        Platform::class => PlatformPolicy::class,
        Marketplace::class => MarketplacePolicy::class,
        BookPromotion::class => BookPromotionPolicy::class,
        KdpSelectPeriod::class => BookPromotionPolicy::class,
        ManuscriptVersion::class => ManuscriptVersionPolicy::class,
        Chapter::class => ChapterPolicy::class,
        Source::class => SourcePolicy::class,
        SourceUsage::class => SourceUsagePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
