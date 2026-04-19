<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add academic_year to emploi_du_temps table
        if (!Schema::hasColumn('emploi_du_temps', 'academic_year')) {
            Schema::table('emploi_du_temps', function (Blueprint $table) {
                $table->string('academic_year')->nullable();
                $table->index(['academic_year', 'date']);
            });
        }

        // Add academic_year to absence_formateurs table
        if (!Schema::hasColumn('absence_formateurs', 'academic_year')) {
            Schema::table('absence_formateurs', function (Blueprint $table) {
                $table->string('academic_year')->nullable();
                $table->index(['academic_year', 'dateAbsence']);
            });
        }

        // Add academic_year to absence_groupes table
        if (!Schema::hasColumn('absence_groupes', 'academic_year')) {
            Schema::table('absence_groupes', function (Blueprint $table) {
                $table->string('academic_year')->nullable();
                $table->index(['academic_year', 'dateAbsence']);
            });
        }

        // Add academic_year to avancements table
        if (!Schema::hasColumn('avancements', 'academic_year')) {
            Schema::table('avancements', function (Blueprint $table) {
                $table->string('academic_year')->nullable();
                $table->index(['academic_year', 'dateLastUpdate']);
            });
        }

        // Add academic_year to stages table
        if (!Schema::hasColumn('stages', 'academic_year')) {
            Schema::table('stages', function (Blueprint $table) {
                $table->string('academic_year')->nullable();
                $table->index(['academic_year']);
            });
        }

        // Add academic_year to groupes table for advancement tracking
        if (!Schema::hasColumn('groupes', 'academic_year')) {
            Schema::table('groupes', function (Blueprint $table) {
                $table->string('academic_year')->nullable();
                $table->index(['academic_year']);
            });
        }

        // Add academic_year to modules table for advancement tracking
        if (!Schema::hasColumn('modules', 'academic_year')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->string('academic_year')->nullable();
                $table->index(['academic_year']);
            });
        }

        // Add academic_year to formateurs table for advancement tracking
        if (!Schema::hasColumn('formateurs', 'academic_year')) {
            Schema::table('formateurs', function (Blueprint $table) {
                $table->string('academic_year')->nullable();
                $table->index(['academic_year']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove academic_year from all tables with explicit index names.
        $indexMap = [
            'emploi_du_temps'   => 'emploi_du_temps_academic_year_date_index',
            'absence_formateurs'=> 'absence_formateurs_academic_year_dateabsence_index',
            'absence_groupes'   => 'absence_groupes_academic_year_dateabsence_index',
            'avancements'       => 'avancements_academic_year_datelastupdate_index',
            'stages'            => 'stages_academic_year_index',
            'groupes'           => 'groupes_academic_year_index',
            'modules'           => 'modules_academic_year_index',
            'formateurs'        => 'formateurs_academic_year_index',
        ];

        foreach ($indexMap as $tableName => $indexName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'academic_year')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                try {
                    $table->dropIndex($indexName);
                } catch (\Throwable $e) {
                    // Ignore missing index; proceed with column drop.
                }
                $table->dropColumn('academic_year');
            });
        }
    }
};
