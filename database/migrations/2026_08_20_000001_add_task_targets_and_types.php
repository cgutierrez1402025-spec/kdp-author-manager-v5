<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        foreach (['Escritura', 'Edición', 'Portada', 'Formato', 'Publicación', 'Marketing'] as $name) {
            DB::table('task_types')->insert([
                'name' => $name,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreignId('publication_id')->nullable()->after('work_id')->constrained()->nullOnDelete();
            $table->foreignId('task_type_id')->nullable()->after('task_type')->constrained()->nullOnDelete();
            $table->index(['work_id', 'publication_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropForeign(['publication_id']);
            $table->dropForeign(['task_type_id']);
            $table->dropIndex(['work_id', 'publication_id', 'status']);
            $table->dropColumn(['publication_id', 'task_type_id']);
        });

        Schema::dropIfExists('task_types');
    }
};
