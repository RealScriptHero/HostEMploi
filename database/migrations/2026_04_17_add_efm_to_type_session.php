<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Modify the enum column to add 'efm' as an option
        DB::statement("ALTER TABLE emploi_du_temps MODIFY type_session ENUM('presentiel', 'distance', 'efm') DEFAULT 'presentiel'");
    }

    public function down()
    {
        // Revert to original enum without 'efm'
        DB::statement("ALTER TABLE emploi_du_temps MODIFY type_session ENUM('presentiel', 'distance') DEFAULT 'presentiel'");
    }
};
