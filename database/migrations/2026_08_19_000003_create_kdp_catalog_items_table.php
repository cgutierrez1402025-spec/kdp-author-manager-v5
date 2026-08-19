<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kdp_catalog_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('publication_id')->nullable()->constrained()->nullOnDelete();
            $table->string('identity_key', 64);
            $table->string('asin', 20)->nullable();
            $table->string('isbn', 20)->nullable();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('format', 50)->nullable();
            $table->json('marketplaces')->nullable();
            $table->string('review_status', 30)->default('pending');
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->timestamps();

            $table->unique(['user_id', 'identity_key']);
            $table->index(['user_id', 'review_status']);
            $table->index(['asin', 'format']);
        });

        Schema::table('kdp_report_rows', function (Blueprint $table): void {
            $table->foreignId('kdp_catalog_item_id')->nullable()->after('publication_id')
                ->constrained('kdp_catalog_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kdp_report_rows', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('kdp_catalog_item_id');
        });
        Schema::dropIfExists('kdp_catalog_items');
    }
};
