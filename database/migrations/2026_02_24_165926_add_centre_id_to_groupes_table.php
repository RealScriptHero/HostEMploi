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
        Schema::table('groupes', function (Blueprint $table) {
            // Add centre_id column
            $table->unsignedBigInteger('centre_id')->nullable();
            
            // Add foreign key constraint
            $table->foreign('centre_id')
                  ->references('id')
                  ->on('centres')
                  ->onDelete('set null'); // If centre is deleted, set groupe's centre_id to null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groupes', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['centre_id']);
            // Then drop column
            $table->dropColumn('centre_id');
        });
    }
};