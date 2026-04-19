<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the default admin user (utilisateurs table only).
     */
    public function run(): void
    {
        if (! Schema::hasTable('utilisateurs')) {
            return;
        }

        Utilisateur::firstOrCreate(
            ['email' => 'zharimaha@gmail.com'],
            [
                'nom' => 'admin',
                'prenom' => 'admin',
                'motDePasse' => Hash::make('ofppt1122'),
                'role' => 'admin',
                'dateCreation' => now(),
            ]
        );
    }
}
