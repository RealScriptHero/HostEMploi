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
        // Step 1: Update all dynamic data to correct academic year (2025/2026)
        \Illuminate\Support\Facades\DB::table('emploi_du_temps')->update(['academic_year' => '2025/2026']);
        \Illuminate\Support\Facades\DB::table('absence_formateurs')->update(['academic_year' => '2025/2026']);
        \Illuminate\Support\Facades\DB::table('absence_groupes')->update(['academic_year' => '2025/2026']);
        \Illuminate\Support\Facades\DB::table('avancements')->update(['academic_year' => '2025/2026']);
        \Illuminate\Support\Facades\DB::table('stages')->update(['academic_year' => '2025/2026']);

        // Step 2: Remove academic_year from static/base data tables
        // These tables should NEVER depend on academic year
        
        // Drop from groupes table
        if (Schema::hasColumn('groupes', 'academic_year')) {
            Schema::table('groupes', function (Blueprint $table) {
                try {
                    $table->dropIndex(['academic_year']);
                } catch (\Throwable $e) {
                    // Index might not exist.
                }
                $table->dropColumn('academic_year');
            });
        }

        // Drop from modules table
        if (Schema::hasColumn('modules', 'academic_year')) {
            Schema::table('modules', function (Blueprint $table) {
                try {
                    $table->dropIndex(['academic_year']);
                } catch (\Throwable $e) {
                    // Index might not exist.
                }
                $table->dropColumn('academic_year');
            });
        }

        // Drop from formateurs table
        if (Schema::hasColumn('formateurs', 'academic_year')) {
            Schema::table('formateurs', function (Blueprint $table) {
                try {
                    $table->dropIndex(['academic_year']);
                } catch (\Throwable $e) {
                    // Index might not exist.
                }
                $table->dropColumn('academic_year');
            });
        }

        // Drop from salles table (if it has the column)
        if (Schema::hasColumn('salles', 'academic_year')) {
            Schema::table('salles', function (Blueprint $table) {
                try {
                    $table->dropIndex(['academic_year']);
                } catch (\Throwable $e) {
                    // Index might not exist.
                }
                $table->dropColumn('academic_year');
            });
        }

        // Drop from centres table (if it has the column)
        if (Schema::hasColumn('centres', 'academic_year')) {
            Schema::table('centres', function (Blueprint $table) {
                try {
                    $table->dropIndex(['academic_year']);
                } catch (\Throwable $e) {
                    // Index might not exist.
                }
                $table->dropColumn('academic_year');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore academic_year columns to static tables
        if (!Schema::hasColumn('groupes', 'academic_year')) {
            Schema::table('groupes', function (Blueprint $table) {
                $table->string('academic_year')->nullable();
                $table->index(['academic_year']);
            });
        }

        if (!Schema::hasColumn('modules', 'academic_year')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->string('academic_year')->nullable();
                $table->index(['academic_year']);
            });
        }

        if (!Schema::hasColumn('formateurs', 'academic_year')) {
            Schema::table('formateurs', function (Blueprint $table) {
                $table->string('academic_year')->nullable();
                $table->index(['academic_year']);
            });
        }
    }
};
