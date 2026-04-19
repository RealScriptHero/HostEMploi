<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add masse_horaire to modules if not exists
        if (!Schema::hasColumn('modules', 'masse_horaire')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->unsignedInteger('masse_horaire')->default(0);
            });
        }

        // Add duree_heures to emploi_du_temps if not exists
        if (!Schema::hasColumn('emploi_du_temps', 'duree_heures')) {
            Schema::table('emploi_du_temps', function (Blueprint $table) {
                $table->decimal('duree_heures', 5, 2)->default(2.00);
            });
        }

        // Ensure module_groupe has heures_allouees (or create table if missing)
        if (!Schema::hasTable('module_groupe')) {
            Schema::create('module_groupe', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained()->onDelete('cascade');
                $table->foreignId('groupe_id')->constrained()->onDelete('cascade');
                $table->unsignedInteger('heures_allouees')->default(0);
                $table->timestamps();
                $table->unique(['module_id', 'groupe_id']);
            });
        } elseif (!Schema::hasColumn('module_groupe', 'heures_allouees')) {
            Schema::table('module_groupe', function (Blueprint $table) {
                $table->unsignedInteger('heures_allouees')->default(0);
            });
        }

        // Remove manual progress columns
        if (Schema::hasColumn('groupes', 'advancement')) {
            Schema::table('groupes', function (Blueprint $table) {
                $table->dropColumn('advancement');
            });
        }

        if (Schema::hasColumn('modules', 'advancement')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropColumn('advancement');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('module_groupe') && Schema::hasColumn('module_groupe', 'heures_allouees')) {
            Schema::table('module_groupe', function (Blueprint $table) {
                $table->dropColumn('heures_allouees');
            });
        }

        if (Schema::hasColumn('emploi_du_temps', 'duree_heures')) {
            Schema::table('emploi_du_temps', function (Blueprint $table) {
                $table->dropColumn('duree_heures');
            });
        }

        if (Schema::hasColumn('modules', 'masse_horaire')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropColumn('masse_horaire');
            });
        }
    }
};

