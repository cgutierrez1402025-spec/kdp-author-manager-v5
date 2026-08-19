<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table): void {
            $table->foreignId('manuscript_version_id')->nullable()->change();
        });

        Schema::create('kdp_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('latest_import_batch_id')->nullable()->constrained('import_batches')->nullOnDelete();
            $table->string('payment_number');
            $table->string('marketplace')->nullable();
            $table->string('status', 50)->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('accrued_royalty', 18, 4)->nullable();
            $table->decimal('tax_withholding', 18, 4)->nullable();
            $table->decimal('fx_rate', 18, 8)->nullable();
            $table->decimal('payment_amount', 18, 4)->nullable();
            $table->char('currency', 3)->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'payment_number', 'currency'], 'kdp_payment_identity_unique');
            $table->index(['user_id', 'payment_date']);
        });

        Schema::create('kdp_payment_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kdp_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kdp_report_row_id')->constrained()->cascadeOnDelete();
            $table->foreignId('publication_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('allocated_amount', 18, 4)->nullable();
            $table->char('currency', 3)->nullable();
            $table->string('allocation_method', 30)->default('source_row');
            $table->string('status', 30)->default('unallocated');
            $table->decimal('confidence', 5, 2)->nullable();
            $table->timestamps();
            $table->unique('kdp_report_row_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kdp_payment_allocations');
        Schema::dropIfExists('kdp_payments');
        Schema::table('publications', function (Blueprint $table): void {
            $table->foreignId('manuscript_version_id')->nullable(false)->change();
        });
    }
};
