<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kdp_report_rows', function (Blueprint $table): void {
            $table->string('observation_status', 20)->default('final')->after('row_kind');
            $table->timestamp('snapshot_at')->nullable()->after('report_period');
            $table->integer('preorder_units')->nullable()->after('free_units');
            $table->integer('preorder_cancellations')->nullable()->after('preorder_units');
            $table->integer('net_preorder_units')->nullable()->after('preorder_cancellations');
            $table->unsignedBigInteger('combined_units_or_kenp')->nullable()->after('kenp_read');
            $table->decimal('average_file_size_mb', 12, 4)->nullable()->after('average_delivery_cost');
            $table->decimal('income_amount', 18, 4)->nullable()->after('total_earnings');
            $table->string('payment_plan')->nullable()->after('income_amount');
            $table->decimal('kenp_rate', 18, 8)->nullable()->after('payment_plan');
            $table->string('payment_method', 50)->nullable()->after('payment_status');
            $table->decimal('net_earnings', 18, 4)->nullable()->after('payment_date');
            $table->string('sales_period')->nullable()->after('net_earnings');
            $table->string('payment_source')->nullable()->after('sales_period');
            $table->string('source_generation', 20)->default('current')->after('report_type');
        });

        Schema::table('kdp_payments', function (Blueprint $table): void {
            $table->string('payment_method', 50)->nullable()->after('status');
            $table->decimal('net_earnings', 18, 4)->nullable()->after('payment_date');
            $table->date('sales_period_start')->nullable()->after('net_earnings');
            $table->date('sales_period_end')->nullable()->after('sales_period_start');
            $table->string('source')->nullable()->after('sales_period_end');
        });

        Schema::create('kdp_royalty_estimates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('publication_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marketplace_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('import_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kdp_report_row_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('period')->nullable();
            $table->timestamp('snapshot_at')->nullable();
            $table->decimal('estimated_amount', 18, 4)->nullable();
            $table->char('currency', 3)->nullable();
            $table->decimal('kenp_rate', 18, 8)->nullable();
            $table->json('filters')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kdp_royalty_estimates');
        Schema::table('kdp_payments', fn (Blueprint $table) => $table->dropColumn([
            'payment_method', 'net_earnings', 'sales_period_start', 'sales_period_end', 'source',
        ]));
        Schema::table('kdp_report_rows', fn (Blueprint $table) => $table->dropColumn([
            'observation_status', 'snapshot_at', 'preorder_units', 'preorder_cancellations',
            'net_preorder_units', 'combined_units_or_kenp', 'average_file_size_mb',
            'income_amount', 'payment_plan', 'source_generation',
            'kenp_rate', 'payment_method', 'net_earnings', 'sales_period', 'payment_source',
        ]));
    }
};
