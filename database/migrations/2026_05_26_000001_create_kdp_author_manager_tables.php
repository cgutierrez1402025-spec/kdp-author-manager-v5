<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $createIfMissing = function (string $table, Closure $callback): void {
            if (! Schema::hasTable($table)) {
                Schema::create($table, $callback);
            }
        };

        $createIfMissing('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('guard_name', 255);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        $createIfMissing('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('guard_name', 255);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        $createIfMissing('model_has_roles', function (Blueprint $table) {
            $table->foreignId('role_id');
            $table->string('model_type', 255);
            $table->foreignId('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        $createIfMissing('model_has_permissions', function (Blueprint $table) {
            $table->foreignId('permission_id');
            $table->string('model_type', 255);
            $table->foreignId('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
        });

        $createIfMissing('series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        if (! Schema::hasTable('works')) {
            $createIfMissing('works', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('series_id')->nullable();
                $table->integer('series_number')->nullable();
                $table->string('title_internal', 255);
                $table->string('title_public', 255);
                $table->string('subtitle', 255)->nullable();
                $table->string('author_name', 255);
                $table->string('pen_name', 255)->nullable();
                $table->string('genre', 100)->nullable();
                $table->string('subgenre', 100)->nullable();
                $table->string('work_type', 100)->nullable();
                $table->char('original_language', 2);
                $table->string('status', 50);
                $table->string('target_audience', 255)->nullable();
                $table->string('age_recommendation', 50)->nullable();
                $table->text('description_internal')->nullable();
                $table->text('description_marketing')->nullable();
                $table->date('start_date')->nullable();
                $table->date('planned_publish_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->index('user_id', 'works_user_id_index');
                $table->index('series_id', 'works_series_id_index');
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('series_id')->references('id')->on('series')->nullOnDelete();
            });
        }

        $createIfMissing('work_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->char('language_code', 2);
            $table->string('regional_variant', 10)->nullable();
            $table->string('translated_title', 255)->nullable();
            $table->string('translated_subtitle', 255)->nullable();
            $table->string('translator_name', 255)->nullable();
            $table->string('translation_status', 50);
            $table->boolean('ai_translation_used');
            $table->string('human_review_level', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['work_id', 'language_code'], 'work_language_unique');
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
        });

        $createIfMissing('editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->foreignId('work_language_id');
            $table->integer('edition_number')->default(1);
            $table->string('edition_name', 255)->nullable();
            $table->string('edition_type', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('work_language_id')->references('id')->on('work_languages')->cascadeOnDelete();
        });

        $createIfMissing('manuscript_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->foreignId('work_language_id');
            $table->foreignId('parent_version_id')->nullable();
            $table->foreignId('edition_id')->nullable();
            $table->string('version_number', 50);
            $table->string('name', 255)->nullable();
            $table->string('status', 50);
            $table->longText('html_content')->nullable();
            $table->string('file_path', 512)->nullable();
            $table->string('file_hash', 64)->nullable();
            $table->integer('word_count')->nullable();
            $table->integer('chapter_count')->nullable();
            $table->integer('image_count')->nullable();
            $table->text('change_summary')->nullable();
            $table->boolean('is_candidate');
            $table->boolean('is_final');
            $table->boolean('is_published');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('work_id', 'manuscript_versions_work_id_index');
            $table->index('parent_version_id', 'manuscript_versions_parent_version_id_index');
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('work_language_id')->references('id')->on('work_languages')->cascadeOnDelete();
            $table->foreign('parent_version_id')->references('id')->on('manuscript_versions')->nullOnDelete();
            $table->foreign('edition_id')->references('id')->on('editions')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });

        $createIfMissing('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manuscript_version_id');
            $table->foreignId('work_id');
            $table->integer('chapter_order');
            $table->integer('level')->default(1);
            $table->string('title', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->string('html_id', 255)->nullable();
            $table->integer('start_position')->nullable();
            $table->integer('end_position')->nullable();
            $table->integer('word_count')->nullable();
            $table->string('status', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('manuscript_version_id', 'chapters_manuscript_version_id_index');
            $table->foreign('manuscript_version_id')->references('id')->on('manuscript_versions')->cascadeOnDelete();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
        });

        $createIfMissing('sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->string('title', 512);
            $table->string('author', 255)->nullable();
            $table->string('year', 20)->nullable();
            $table->string('source_type', 100);
            $table->char('language_code', 2)->nullable();
            $table->string('url', 512)->nullable();
            $table->date('consulted_at')->nullable();
            $table->text('citation')->nullable();
            $table->text('summary')->nullable();
            $table->string('rights_status', 100)->nullable();
            $table->string('license', 255)->nullable();
            $table->string('reliability', 50)->nullable();
            $table->string('file_path', 512)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('work_id', 'sources_work_id_index');
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
        });

        $createIfMissing('source_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id');
            $table->foreignId('work_id');
            $table->foreignId('manuscript_version_id')->nullable();
            $table->foreignId('chapter_id')->nullable();
            $table->text('fragment')->nullable();
            $table->string('usage_type', 100)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('verified');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('source_id')->references('id')->on('sources')->cascadeOnDelete();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('manuscript_version_id')->references('id')->on('manuscript_versions')->nullOnDelete();
            $table->foreign('chapter_id')->references('id')->on('chapters')->nullOnDelete();
        });

        $createIfMissing('ai_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('name', 255);
            $table->string('provider', 255)->nullable();
            $table->string('tool_type', 100);
            $table->string('model', 255)->nullable();
            $table->string('url', 512)->nullable();
            $table->text('strengths')->nullable();
            $table->text('weaknesses')->nullable();
            $table->string('cost_notes', 255)->nullable();
            $table->integer('quality_score')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        $createIfMissing('ai_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->string('task_type', 100);
            $table->foreignId('preferred_ai_tool_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('preferred_ai_tool_id')->references('id')->on('ai_tools')->nullOnDelete();
        });

        $createIfMissing('prompts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->foreignId('ai_tool_id')->nullable();
            $table->foreignId('task_id')->nullable();
            $table->string('title', 255);
            $table->text('prompt_text');
            $table->char('language_code', 2)->nullable();
            $table->string('purpose', 255)->nullable();
            $table->text('result_summary')->nullable();
            $table->integer('rating')->nullable();
            $table->boolean('reused');
            $table->boolean('generated_final_content');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('work_id', 'prompts_work_id_index');
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('ai_tool_id')->references('id')->on('ai_tools')->nullOnDelete();
            $table->foreign('task_id')->references('id')->on('ai_tasks')->nullOnDelete();
        });

        $createIfMissing('illustrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->foreignId('work_language_id')->nullable();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('image_type', 100);
            $table->string('file_original', 512);
            $table->string('file_optimized', 512)->nullable();
            $table->string('thumbnail', 512)->nullable();
            $table->string('format', 20)->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('resolution')->nullable();
            $table->foreignId('ai_tool_id')->nullable();
            $table->foreignId('prompt_id')->nullable();
            $table->string('rights_status', 100)->nullable();
            $table->string('license', 255)->nullable();
            $table->string('status', 50);
            $table->boolean('approved');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('work_language_id')->references('id')->on('work_languages')->nullOnDelete();
            $table->foreign('ai_tool_id')->references('id')->on('ai_tools')->nullOnDelete();
            $table->foreign('prompt_id')->references('id')->on('prompts')->nullOnDelete();
        });

        $createIfMissing('illustration_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('illustration_id');
            $table->integer('version_number');
            $table->string('file_path', 512);
            $table->text('change_summary')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('illustration_id')->references('id')->on('illustrations')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });

        $createIfMissing('illustration_anchors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('illustration_id');
            $table->foreignId('manuscript_version_id');
            $table->foreignId('chapter_id')->nullable();
            $table->string('anchor_type', 50);
            $table->string('position_type', 50)->nullable();
            $table->text('search_text')->nullable();
            $table->text('search_text_before')->nullable();
            $table->text('search_text_after')->nullable();
            $table->string('css_selector', 512)->nullable();
            $table->string('html_marker', 255)->nullable();
            $table->string('insertion_mode', 50)->nullable();
            $table->string('confidence', 50)->nullable();
            $table->string('status', 50);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('illustration_id')->references('id')->on('illustrations')->cascadeOnDelete();
            $table->foreign('manuscript_version_id')->references('id')->on('manuscript_versions')->cascadeOnDelete();
            $table->foreign('chapter_id')->references('id')->on('chapters')->nullOnDelete();
        });

        $createIfMissing('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        $createIfMissing('marketplaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id');
            $table->string('code', 50);
            $table->string('name', 255);
            $table->char('currency', 3)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('platform_id')->references('id')->on('platforms')->cascadeOnDelete();
        });

        $createIfMissing('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->foreignId('work_language_id');
            $table->foreignId('manuscript_version_id');
            $table->foreignId('platform_id');
            $table->foreignId('marketplace_id')->nullable();
            $table->string('format', 50);
            $table->string('external_identifier', 255)->nullable();
            $table->string('public_url', 512)->nullable();
            $table->string('status', 50);
            $table->decimal('price', 10, 2)->nullable();
            $table->char('currency', 3)->nullable();
            $table->text('territories')->nullable();
            $table->string('isbn', 20)->nullable();
            $table->string('asin', 20)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unique(['asin', 'marketplace_id'], 'publications_asin_marketplace_unique');
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('work_language_id')->references('id')->on('work_languages')->cascadeOnDelete();
            $table->foreign('manuscript_version_id')->references('id')->on('manuscript_versions')->cascadeOnDelete();
            $table->foreign('platform_id')->references('id')->on('platforms')->cascadeOnDelete();
            $table->foreign('marketplace_id')->references('id')->on('marketplaces')->nullOnDelete();
        });

        $createIfMissing('kdp_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id');
            $table->string('title', 255);
            $table->string('subtitle', 255)->nullable();
            $table->string('author', 255);
            $table->text('contributors')->nullable();
            $table->string('series_name', 255)->nullable();
            $table->integer('series_number')->nullable();
            $table->text('description')->nullable();
            $table->string('keywords', 255)->nullable();
            $table->text('categories')->nullable();
            $table->string('age_range', 50)->nullable();
            $table->text('rights')->nullable();
            $table->text('ai_declaration')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('publication_id')->references('id')->on('publications')->cascadeOnDelete();
        });

        $createIfMissing('kdp_select_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('auto_renewal');
            $table->integer('free_promo_days_allowed')->default(5);
            $table->integer('free_promo_days_used')->default(0);
            $table->integer('free_promo_days_remaining')->default(5);
            $table->string('status', 50);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('publication_id')->references('id')->on('publications')->cascadeOnDelete();
        });

        $createIfMissing('book_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id');
            $table->foreignId('marketplace_id')->nullable();
            $table->string('promotion_type', 50);
            $table->string('promotion_name', 255)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('normal_price', 10, 2)->nullable();
            $table->decimal('promotional_price', 10, 2)->nullable();
            $table->foreignId('kdp_select_period_id')->nullable();
            $table->string('status', 50);
            $table->text('objective')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('publication_id')->references('id')->on('publications')->cascadeOnDelete();
            $table->foreign('marketplace_id')->references('id')->on('marketplaces')->nullOnDelete();
            $table->foreign('kdp_select_period_id')->references('id')->on('kdp_select_periods')->nullOnDelete();
        });

        $createIfMissing('promotion_daily_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_promotion_id');
            $table->date('date');
            $table->integer('paid_units')->default(0);
            $table->integer('free_units_promo')->default(0);
            $table->integer('free_units_price_match')->default(0);
            $table->integer('kenp_pages_read')->default(0);
            $table->decimal('gross_royalties', 10, 2);
            $table->decimal('net_royalties', 10, 2);
            $table->char('currency', 3)->nullable();
            $table->integer('ranking_position')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('book_promotion_id')->references('id')->on('book_promotions')->cascadeOnDelete();
        });

        $createIfMissing('promotion_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_promotion_id');
            $table->string('cost_type', 100);
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('book_promotion_id')->references('id')->on('book_promotions')->cascadeOnDelete();
        });

        $createIfMissing('royalty_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id');
            $table->integer('year');
            $table->integer('month');
            $table->integer('paid_units')->default(0);
            $table->integer('free_units')->default(0);
            $table->integer('kenp_pages')->default(0);
            $table->decimal('royalty_ebook', 10, 2);
            $table->decimal('royalty_paperback', 10, 2);
            $table->decimal('royalty_hardcover', 10, 2);
            $table->decimal('royalty_kenp', 10, 2);
            $table->decimal('total_royalty', 10, 2);
            $table->char('currency', 3)->nullable();
            $table->string('source_file', 512)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['publication_id', 'year', 'month'], 'royalty_unique');
            $table->foreign('publication_id')->references('id')->on('publications')->cascadeOnDelete();
        });

        $createIfMissing('royalty_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id');
            $table->foreignId('marketplace_id')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('expected_amount', 10, 2);
            $table->decimal('received_amount', 10, 2)->nullable();
            $table->decimal('withheld_tax', 10, 2)->nullable();
            $table->char('currency', 3);
            $table->date('expected_date')->nullable();
            $table->date('received_date')->nullable();
            $table->string('status', 50);
            $table->string('receipt_file', 512)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('platform_id')->references('id')->on('platforms')->cascadeOnDelete();
            $table->foreign('marketplace_id')->references('id')->on('marketplaces')->nullOnDelete();
        });

        $createIfMissing('payment_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id');
            $table->foreignId('marketplace_id')->nullable();
            $table->char('currency', 3);
            $table->decimal('threshold_amount', 10, 2);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('platform_id')->references('id')->on('platforms')->cascadeOnDelete();
            $table->foreign('marketplace_id')->references('id')->on('marketplaces')->nullOnDelete();
        });

        $createIfMissing('awards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('organizer', 255)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('genre', 255)->nullable();
            $table->char('language_code', 2)->nullable();
            $table->string('prize_amount', 255)->nullable();
            $table->string('url', 512)->nullable();
            $table->date('opening_date')->nullable();
            $table->date('deadline')->nullable();
            $table->date('expected_resolution_date')->nullable();
            $table->date('actual_resolution_date')->nullable();
            $table->boolean('requires_unpublished');
            $table->boolean('forbids_self_publishing');
            $table->boolean('forbids_simultaneous_submissions');
            $table->boolean('requires_anonymity');
            $table->boolean('allows_pseudonym');
            $table->integer('min_words')->nullable();
            $table->integer('max_words')->nullable();
            $table->text('terms')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        $createIfMissing('award_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->foreignId('work_language_id');
            $table->foreignId('manuscript_version_id');
            $table->foreignId('award_id');
            $table->date('submission_date');
            $table->string('submitted_title', 255)->nullable();
            $table->string('pseudonym_used', 255)->nullable();
            $table->string('status', 50);
            $table->string('result', 50)->nullable();
            $table->string('submitted_file', 512)->nullable();
            $table->string('proof_file', 512)->nullable();
            $table->boolean('block_publication');
            $table->date('block_until_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('work_language_id')->references('id')->on('work_languages')->cascadeOnDelete();
            $table->foreign('manuscript_version_id')->references('id')->on('manuscript_versions')->cascadeOnDelete();
            $table->foreign('award_id')->references('id')->on('awards')->cascadeOnDelete();
        });

        $createIfMissing('book_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title', 255);
            $table->string('event_type', 100);
            $table->date('event_date');
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('location_name', 255)->nullable();
            $table->string('address', 512)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('organizer', 255)->nullable();
            $table->string('contact_person', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->integer('expected_attendance')->nullable();
            $table->integer('actual_attendance')->nullable();
            $table->string('status', 50);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        $createIfMissing('event_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id');
            $table->foreignId('work_id');
            $table->foreignId('edition_id')->nullable();
            $table->foreignId('work_language_id')->nullable();
            $table->integer('copies_brought')->default(0);
            $table->integer('copies_sold')->default(0);
            $table->integer('copies_gifted')->default(0);
            $table->integer('copies_returned')->default(0);
            $table->decimal('unit_sale_price', 10, 2)->nullable();
            $table->decimal('income_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('event_id')->references('id')->on('book_events')->cascadeOnDelete();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('edition_id')->references('id')->on('editions')->nullOnDelete();
            $table->foreign('work_language_id')->references('id')->on('work_languages')->nullOnDelete();
        });

        $createIfMissing('physical_print_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->foreignId('edition_id')->nullable();
            $table->foreignId('work_language_id');
            $table->string('format', 50);
            $table->date('print_date');
            $table->string('printer_name', 255)->nullable();
            $table->integer('copies_printed');
            $table->decimal('unit_cost', 10, 4)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->decimal('recommended_retail_price', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('edition_id')->references('id')->on('editions')->nullOnDelete();
            $table->foreign('work_language_id')->references('id')->on('work_languages')->cascadeOnDelete();
        });

        $createIfMissing('distribution_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('name', 255);
            $table->string('type', 100);
            $table->string('address', 512)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website', 512)->nullable();
            $table->string('contact_person', 255)->nullable();
            $table->boolean('accepts_consignment');
            $table->boolean('accepts_direct_purchase');
            $table->boolean('accepts_events');
            $table->decimal('default_commission_percentage', 5, 2)->nullable();
            $table->string('usual_payment_terms', 255)->nullable();
            $table->string('relationship_status', 50)->nullable();
            $table->integer('rating')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        $createIfMissing('stock_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('name', 255);
            $table->string('type', 100);
            $table->foreignId('distribution_point_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('distribution_point_id')->references('id')->on('distribution_points')->nullOnDelete();
        });

        $createIfMissing('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->foreignId('edition_id')->nullable();
            $table->foreignId('work_language_id');
            $table->foreignId('print_run_id')->nullable();
            $table->foreignId('from_location_id')->nullable();
            $table->foreignId('to_location_id')->nullable();
            $table->string('movement_type', 100);
            $table->date('movement_date');
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 4)->nullable();
            $table->decimal('unit_sale_price', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('edition_id')->references('id')->on('editions')->nullOnDelete();
            $table->foreign('work_language_id')->references('id')->on('work_languages')->cascadeOnDelete();
            $table->foreign('print_run_id')->references('id')->on('physical_print_runs')->nullOnDelete();
            $table->foreign('from_location_id')->references('id')->on('stock_locations')->nullOnDelete();
            $table->foreign('to_location_id')->references('id')->on('stock_locations')->nullOnDelete();
        });

        $createIfMissing('book_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->foreignId('edition_id')->nullable();
            $table->foreignId('work_language_id');
            $table->foreignId('distribution_point_id');
            $table->date('delivery_date');
            $table->integer('quantity_delivered');
            $table->decimal('retail_price', 10, 2);
            $table->decimal('author_price', 10, 2);
            $table->decimal('commission_percentage', 5, 2)->nullable();
            $table->string('agreement_type', 50);
            $table->date('expected_review_date')->nullable();
            $table->string('receipt_file_path', 512)->nullable();
            $table->string('received_by', 255)->nullable();
            $table->string('status', 50);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('edition_id')->references('id')->on('editions')->nullOnDelete();
            $table->foreign('work_language_id')->references('id')->on('work_languages')->cascadeOnDelete();
            $table->foreign('distribution_point_id')->references('id')->on('distribution_points')->cascadeOnDelete();
        });

        $createIfMissing('distribution_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_point_id');
            $table->date('visit_date');
            $table->string('contact_person', 255)->nullable();
            $table->text('general_notes')->nullable();
            $table->date('next_visit_date')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('distribution_point_id')->references('id')->on('distribution_points')->cascadeOnDelete();
        });

        $createIfMissing('delivery_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_delivery_id');
            $table->foreignId('distribution_visit_id');
            $table->integer('copies_remaining_before');
            $table->integer('copies_sold');
            $table->integer('copies_returned');
            $table->integer('copies_restocked');
            $table->integer('copies_remaining_after');
            $table->decimal('amount_to_collect', 10, 2)->nullable();
            $table->decimal('amount_collected', 10, 2)->nullable();
            $table->decimal('amount_pending', 10, 2)->nullable();
            $table->string('payment_method', 100)->nullable();
            $table->string('review_status', 50);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('book_delivery_id')->references('id')->on('book_deliveries')->cascadeOnDelete();
            $table->foreign('distribution_visit_id')->references('id')->on('distribution_visits')->cascadeOnDelete();
        });

        $createIfMissing('promotional_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('work_id');
            $table->foreignId('work_language_id')->nullable();
            $table->foreignId('platform_id')->nullable();
            $table->foreignId('marketplace_id')->nullable();
            $table->string('asset_type', 100);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('file_path', 512);
            $table->string('thumbnail_path', 512)->nullable();
            $table->string('file_format', 20)->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('file_size')->nullable();
            $table->integer('resolution')->nullable();
            $table->string('color_space', 50)->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->foreignId('ai_tool_id')->nullable();
            $table->foreignId('prompt_id')->nullable();
            $table->string('rights_status', 100)->nullable();
            $table->string('license', 255)->nullable();
            $table->string('status', 50);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('work_language_id')->references('id')->on('work_languages')->nullOnDelete();
            $table->foreign('platform_id')->references('id')->on('platforms')->nullOnDelete();
            $table->foreign('marketplace_id')->references('id')->on('marketplaces')->nullOnDelete();
            $table->foreign('ai_tool_id')->references('id')->on('ai_tools')->nullOnDelete();
            $table->foreign('prompt_id')->references('id')->on('prompts')->nullOnDelete();
        });

        $createIfMissing('asset_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotional_asset_id');
            $table->integer('version_number');
            $table->string('file_path', 512);
            $table->text('change_summary')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('promotional_asset_id')->references('id')->on('promotional_assets')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });

        $createIfMissing('aplus_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('work_id');
            $table->foreignId('work_language_id');
            $table->foreignId('publication_id');
            $table->foreignId('platform_id');
            $table->foreignId('marketplace_id');
            $table->string('asin', 20);
            $table->char('language_code', 2);
            $table->string('title', 255)->nullable();
            $table->text('commercial_goal')->nullable();
            $table->string('status', 50);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('work_language_id')->references('id')->on('work_languages')->cascadeOnDelete();
            $table->foreign('publication_id')->references('id')->on('publications')->cascadeOnDelete();
            $table->foreign('platform_id')->references('id')->on('platforms')->cascadeOnDelete();
            $table->foreign('marketplace_id')->references('id')->on('marketplaces')->cascadeOnDelete();
        });

        $createIfMissing('aplus_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aplus_project_id');
            $table->string('module_type', 100);
            $table->integer('module_order');
            $table->string('headline', 255)->nullable();
            $table->text('body_text')->nullable();
            $table->foreignId('image_asset_id')->nullable();
            $table->foreignId('secondary_image_asset_id')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->text('comparison_asins')->nullable();
            $table->string('status', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('aplus_project_id')->references('id')->on('aplus_projects')->cascadeOnDelete();
            $table->foreign('image_asset_id')->references('id')->on('promotional_assets')->nullOnDelete();
            $table->foreign('secondary_image_asset_id')->references('id')->on('promotional_assets')->nullOnDelete();
        });

        $createIfMissing('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('import_type', 100);
            $table->string('source_system', 100)->nullable();
            $table->string('original_file_path', 512);
            $table->string('original_file_name', 255);
            $table->string('file_hash', 64);
            $table->string('detected_format', 50)->nullable();
            $table->string('status', 50);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->boolean('processed_by_ai');
            $table->foreignId('ai_tool_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['file_hash'], 'import_batches_file_hash_unique');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('ai_tool_id')->references('id')->on('ai_tools')->nullOnDelete();
        });

        $createIfMissing('import_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id');
            $table->string('external_column_name', 255);
            $table->string('internal_entity', 100)->nullable();
            $table->string('internal_field', 100)->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->boolean('mapped_by_ai');
            $table->boolean('confirmed_by_user');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('import_batch_id')->references('id')->on('import_batches')->cascadeOnDelete();
        });

        $createIfMissing('import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id');
            $table->integer('row_number');
            $table->json('raw_data_json');
            $table->json('normalized_data_json')->nullable();
            $table->string('validation_status', 50);
            $table->text('error_message')->nullable();
            $table->foreignId('linked_work_id')->nullable();
            $table->foreignId('linked_publication_id')->nullable();
            $table->foreignId('linked_royalty_entry_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('import_batch_id')->references('id')->on('import_batches')->cascadeOnDelete();
            $table->foreign('linked_work_id')->references('id')->on('works')->nullOnDelete();
            $table->foreign('linked_publication_id')->references('id')->on('publications')->nullOnDelete();
            $table->foreign('linked_royalty_entry_id')->references('id')->on('royalty_entries')->nullOnDelete();
        });

        $createIfMissing('import_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id');
            $table->string('severity', 50);
            $table->string('error_type', 100);
            $table->text('message');
            $table->integer('row_number')->nullable();
            $table->string('field_name', 255)->nullable();
            $table->text('suggested_solution')->nullable();
            $table->boolean('resolved');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('import_batch_id')->references('id')->on('import_batches')->cascadeOnDelete();
        });

        $createIfMissing('calibre_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id');
            $table->string('calibre_book_id', 255)->nullable();
            $table->string('title', 255);
            $table->string('author', 255)->nullable();
            $table->string('series', 255)->nullable();
            $table->integer('series_index')->nullable();
            $table->char('language_code', 2)->nullable();
            $table->text('tags')->nullable();
            $table->string('opf_path', 512)->nullable();
            $table->string('cover_path', 512)->nullable();
            $table->json('available_formats_json')->nullable();
            $table->foreignId('matched_work_id')->nullable();
            $table->string('status', 50);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('import_batch_id')->references('id')->on('import_batches')->cascadeOnDelete();
            $table->foreign('matched_work_id')->references('id')->on('works')->nullOnDelete();
        });

        $createIfMissing('ocr_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('source_id')->nullable();
            $table->foreignId('import_batch_id')->nullable();
            $table->string('input_file_path', 512);
            $table->string('ocr_engine', 100);
            $table->char('language_code', 2);
            $table->string('output_txt_path', 512)->nullable();
            $table->string('output_hocr_path', 512)->nullable();
            $table->string('output_tsv_path', 512)->nullable();
            $table->string('output_pdf_path', 512)->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->string('status', 50);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('source_id')->references('id')->on('sources')->nullOnDelete();
            $table->foreign('import_batch_id')->references('id')->on('import_batches')->nullOnDelete();
        });

        $createIfMissing('ocr_text_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ocr_job_id');
            $table->string('version_type', 50);
            $table->longText('text_content');
            $table->boolean('processed_by_ai');
            $table->foreignId('ai_tool_id')->nullable();
            $table->boolean('human_reviewed');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('ocr_job_id')->references('id')->on('ocr_jobs')->cascadeOnDelete();
            $table->foreign('ai_tool_id')->references('id')->on('ai_tools')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });

        $createIfMissing('translation_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->foreignId('source_work_language_id');
            $table->char('target_language_code', 2);
            $table->foreignId('source_manuscript_version_id');
            $table->string('tool_type', 100);
            $table->string('tool_name', 255)->nullable();
            $table->foreignId('ai_tool_id')->nullable();
            $table->boolean('calibre_used');
            $table->string('calibre_plugin_name', 255)->nullable();
            $table->string('input_file_path', 512)->nullable();
            $table->string('output_file_path', 512)->nullable();
            $table->string('status', 50);
            $table->string('human_review_status', 50);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('source_work_language_id')->references('id')->on('work_languages')->cascadeOnDelete();
            $table->foreign('source_manuscript_version_id')->references('id')->on('manuscript_versions')->cascadeOnDelete();
            $table->foreign('ai_tool_id')->references('id')->on('ai_tools')->nullOnDelete();
        });

        $createIfMissing('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('task_type', 100)->nullable();
            $table->string('priority', 50)->nullable();
            $table->string('status', 50);
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });

        $createIfMissing('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('work_id')->nullable();
            $table->foreignId('manuscript_version_id')->nullable();
            $table->foreignId('chapter_id')->nullable();
            $table->foreignId('task_id')->nullable();
            $table->text('comment');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
            $table->foreign('manuscript_version_id')->references('id')->on('manuscript_versions')->cascadeOnDelete();
            $table->foreign('chapter_id')->references('id')->on('chapters')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
        });

        $createIfMissing('checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('work_id')->references('id')->on('works')->cascadeOnDelete();
        });

        $createIfMissing('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id');
            $table->string('item', 255);
            $table->boolean('is_checked');
            $table->unsignedBigInteger('checked_by')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->integer('order')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('checklist_id')->references('id')->on('checklists')->cascadeOnDelete();
            $table->foreign('checked_by')->references('id')->on('users')->nullOnDelete();
        });

        $createIfMissing('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['name'], 'tags_name_unique');
        });

        $createIfMissing('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id');
            $table->string('taggable_type', 255);
            $table->foreignId('taggable_id');
            $table->primary(['tag_id', 'taggable_id', 'taggable_type']);
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();
        });

        $createIfMissing('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('action', 255);
            $table->text('description')->nullable();
            $table->string('loggable_type', 255)->nullable();
            $table->foreignId('loggable_id')->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index('user_id', 'activity_logs_user_id_index');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('checklists');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('translation_jobs');
        Schema::dropIfExists('ocr_text_versions');
        Schema::dropIfExists('ocr_jobs');
        Schema::dropIfExists('calibre_imports');
        Schema::dropIfExists('import_errors');
        Schema::dropIfExists('import_rows');
        Schema::dropIfExists('import_mappings');
        Schema::dropIfExists('import_batches');
        Schema::dropIfExists('aplus_modules');
        Schema::dropIfExists('aplus_projects');
        Schema::dropIfExists('asset_versions');
        Schema::dropIfExists('promotional_assets');
        Schema::dropIfExists('delivery_reviews');
        Schema::dropIfExists('distribution_visits');
        Schema::dropIfExists('book_deliveries');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_locations');
        Schema::dropIfExists('distribution_points');
        Schema::dropIfExists('physical_print_runs');
        Schema::dropIfExists('event_books');
        Schema::dropIfExists('book_events');
        Schema::dropIfExists('award_submissions');
        Schema::dropIfExists('awards');
        Schema::dropIfExists('payment_thresholds');
        Schema::dropIfExists('royalty_payments');
        Schema::dropIfExists('royalty_entries');
        Schema::dropIfExists('promotion_costs');
        Schema::dropIfExists('promotion_daily_results');
        Schema::dropIfExists('book_promotions');
        Schema::dropIfExists('kdp_select_periods');
        Schema::dropIfExists('kdp_metadata');
        Schema::dropIfExists('publications');
        Schema::dropIfExists('marketplaces');
        Schema::dropIfExists('platforms');
        Schema::dropIfExists('illustration_anchors');
        Schema::dropIfExists('illustration_versions');
        Schema::dropIfExists('illustrations');
        Schema::dropIfExists('prompts');
        Schema::dropIfExists('ai_tasks');
        Schema::dropIfExists('ai_tools');
        Schema::dropIfExists('source_usages');
        Schema::dropIfExists('sources');
        Schema::dropIfExists('chapters');
        Schema::dropIfExists('manuscript_versions');
        Schema::dropIfExists('editions');
        Schema::dropIfExists('work_languages');
        Schema::dropIfExists('works');
        Schema::dropIfExists('series');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
