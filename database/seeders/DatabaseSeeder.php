<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Utilisateur;
use App\Models\Centre;
use App\Models\Salle;
use App\Models\Groupe;
use App\Models\Module;
use App\Models\Formateur;
use App\Models\EmploiDuTemps;
use App\Models\AbsenceFormateur;
use App\Models\AbsenceGroupe;
use App\Models\Avancement;
use App\Models\Stage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the AdminUserSeeder to create the default admin account
        $this->call(AdminUserSeeder::class);

        // Seed Utilisateurs safely without creating duplicates on repeated deploys.
        Utilisateur::firstOrCreate(['email' => 'admin@ofppt.org'], [
            'nom' => 'Admin',
            'prenom' => 'System',
            'motDePasse' => Hash::make('password'),
            'role' => 'admin',
            'dateCreation' => now(),
        ]);

        Utilisateur::firstOrCreate(['email' => 'responsable@ofppt.org'], [
            'nom' => 'Responsable',
            'prenom' => 'Centre',
            'motDePasse' => Hash::make('password'),
            'role' => 'responsable',
            'dateCreation' => now(),
        ]);

        // Seed Centres
        $centre1 = Centre::create([
            'nomCentre' => 'Centre de Formation Casablanca',
            'ville' => 'Casablanca',
            'adresse' => 'Boulevard de la Corniche',
        ]);

        $centre2 = Centre::create([
            'nomCentre' => 'Centre de Formation Marrakech',
            'ville' => 'Marrakech',
            'adresse' => 'Avenue Mohammed V',
        ]);

        $centre3 = Centre::create([
            'nomCentre' => 'Centre de Formation Fès',
            'ville' => 'Fès',
            'adresse' => 'Rue Hassan II',
        ]);

        // Seed Salles
        $salle1 = Salle::create([
            'nomCentre' => 'Salle A1',
            'ville' => 'Casablanca',
            'adresse' => 'Boulevard de la Corniche',
            'centre_id' => $centre1->id,
        ]);

        $salle2 = Salle::create([
            'nomCentre' => 'Salle A2',
            'ville' => 'Casablanca',
            'adresse' => 'Boulevard de la Corniche',
            'centre_id' => $centre1->id,
        ]);

        $salle3 = Salle::create([
            'nomCentre' => 'Salle B1',
            'ville' => 'Marrakech',
            'adresse' => 'Avenue Mohammed V',
            'centre_id' => $centre2->id,
        ]);

        $salle4 = Salle::create([
            'nomCentre' => 'Salle C1',
            'ville' => 'Fès',
            'adresse' => 'Rue Hassan II',
            'centre_id' => $centre3->id,
        ]);

        // Seed Groupes
        $groupe1 = Groupe::create([
            'nomGroupe' => 'TSSFC-A1',
            'filiere' => 'Technicien Spécialisé en Systèmes d\'Exploitation',
            'niveau' => '2ème année',
        ]);

        $groupe2 = Groupe::create([
            'nomGroupe' => 'TSSFC-B1',
            'filiere' => 'Technicien Spécialisé en Systèmes d\'Exploitation',
            'niveau' => '2ème année',
        ]);

        $groupe3 = Groupe::create([
            'nomGroupe' => 'TSDEV-A1',
            'filiere' => 'Technicien Spécialisé en Développement Digital',
            'niveau' => '1ère année',
        ]);

        $groupe4 = Groupe::create([
            'nomGroupe' => 'TSDEV-B1',
            'filiere' => 'Technicien Spécialisé en Développement Digital',
            'niveau' => '2ème année',
        ]);

        $groupe5 = Groupe::create([
            'nomGroupe' => 'TSNCD-A1',
            'filiere' => 'Technicien Spécialisé en Réseaux et Communication',
            'niveau' => '1ère année',
        ]);

        // Seed Modules
        $module1 = Module::create([
            'nomModule' => 'Systèmes d\'Exploitation Linux',
            'codeModule' => 'SE-LINUX-001',
            'volumeHoraire' => 40,
            'semestre' => 3,
        ]);

        $module2 = Module::create([
            'nomModule' => 'PHP et Laravel',
            'codeModule' => 'DEV-PHP-001',
            'volumeHoraire' => 50,
            'semestre' => 3,
        ]);

        $module3 = Module::create([
            'nomModule' => 'Réseaux et TCP/IP',
            'codeModule' => 'RES-TCP-001',
            'volumeHoraire' => 45,
            'semestre' => 3,
        ]);

        $module4 = Module::create([
            'nomModule' => 'Bases de Données MySQL',
            'codeModule' => 'BDD-MYSQL-001',
            'volumeHoraire' => 40,
            'semestre' => 4,
        ]);

        $module5 = Module::create([
            'nomModule' => 'JavaScript et Vue.js',
            'codeModule' => 'DEV-JS-001',
            'volumeHoraire' => 50,
            'semestre' => 4,
        ]);

        $module6 = Module::create([
            'nomModule' => 'Administration Système',
            'codeModule' => 'SE-ADMIN-001',
            'volumeHoraire' => 35,
            'semestre' => 4,
        ]);

        $module7 = Module::create([
            'nomModule' => 'Sécurité Informatique',
            'codeModule' => 'SEC-IT-001',
            'volumeHoraire' => 30,
            'semestre' => 3,
        ]);

        $module8 = Module::create([
            'nomModule' => 'HTML et CSS Avancé',
            'codeModule' => 'WEB-CSS-001',
            'volumeHoraire' => 25,
            'semestre' => 3,
        ]);

        // Seed Formateurs
        $formateur1 = Formateur::create([
            'nom' => 'Benali',
            'prenom' => 'Mohammed',
            'specialite' => 'Systèmes d\'Exploitation',
            'telephone' => '06 12 34 56 78',
            'email' => 'benali@ofppt.org',
        ]);

        $formateur2 = Formateur::create([
            'nom' => 'Jaradi',
            'prenom' => 'Fatima',
            'specialite' => 'Développement Web',
            'telephone' => '06 23 45 67 89',
            'email' => 'jaradi@ofppt.org',
        ]);

        $formateur3 = Formateur::create([
            'nom' => 'Saadi',
            'prenom' => 'Ahmed',
            'specialite' => 'Réseaux et Communication',
            'telephone' => '06 34 56 78 90',
            'email' => 'saadi@ofppt.org',
        ]);

        $formateur4 = Formateur::create([
            'nom' => 'Zaoui',
            'prenom' => 'Leila',
            'specialite' => 'Bases de Données',
            'telephone' => '06 45 67 89 01',
            'email' => 'zaoui@ofppt.org',
        ]);

        $formateur5 = Formateur::create([
            'nom' => 'Bennani',
            'prenom' => 'Hassan',
            'specialite' => 'Sécurité Informatique',
            'telephone' => '06 56 78 90 12',
            'email' => 'bennani@ofppt.org',
        ]);

        // Seed Emploi du Temps
        EmploiDuTemps::create([
            'jour' => 'Lundi',
            'pour' => 'TSSFC-A1',
            'heureDebut' => '08:00',
            'heureFin' => '10:00',
            'formateur_id' => $formateur1->id,
            'module_id' => $module1->id,
            'salle_id' => $salle1->id,
        ]);

        EmploiDuTemps::create([
            'jour' => 'Lundi',
            'pour' => 'TSSFC-A1',
            'heureDebut' => '10:15',
            'heureFin' => '12:15',
            'formateur_id' => $formateur2->id,
            'module_id' => $module2->id,
            'salle_id' => $salle1->id,
        ]);

        EmploiDuTemps::create([
            'jour' => 'Mardi',
            'pour' => 'TSDEV-A1',
            'heureDebut' => '09:00',
            'heureFin' => '11:00',
            'formateur_id' => $formateur2->id,
            'module_id' => $module8->id,
            'salle_id' => $salle2->id,
        ]);

        EmploiDuTemps::create([
            'jour' => 'Mercredi',
            'pour' => 'TSNCD-A1',
            'heureDebut' => '08:00',
            'heureFin' => '10:00',
            'formateur_id' => $formateur3->id,
            'module_id' => $module3->id,
            'salle_id' => $salle3->id,
        ]);

        // Seed Absence Formateurs
        AbsenceFormateur::create([
            'dateAbsence' => now()->subDays(5),
            'motif' => 'Maladie',
            'formateur_id' => $formateur1->id,
        ]);

        // Seed Absence Groupes
        AbsenceGroupe::create([
            'dateAbsence' => now()->subDays(2),
            'motif' => 'Sortie pédagogique',
            'groupe_id' => $groupe1->id,
        ]);

        // Seed Avancements
        Avancement::create([
            'pourcentage' => 65,
            'dateLastUpdate' => now(),
            'modifie_id' => 1,
            'formateur_id' => $formateur1->id,
        ]);

        Avancement::create([
            'pourcentage' => 78,
            'dateLastUpdate' => now(),
            'modifie_id' => 1,
            'formateur_id' => $formateur2->id,
        ]);

        // Seed Stages
        Stage::create([
            'date' => now(),
            'dateDebut' => now()->addMonths(1),
            'dateFin' => now()->addMonths(1)->addDays(15),
            'groupe_id' => $groupe1->id,
            'formateur_id' => $formateur1->id,
        ]);

        Stage::create([
            'date' => now(),
            'dateDebut' => now()->addMonths(2),
            'dateFin' => now()->addMonths(2)->addDays(20),
            'groupe_id' => $groupe3->id,
            'formateur_id' => $formateur2->id,
        ]);
    }
}
