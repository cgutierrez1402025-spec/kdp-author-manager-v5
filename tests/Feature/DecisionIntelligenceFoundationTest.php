<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\Publications\Pages\EditPublication;
use App\Filament\Admin\Resources\Publications\RelationManagers\MarketObservationsRelationManager;
use App\Filament\Admin\Resources\Publications\RelationManagers\PriceHistoriesRelationManager;
use App\Models\Publication;
use App\Models\PublicationPriceHistory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class DecisionIntelligenceFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_price_history_and_market_observations_are_traceable(): void
    {
        $publication = Publication::with(['priceHistories.marketplace', 'marketObservations.marketplace'])->firstOrFail();

        $this->assertCount(2, $publication->priceHistories);
        $this->assertCount(1, $publication->marketObservations);
        $this->assertSame('4.35', $publication->marketObservations->first()->average_rating);
    }

    public function test_overlapping_price_period_is_rejected(): void
    {
        $current = PublicationPriceHistory::whereNull('ends_at')->firstOrFail();

        $this->expectException(ValidationException::class);
        PublicationPriceHistory::create([
            'publication_id' => $current->publication_id,
            'marketplace_id' => $current->marketplace_id,
            'price' => 3.99,
            'currency' => 'EUR',
            'starts_at' => '2026-07-01',
        ]);
    }

    public function test_author_can_grant_and_revoke_analytics_consent(): void
    {
        $author = User::where('email', 'author@example.com')->firstOrFail();
        $author->update(['analytics_opt_in' => false, 'analytics_consented_at' => null]);

        $this->actingAs($author)->patch('/profile', ['name' => $author->name, 'email' => $author->email, 'analytics_opt_in' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($author->fresh()->analytics_opt_in);
        $this->assertNotNull($author->fresh()->analytics_consented_at);

        $this->actingAs($author)->patch('/profile', ['name' => $author->name, 'email' => $author->email, 'analytics_opt_in' => '0'])->assertSessionHasNoErrors();
        $this->assertFalse($author->fresh()->analytics_opt_in);
        $this->assertNull($author->fresh()->analytics_consented_at);
    }

    public function test_publication_history_relation_managers_render_for_owner(): void
    {
        $author = User::where('email', 'author@example.com')->firstOrFail();
        $publication = Publication::whereHas('work', fn ($query) => $query->where('user_id', $author->id))->firstOrFail();
        $this->actingAs($author);

        Livewire::test(PriceHistoriesRelationManager::class, ['ownerRecord' => $publication, 'pageClass' => EditPublication::class])->assertSuccessful();
        Livewire::test(MarketObservationsRelationManager::class, ['ownerRecord' => $publication, 'pageClass' => EditPublication::class])->assertSuccessful();
    }

    public function test_health_command_reports_a_consistent_database(): void
    {
        $this->artisan('app:health')->assertSuccessful()->expectsOutputToContain('Claves foráneas');
    }
}
