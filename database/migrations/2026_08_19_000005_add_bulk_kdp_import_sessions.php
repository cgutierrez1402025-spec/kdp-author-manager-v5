<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('total_files')->default(0);
            $table->unsignedInteger('completed_files')->default(0);
            $table->unsignedInteger('failed_files')->default(0);
            $table->unsignedInteger('duplicate_files')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('import_batches', function (Blueprint $table): void {
            $table->foreignId('import_session_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('detected_import_type', 40)->nullable()->after('import_type');
            $table->decimal('detection_confidence', 5, 2)->nullable()->after('detected_import_type');
            $table->date('detected_report_period')->nullable()->after('report_period');
            $table->unsignedInteger('processing_order')->nullable()->after('detected_format');
            $table->index(['import_session_id', 'processing_order']);
        });
    }

    public function down(): void
    {
        Schema::table('import_batches', function (Blueprint $table): void {
            $table->dropForeign(['import_session_id']);
            $table->dropIndex(['import_session_id', 'processing_order']);
            $table->dropColumn(['import_session_id', 'detected_import_type', 'detection_confidence', 'detected_report_period', 'processing_order']);
        });
        Schema::dropIfExists('import_sessions');
    }
};
