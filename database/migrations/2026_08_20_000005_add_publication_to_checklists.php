<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklists', function (Blueprint $table): void {
            $table->foreignId('publication_id')->nullable()->after('work_id')->constrained()->nullOnDelete();
            $table->index(['work_id', 'publication_id']);
        });
    }

    public function down(): void
    {
        Schema::table('checklists', function (Blueprint $table): void {
            $table->dropForeign(['publication_id']);
            $table->dropIndex(['work_id', 'publication_id']);
            $table->dropColumn('publication_id');
        });
    }
};
