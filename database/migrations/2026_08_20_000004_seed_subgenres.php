<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'Ficción' => ['Novela', 'Cuento'],
            'No ficción' => ['Ensayo', 'Autoayuda'],
            'Infantil' => ['Primeros lectores', 'Cuentos infantiles'],
            'Romance' => ['Romance contemporáneo', 'Romance histórico'],
            'Misterio, thriller y suspense' => ['Misterio', 'Thriller'],
        ] as $genreName => $subgenres) {
            $genreId = DB::table('genres')->where('name', $genreName)->value('id');
            foreach ($subgenres as $name) {
                DB::table('subgenres')->insertOrIgnore([
                    'genre_id' => $genreId,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('subgenres')->whereIn('name', [
            'Novela', 'Cuento', 'Ensayo', 'Autoayuda', 'Primeros lectores',
            'Cuentos infantiles', 'Romance contemporáneo', 'Romance histórico',
            'Misterio', 'Thriller',
        ])->delete();
    }
};
