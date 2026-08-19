<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'works_user_status_index');
        });

        Schema::table('manuscript_versions', function (Blueprint $table) {
            $table->index(['work_id', 'work_language_id', 'is_final'], 'manuscripts_work_language_final_index');
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->index(['work_id', 'status'], 'publications_work_status_index');
            $table->index(['platform_id', 'status'], 'publications_platform_status_index');
        });

        Schema::table('book_promotions', function (Blueprint $table) {
            $table->index(['status', 'start_date', 'end_date'], 'book_promotions_status_dates_index');
        });

        Schema::table('promotion_daily_results', function (Blueprint $table) {
            $table->index(['book_promotion_id', 'date'], 'promotion_results_promotion_date_index');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['assigned_to', 'status', 'due_date'], 'tasks_assignee_status_due_index');
        });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->index(['event', 'created_at'], 'activity_log_event_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('works', fn (Blueprint $table) => $table->dropIndex('works_user_status_index'));
        Schema::table('manuscript_versions', fn (Blueprint $table) => $table->dropIndex('manuscripts_work_language_final_index'));
        Schema::table('publications', function (Blueprint $table) {
            $table->dropIndex('publications_work_status_index');
            $table->dropIndex('publications_platform_status_index');
        });
        Schema::table('book_promotions', fn (Blueprint $table) => $table->dropIndex('book_promotions_status_dates_index'));
        Schema::table('promotion_daily_results', fn (Blueprint $table) => $table->dropIndex('promotion_results_promotion_date_index'));
        Schema::table('tasks', fn (Blueprint $table) => $table->dropIndex('tasks_assignee_status_due_index'));
        Schema::table('activity_log', fn (Blueprint $table) => $table->dropIndex('activity_log_event_created_index'));
    }
};
