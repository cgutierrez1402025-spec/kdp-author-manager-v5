<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kdp_report_rows', function (Blueprint $table): void {
            $table->date('transaction_date')->nullable()->after('report_period');
            $table->string('source_sheet')->nullable()->after('report_type');
            $table->string('row_kind', 30)->default('royalty')->after('source_sheet');
            $table->integer('paid_units')->nullable()->after('net_units_sold');
            $table->integer('free_units')->nullable()->after('paid_units');
            $table->index(['user_id', 'row_kind', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::table('kdp_report_rows', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'row_kind', 'transaction_date']);
            $table->dropColumn(['transaction_date', 'source_sheet', 'row_kind', 'paid_units', 'free_units']);
        });
    }
};
