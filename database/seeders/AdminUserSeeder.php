<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the default admin user.
     */
    public function run(): void
    {
        // Create default admin only on first deploy (empty users table).
        if (Utilisateur::count() === 0) {
            Utilisateur::create([
                'nom' => 'admin',
                'prenom' => 'admin',
                'email' => 'zharimaha@gmail.com',
                'motDePasse' => Hash::make('ofppt1122'),
                'role' => 'admin',
                'dateCreation' => now(),
            ]);
        }
    }
}
