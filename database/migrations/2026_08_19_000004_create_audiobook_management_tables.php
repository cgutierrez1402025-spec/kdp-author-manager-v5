<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('narrators', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('stage_name')->nullable();
            $table->string('narrator_type', 30)->default('human');
            $table->json('languages')->nullable();
            $table->json('voice_attributes')->nullable();
            $table->string('provider')->nullable();
            $table->string('external_profile_url', 512)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('sample_file_path', 512)->nullable();
            $table->boolean('voice_consent')->default(false);
            $table->date('consent_expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'name']);
        });

        Schema::create('audiobook_editions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_language_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('manuscript_version_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('source_publication_id')->nullable()->constrained('publications')->nullOnDelete();
            $table->string('title');
            $table->string('language_code', 5)->nullable();
            $table->string('production_method', 40);
            $table->string('status', 40)->default('idea');
            $table->string('rights_status', 30)->default('pending');
            $table->boolean('exclusive')->default(false);
            $table->boolean('kdp_select_inherited')->default(false);
            $table->json('territories')->nullable();
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->unsignedInteger('final_duration_minutes')->nullable();
            $table->string('external_identifier')->nullable();
            $table->decimal('list_price', 10, 2)->nullable();
            $table->char('currency', 3)->nullable();
            $table->decimal('royalty_rate', 5, 2)->nullable();
            $table->date('royalty_rate_effective_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['work_id', 'language_code']);
        });

        Schema::create('audiobook_narrators', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audiobook_edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('narrator_id')->constrained()->cascadeOnDelete();
            $table->string('role', 50)->default('narrator');
            $table->string('external_voice_id')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['audiobook_edition_id', 'narrator_id', 'role']);
        });

        Schema::create('audiobook_productions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audiobook_edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('narrator_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->nullable();
            $table->string('contract_type', 30);
            $table->string('status', 40)->default('draft');
            $table->decimal('per_finished_hour_rate', 10, 2)->nullable();
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->decimal('actual_cost', 12, 2)->nullable();
            $table->char('currency', 3)->nullable();
            $table->decimal('rights_holder_share', 5, 2)->nullable();
            $table->decimal('producer_share', 5, 2)->nullable();
            $table->date('offer_date')->nullable();
            $table->date('accepted_at')->nullable();
            $table->date('due_date')->nullable();
            $table->date('approved_at')->nullable();
            $table->string('contract_file_path', 512)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('audiobook_chapters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audiobook_edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('narrator_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('chapter_order');
            $table->string('title');
            $table->unsignedInteger('word_count')->nullable();
            $table->unsignedInteger('estimated_duration_seconds')->nullable();
            $table->unsignedInteger('final_duration_seconds')->nullable();
            $table->string('status', 30)->default('pending');
            $table->unsignedTinyInteger('revisions_used')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['audiobook_edition_id', 'chapter_order']);
        });

        Schema::create('audio_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audiobook_edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audiobook_chapter_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('asset_type', 30);
            $table->unsignedInteger('version')->default(1);
            $table->string('file_path', 512);
            $table->string('file_hash', 64);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('codec')->nullable();
            $table->unsignedInteger('bitrate_kbps')->nullable();
            $table->unsignedInteger('sample_rate_hz')->nullable();
            $table->string('channels', 10)->nullable();
            $table->decimal('rms_db', 6, 2)->nullable();
            $table->decimal('peak_db', 6, 2)->nullable();
            $table->decimal('noise_floor_db', 6, 2)->nullable();
            $table->string('qa_status', 30)->default('pending');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['audiobook_edition_id', 'asset_type', 'audiobook_chapter_id', 'version'], 'audio_asset_version_unique');
        });

        Schema::create('audiobook_pronunciations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audiobook_edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audiobook_chapter_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('term');
            $table->string('pronunciation');
            $table->string('language_code', 5)->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamps();
        });

        Schema::create('audiobook_distributions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audiobook_edition_id')->constrained()->cascadeOnDelete();
            $table->string('distributor');
            $table->string('channel');
            $table->string('marketplace')->nullable();
            $table->string('external_identifier')->nullable();
            $table->string('public_url', 512)->nullable();
            $table->boolean('exclusive')->default(false);
            $table->json('territories')->nullable();
            $table->decimal('list_price', 10, 2)->nullable();
            $table->char('currency', 3)->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['audiobook_edition_id', 'distributor', 'channel', 'marketplace'], 'audio_distribution_unique');
        });

        Schema::create('audiobook_costs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audiobook_edition_id')->constrained()->cascadeOnDelete();
            $table->string('cost_type', 50);
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3);
            $table->date('incurred_at');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('audiobook_royalties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audiobook_edition_id')->constrained()->cascadeOnDelete();
            $table->string('distributor');
            $table->string('marketplace')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('units')->default(0);
            $table->decimal('net_revenue', 12, 2)->default(0);
            $table->decimal('royalty_amount', 12, 2);
            $table->char('currency', 3);
            $table->string('source_reference')->nullable();
            $table->timestamps();
            $table->unique(['audiobook_edition_id', 'distributor', 'marketplace', 'period_start', 'period_end'], 'audio_royalty_period_unique');
        });

        Schema::create('audiobook_quality_checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audiobook_edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('audio_asset_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('check_type', 50);
            $table->string('rule_version', 30);
            $table->string('status', 20);
            $table->json('evidence')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['audiobook_quality_checks', 'audiobook_royalties', 'audiobook_costs', 'audiobook_distributions', 'audiobook_pronunciations', 'audio_assets', 'audiobook_chapters', 'audiobook_productions', 'audiobook_narrators', 'audiobook_editions', 'narrators'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
