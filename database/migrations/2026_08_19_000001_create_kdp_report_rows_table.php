<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_batches', function (Blueprint $table): void {
            $table->date('report_period')->nullable()->after('import_type');
            $table->unsignedInteger('total_rows')->default(0)->after('status');
            $table->unsignedInteger('imported_rows')->default(0)->after('total_rows');
            $table->unsignedInteger('skipped_rows')->default(0)->after('imported_rows');
            $table->unsignedInteger('error_rows')->default(0)->after('skipped_rows');
        });

        Schema::create('kdp_report_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('import_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('publication_id')->nullable()->constrained()->nullOnDelete();
            $table->string('row_fingerprint', 64);
            $table->string('report_type', 50);
            $table->date('report_period')->nullable();
            $table->string('title')->nullable();
            $table->string('author')->nullable();
            $table->string('asin', 20)->nullable();
            $table->string('format', 50)->nullable();
            $table->string('marketplace', 100)->nullable();
            $table->char('currency', 3)->nullable();
            $table->string('transaction_type', 100)->nullable();
            $table->string('royalty_type', 100)->nullable();
            $table->integer('units_sold')->nullable();
            $table->integer('units_refunded')->nullable();
            $table->integer('net_units_sold')->nullable();
            $table->unsignedBigInteger('kenp_read')->nullable();
            $table->decimal('average_list_price', 18, 4)->nullable();
            $table->decimal('average_offer_price', 18, 4)->nullable();
            $table->decimal('average_delivery_cost', 18, 4)->nullable();
            $table->decimal('total_earnings', 18, 4)->nullable();
            $table->string('payment_number')->nullable();
            $table->string('payment_status', 50)->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('accrued_royalty', 18, 4)->nullable();
            $table->decimal('tax_withholding', 18, 4)->nullable();
            $table->decimal('fx_rate', 18, 8)->nullable();
            $table->decimal('payment_amount', 18, 4)->nullable();
            $table->json('raw_data');
            $table->json('normalized_data');
            $table->timestamps();

            $table->unique(['user_id', 'row_fingerprint']);
            $table->index(['user_id', 'report_type', 'report_period']);
            $table->index(['asin', 'format']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kdp_report_rows');

        Schema::table('import_batches', function (Blueprint $table): void {
            $table->dropColumn([
                'report_period', 'total_rows', 'imported_rows', 'skipped_rows', 'error_rows',
            ]);
        });
    }
};
