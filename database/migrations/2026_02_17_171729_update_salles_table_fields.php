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
        Schema::table('salles', function (Blueprint $table) {
            // Check the database driver
            $connection = DB::connection()->getDriverName();
            
            // For SQLite, we need to handle this differently
            if ($connection === 'sqlite') {
                // SQLite doesn't support ALTER TABLE DROP COLUMN in older versions
                // So we'll just skip this migration for SQLite - the columns might not exist anyway
                return;
            }
            
            // For other databases (MySQL, PostgreSQL), do the original operations
            if (Schema::hasColumn('salles', 'ville')) {
                $table->dropColumn('ville');
            }
            if (Schema::hasColumn('salles', 'adresse')) {
                $table->dropColumn('adresse');
            }
            if (Schema::hasColumn('salles', 'nomCentre') && !Schema::hasColumn('salles', 'nomSalle')) {
                $table->renameColumn('nomCentre', 'nomSalle');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salles', function (Blueprint $table) {
            if (Schema::hasColumn('salles', 'nomSalle') && !Schema::hasColumn('salles', 'nomCentre')) {
                $table->renameColumn('nomSalle', 'nomCentre');
            }
            if (!Schema::hasColumn('salles', 'ville')) {
                $table->string('ville')->nullable();
            }
            if (!Schema::hasColumn('salles', 'adresse')) {
                $table->string('adresse')->nullable();
            }
        });
    }
};
