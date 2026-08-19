<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('royalty_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('royalty_entries', 'net_royalty')) {
                $table->decimal('net_royalty', 10, 2)->default(0)->after('gross_revenue');
            }
        });
    }

    public function down(): void
    {
        Schema::table('royalty_entries', function (Blueprint $table) {
            $table->dropColumn('net_royalty');
        });
    }
};
