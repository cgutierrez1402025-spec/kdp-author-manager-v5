<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('analytics_opt_in')->default(false)->after('remember_token');
            $table->timestamp('analytics_consented_at')->nullable()->after('analytics_opt_in');
        });

        Schema::create('publication_price_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('publication_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->char('currency', 3);
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->string('change_reason', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['publication_id', 'marketplace_id', 'starts_at'], 'publication_market_price_start_unique');
            $table->index(['publication_id', 'marketplace_id', 'ends_at'], 'publication_market_price_period_index');
        });

        Schema::create('publication_market_observations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('publication_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketplace_id')->constrained()->cascadeOnDelete();
            $table->date('observed_at');
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->unsignedInteger('rating_count')->nullable();
            $table->unsignedInteger('review_count')->nullable();
            $table->unsignedInteger('overall_rank')->nullable();
            $table->string('category_name')->nullable();
            $table->unsignedInteger('category_rank')->nullable();
            $table->json('extra_metrics')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
            $table->unique(['publication_id', 'marketplace_id', 'observed_at'], 'publication_market_observation_unique');
            $table->index(['marketplace_id', 'observed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_market_observations');
        Schema::dropIfExists('publication_price_histories');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['analytics_opt_in', 'analytics_consented_at']));
    }
};
