<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['Escritura', 'Edición', 'Portada', 'Formato', 'Publicación', 'Marketing'] as $name) {
            DB::table('task_types')->insertOrIgnore([
                'name' => $name,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('task_types')->whereIn('name', ['Escritura', 'Edición', 'Portada', 'Formato', 'Publicación', 'Marketing'])->delete();
    }
};
