<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy migration kept to preserve migration history. The canonical
        // schema is defined by create_kdp_author_manager_tables.
    }

    public function down(): void
    {
        // Intentionally empty: this migration no longer owns any tables.
    }
};
