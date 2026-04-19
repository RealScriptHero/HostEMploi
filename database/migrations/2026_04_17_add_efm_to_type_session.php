<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('emploi_du_temps') || ! Schema::hasColumn('emploi_du_temps', 'type_session')) {
            return;
        }

        // Column is string in PostgreSQL-compatible migrations; no SQL alteration needed.
    }

    public function down()
    {
        if (! Schema::hasTable('emploi_du_temps') || ! Schema::hasColumn('emploi_du_temps', 'type_session')) {
            return;
        }

        // No-op: type_session remains a string for compatibility.
    }
};
