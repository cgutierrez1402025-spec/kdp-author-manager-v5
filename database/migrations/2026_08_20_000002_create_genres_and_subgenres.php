<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('genres', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subgenres', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['genre_id', 'slug']);
        });

        Schema::create('genre_work', function (Blueprint $table): void {
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->primary(['genre_id', 'work_id']);
        });

        Schema::create('subgenre_work', function (Blueprint $table): void {
            $table->foreignId('subgenre_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->primary(['subgenre_id', 'work_id']);
        });

        foreach ([
            ['Ficción', 'ficcion'], ['No ficción', 'no-ficcion'], ['Infantil', 'infantil'],
            ['Romance', 'romance'], ['Misterio, thriller y suspense', 'misterio-thriller-suspense'],
        ] as [$name, $slug]) {
            DB::table('genres')->insert(['name' => $name, 'slug' => $slug, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subgenre_work');
        Schema::dropIfExists('genre_work');
        Schema::dropIfExists('subgenres');
        Schema::dropIfExists('genres');
    }
};
