<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This migration is optional - skip if the new index already exists
        if (Schema::hasTable('emploi_du_temps')) {
            Schema::table('emploi_du_temps', function (Blueprint $table) {
                try {
                    $table->dropUnique('edt_unique_groupe_date_creneau');
                } catch (\Throwable $e) {
                    // Index might not exist, continue
                }
            });

            Schema::table('emploi_du_temps', function (Blueprint $table) {
                try {
                    $table->unique(
                        ['groupe_id', 'date', 'jour', 'creneau'],
                        'edt_unique_groupe_date_jour_creneau'
                    );
                } catch (\Throwable $e) {
                    // Index might already exist
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('emploi_du_temps', function (Blueprint $table) {
            try {
                $table->dropUnique('edt_unique_groupe_date_jour_creneau');
            } catch (\Throwable $e) {
                // no-op
            }

            $table->unique(['groupe_id', 'date', 'creneau'], 'edt_unique_groupe_date_creneau');
        });
    }
};

