<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace old unique key with one that includes jour.
     */
    public function up(): void
    {
        if (! Schema::hasTable('emploi_du_temps')) {
            return;
        }

        $this->upgradeViaBlueprint();
    }

    public function down(): void
    {
        // Intentionally empty: reversing risks restoring a broken unique key on live data.
    }

    private function upgradeViaBlueprint(): void
    {
        // Skip for SQLite as it has different unique constraint handling
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }
        
        Schema::table('emploi_du_temps', function (Blueprint $table) {
            try {
                $table->index('groupe_id', 'emploi_du_temps_groupe_id_index');
            } catch (\Throwable $e) {
                // already present
            }
        });

        Schema::table('emploi_du_temps', function (Blueprint $table) {
            try {
                $table->dropUnique('edt_unique_groupe_date_creneau');
            } catch (\Throwable $e) {
                // already dropped
            }
        });

        Schema::table('emploi_du_temps', function (Blueprint $table) {
            try {
                $table->unique(
                    ['groupe_id', 'date', 'jour', 'creneau'],
                    'edt_unique_groupe_date_jour_creneau'
                );
            } catch (\Throwable $e) {
                // already exists
            }
        });
    }
};
