<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('illustration_anchors', function (Blueprint $table) {
            $table->boolean('applied')->default(false)->after('status');
            $table->longText('applied_html_content')->nullable()->after('applied');
            $table->foreignId('applied_version_id')->nullable()->after('applied_html_content')->constrained('manuscript_versions')->nullOnDelete();
            $table->timestamp('applied_at')->nullable()->after('applied_version_id');
        });
    }

    public function down(): void
    {
        Schema::table('illustration_anchors', function (Blueprint $table) {
            $table->dropForeign(['applied_version_id']);
            $table->dropColumn(['applied', 'applied_html_content', 'applied_version_id', 'applied_at']);
        });
    }
};
