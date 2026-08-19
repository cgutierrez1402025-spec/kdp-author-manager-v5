<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table): void {
            $table->string('author_name')->nullable()->change();
            $table->char('original_language', 2)->nullable()->change();
            $table->string('kdp_identity_key', 64)->nullable()->after('slug');
            $table->unique(['user_id', 'kdp_identity_key'], 'works_user_kdp_identity_unique');
        });
        Schema::table('publications', function (Blueprint $table): void {
            $table->foreignId('work_language_id')->nullable()->change();
        });
        Schema::table('kdp_metadata', function (Blueprint $table): void {
            $table->string('author')->nullable()->change();
        });
        Schema::table('marketplaces', function (Blueprint $table): void {
            $table->char('currency', 3)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table): void {
            $table->dropUnique('works_user_kdp_identity_unique');
            $table->dropColumn('kdp_identity_key');
            $table->string('author_name')->nullable(false)->change();
            $table->char('original_language', 2)->nullable(false)->change();
        });
        Schema::table('publications', fn (Blueprint $table) => $table->foreignId('work_language_id')->nullable(false)->change());
        Schema::table('kdp_metadata', fn (Blueprint $table) => $table->string('author')->nullable(false)->change());
        Schema::table('marketplaces', fn (Blueprint $table) => $table->char('currency', 3)->nullable(false)->change());
    }
};
