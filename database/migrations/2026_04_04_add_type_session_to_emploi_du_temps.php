<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('emploi_du_temps', function (Blueprint $table) {
            if (!Schema::hasColumn('emploi_du_temps', 'type_session')) {
                // Use string for cross-database compatibility (PostgreSQL/MySQL).
                $table->string('type_session', 20)
                    ->default('presentiel');
            }
        });
    }

    public function down()
    {
        Schema::table('emploi_du_temps', function (Blueprint $table) {
            if (Schema::hasColumn('emploi_du_temps', 'type_session')) {
                $table->dropColumn('type_session');
            }
        });
    }
};
