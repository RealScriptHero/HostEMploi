<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formateurs', function (Blueprint $table) {
            if (!Schema::hasColumn('formateurs', 'heures_totales_requises')) {
                $table->unsignedInteger('heures_totales_requises')->default(0);
            }
        });

        // Remove manual progress columns if exist
        if (Schema::hasColumn('formateurs', 'avancement')) {
            Schema::table('formateurs', function (Blueprint $table) {
                $table->dropColumn('avancement');
            });
        }

        if (Schema::hasColumn('formateurs', 'progression')) {
            Schema::table('formateurs', function (Blueprint $table) {
                $table->dropColumn('progression');
            });
        }

        if (Schema::hasColumn('formateurs', 'progress')) {
            Schema::table('formateurs', function (Blueprint $table) {
                $table->dropColumn('progress');
            });
        }
    }

    public function down(): void
    {
        Schema::table('formateurs', function (Blueprint $table) {
            if (Schema::hasColumn('formateurs', 'heures_totales_requises')) {
                $table->dropColumn('heures_totales_requises');
            }
        });
    }
};

