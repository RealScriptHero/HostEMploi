<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" />
  <img src="https://img.shields.io/badge/Vite-5.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" />
</p>

# 📅 eDT Pro — Système de Gestion des Emplois du Temps

> **ENSET / OFPPT** — Application web de gestion des emplois du temps, des formateurs, des groupes et des modules pour les établissements de formation professionnelle.

---

## 📋 Table des Matières

- [Aperçu](#-aperçu)
- [Fonctionnalités](#-fonctionnalités)
- [Architecture Technique](#-architecture-technique)
- [Structure du Projet](#-structure-du-projet)
- [Installation](#-installation)
- [Lancement](#️-lancement)
- [Pages et Routes](#-pages--routes)
- [Modèles de Données](#-modèles-de-données)
- [Composants Frontend](#-composants-frontend)
- [Import / Export CSV](#-import--export-csv)
- [Persistence des Données](#-persistence-des-données)
- [Technologies Utilisées](#-technologies-utilisées)
- [Auteurs](#-auteurs)

---

## 🌟 Aperçu

**eDT Pro** est une application web de gestion scolaire conçue pour l'**ENSET / OFPPT**. Elle permet aux directeurs de centres et aux administrateurs de :

- 📅 Créer et gérer les **emplois du temps** des formateurs
- 👥 Gérer les **groupes** de stagiaires par filière (DEV, NET, RESEAUX)
- 📚 Suivre l'**avancement des modules** de formation
- 👨‍🏫 Gérer les **formateurs**, **salles** et **centres**
- 📤 **Importer/Exporter** des données au format CSV

L'application adopte une approche **frontend-first** avec Alpine.js pour l'interactivité et `localStorage` pour la persistence des données côté client.

---

## ✨ Fonctionnalités

### 🏠 Tableau de Bord

| Composant | Description |
|-----------|-------------|
| **Cartes statistiques** | 4 cartes : Classes actives, Salles, Départements, Professeurs |
| **Accès rapide** | Liens directs vers Emploi Formateur, Groupes, Modules, Formateurs |
| **Avancement des modules** | Anneau de progression global, barres par module, compteurs par statut |

### 📅 Emploi du Temps Formateur

| Fonctionnalité | Détail |
|----------------|--------|
| **Grille interactive** | 5 formateurs × 6 jours × 4 séances |
| **3 lignes par formateur** | Groupe, Module, Salle — chacune avec menu déroulant |
| **Code couleur jours** | Lundi 🔵, Mardi 🟣, Mercredi 🟠, Jeudi 🟡, Vendredi 🟢, Samedi 🩷 |
| **Code couleur cellules** | Groupe (bleu), Module (jaune), Salle (indigo) |
| **Modules filtrés** | Chaque formateur ne voit que ses modules assignés |
| **Sauvegarde** | Sauvegarde/Réinitialisation vers localStorage |
| **Import/Export** | CSV complet |

### 👥 Gestion des Groupes

- CRUD complet (ajouter, modifier, supprimer)
- Filtres par filière (DEV, NET, RESEAUX) et par niveau
- Recherche en temps réel
- Pagination (8 par page)
- Badges colorés par filière
- Import/Export CSV

### 📚 Gestion des Modules

- CRUD complet avec champs : Code, Titre, Filière, Semestre (S1–S4), Heures, Description
- Barres de progression colorées (🟢 ≥75%, 🟡 ≥40%, 🔴 <40%)
- Recherche par code, titre, filière ou semestre
- Pagination (8 par page)
- Import/Export CSV

### 👨‍🏫 Gestion des Formateurs

- Affectation de modules aux formateurs
- Suivi du statut actif/inactif
- Modal d'ajout/modification

### 🎨 Interface Utilisateur

- **Sidebar rétractable** : Thème sombre (`#1e293b`), navigation groupée avec menus déroulants
- **Barre supérieure** : Branding ENSET, recherche, notifications, profil utilisateur avec déconnexion
- **Responsive** : Sidebar off-canvas, composants adaptatifs
- **Police** : Figtree (Bunny Fonts CDN)

---

## 🏗️ Architecture Technique

```
┌─────────────────────────────────────────────────────┐
│                     NAVIGATEUR                       │
│                                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌───────────┐ │
│  │  Alpine.js    │  │ Tailwind CSS │  │   Vite    │ │
│  │  Components   │  │   Styling    │  │   HMR     │ │
│  └──────┬───────┘  └──────────────┘  └───────────┘ │
│         │                                           │
│  ┌──────▼───────┐                                   │
│  │ localStorage  │ ← Persistence des données        │
│  └──────────────┘                                   │
│                                                     │
├─────────────────────────────────────────────────────┤
│                     SERVEUR                          │
│                                                     │
│  ┌──────────────┐  ┌──────────────┐                │
│  │  Laravel 10   │──│ Blade Views  │                │
│  │  Routes/Ctrl  │  │  Templates   │                │
│  └──────────────┘  └──────────────┘                │
│                                                     │
│  ┌──────────────┐                                   │
│  │  MySQL (DB)   │ ← Schéma défini, usage minimal  │
│  └──────────────┘                                   │
└─────────────────────────────────────────────────────┘
```

### Flux de données

1. **Laravel** sert les vues Blade (HTML initial)
2. **Alpine.js** gère toute l'interactivité (CRUD, filtres, modals, dropdowns)
3. Les données sont définies en **JavaScript statique** dans les composants Alpine
4. La **persistence** se fait via `localStorage` du navigateur
5. Les **événements personnalisés** (`modules-updated`, `groups-updated`) synchronisent les composants

---

## 📁 Structure du Projet

```
emploi/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php            # Tableau de bord
│   │   │   └── FormateurController.php       # Emploi formateur
│   │   ├── Middleware/                        # Middlewares Laravel
│   │   └── Requests/                         # Form requests
│   ├── Models/
│   │   ├── Group.php                         # Modèle Groupe
│   │   ├── Module.php                        # Modèle Module
│   │   ├── Trainer.php                       # Modèle Formateur (→ modules)
│   │   └── User.php                          # Modèle Utilisateur
│   └── Providers/                            # Service providers
│
├── database/
│   └── migrations/
│       ├── create_groups_table.php            # id, name, description
│       ├── create_trainers_table.php          # id, name, specialty
│       ├── create_modules_table.php           # id, name, code
│       └── create_trainer_module_table.php    # Pivot table
│
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php                     # Layout principal + top bar
│   │   └── sidebar.blade.php                 # Navigation sidebar sombre
│   ├── home/index.blade.php                  # Tableau de bord
│   ├── emploi/
│   │   ├── formateur.blade.php               # Grille emploi du temps
│   │   └── global.blade.php                  # Emploi global
│   ├── groupes/index.blade.php               # CRUD groupes
│   ├── modules/index.blade.php               # CRUD modules
│   ├── formateurs/index.blade.php            # CRUD formateurs
│   ├── salles/                               # Gestion salles
│   ├── centers/                              # Gestion centres
│   ├── stage/                                # Gestion stages
│   ├── absence/                              # Suivi absences
│   ├── reports/                              # Rapports
│   ├── avancement/                           # Avancement modules
│   └── parametres/                           # Paramètres
│
├── routes/
│   ├── web.php                               # 14 routes GET + 1 POST
│   └── auth.php                              # Auth (Laravel Breeze)
│
├── public/                                   # Assets publics
├── composer.json                             # Dépendances PHP
├── package.json                              # Dépendances Node.js
├── tailwind.config.js                        # Configuration Tailwind
├── vite.config.js                            # Configuration Vite
└── .env                                      # Variables d'environnement
```

---

## 🚀 Installation

### Prérequis

| Outil | Version minimale |
|-------|-----------------|
| **PHP** | ≥ 8.1 |
| **Composer** | ≥ 2.x |
| **Node.js** | ≥ 18.x |
| **npm** | ≥ 9.x |
| **MySQL** | ≥ 8.0 *(optionnel)* |

### Étapes d'installation

```bash
# 1. Cloner le dépôt
git clone https://github.com/RealScriptHero/emploi.git
cd emploi

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances Node.js
npm install

# 4. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 5. (Optionnel) Configurer la base de données dans .env
#    DB_DATABASE=emploi_db
#    DB_USERNAME=root
#    DB_PASSWORD=

# 6. (Optionnel) Exécuter les migrations
php artisan migrate
```

> **💡 Note** : La base de données est **optionnelle**. L'application fonctionne entièrement avec des données statiques côté client et `localStorage`.

---

## ▶️ Lancement

### Développement

```bash
# Terminal 1 — Serveur Laravel
php artisan serve
# → http://localhost:8000

# Terminal 2 — Vite (HMR pour CSS/JS)
npm run dev
# → http://localhost:5173
```

### Production

```bash
# Compiler les assets
npm run build

# Servir avec un serveur web (Apache/Nginx)
```

---

## 🗺️ Pages & Routes

| Route | Méthode | Nom | Description |
|-------|---------|-----|-------------|
| `/` | GET | `home` | Tableau de bord avec statistiques |
| `/emploi-global` | GET | `emploi.global` | Emploi du temps global |
| `/emploi-formateur` | GET | `emploi.formateur` | Grille EDT par formateur |
| `/groupes` | GET | `groupes.index` | Gestion des groupes |
| `/modules` | GET | `modules.index` | Gestion des modules |
| `/formateurs` | GET | `formateurs.index` | Gestion des formateurs |
| `/salles` | GET | `salles.index` | Gestion des salles |
| `/centers` | GET | `centers.index` | Gestion des centres |
| `/stage` | GET | `stage.index` | Gestion des stages |
| `/absence` | GET | `absence.index` | Suivi des absences |
| `/reports` | GET | `reports.index` | Rapports |
| `/avancement` | GET | `avancement.index` | Avancement des modules |
| `/parametres` | GET | `parametres.index` | Paramètres |
| `/logout` | POST | `logout` | Déconnexion |

---

## 🗄️ Modèles de Données

### Schéma de la Base de Données

```
┌──────────────┐       ┌───────────────────┐       ┌──────────────┐
│   trainers    │       │  trainer_module    │       │   modules     │
├──────────────┤       ├───────────────────┤       ├──────────────┤
│ id            │──┐    │ id                │    ┌──│ id            │
│ name          │  └──→ │ trainer_id  (FK)  │    │  │ name          │
│ specialty     │       │ module_id   (FK)  │ ←──┘  │ code (unique) │
│ timestamps    │       │ timestamps        │       │ timestamps    │
└──────────────┘       └───────────────────┘       └──────────────┘

┌──────────────┐
│   groups      │
├──────────────┤
│ id            │
│ name (unique) │
│ description   │
│ timestamps    │
└──────────────┘
```

### Données Statiques (JavaScript côté client)

| Entité | Quantité par défaut | Clé localStorage |
|--------|---------------------|-------------------|
| **Modules** | 10 modules | `app_modules` |
| **Groupes** | 7 groupes | `app_groups` |
| **Formateurs** | 5 formateurs | — |
| **Salles** | 9 salles | — |
| **Emploi du temps** | Grille complète | `formateurTimetable` |

---

## 🧩 Composants Frontend (Alpine.js)

Chaque page majeure utilise un composant Alpine.js enregistré via `Alpine.data()` :

| Composant | Fichier | Fonctionnalités |
|-----------|---------|-----------------|
| `homePage` | `home/index.blade.php` | Compteurs dynamiques, stats modules, accès rapide |
| `formateurTimetable` | `emploi/formateur.blade.php` | Grille EDT, CRUD cellules, import/export CSV |
| `groupsComponent` | `groupes/index.blade.php` | CRUD groupes, filtres, recherche, pagination |
| `modulesPage` | `modules/index.blade.php` | CRUD modules, recherche, pagination, progression |

### Pattern utilisé

```javascript
// Composant enregistré dans un <script> en bas de page Blade
document.addEventListener('alpine:init', () => {
    Alpine.data('nomDuComposant', () => ({
        // État réactif
        items: [],
        modalOpen: false,
        search: '',

        // Initialisation (charge depuis localStorage)
        init() {
            var saved = localStorage.getItem('key');
            if (saved) this.items = JSON.parse(saved);
        },

        // Méthodes CRUD
        save() { /* ... */ },
        persist() {
            localStorage.setItem('key', JSON.stringify(this.items));
            window.dispatchEvent(new CustomEvent('items-updated'));
        }
    }));
});
```

> ⚠️ **Important** : Les composants sont définis dans des `<script>` tags (et non en inline `x-data="{ ... }"`) pour éviter les problèmes d'échappement HTML (`&quot;`) dans les attributs Blade.

---

## 📤 Import / Export CSV

### Export

Chaque page génère un fichier CSV horodaté côté client :

```
modules_2026-02-15.csv
groupes_2026-02-15.csv
formateur_timetable_2026-02-15.csv
```

### Import

Le fichier CSV est lu via l'API `FileReader` et parsé côté client. Les doublons (même code/nom) sont automatiquement ignorés.

### Format CSV — Modules

```csv
Code,Title,Filiere,Semester,Hours,Progress,Description
"M101-DEV","Programmation structurée","DEV","S1",60,75,""
"M201-BDD","Base de données","Commun","S2",50,60,""
```

### Format CSV — Emploi du Temps

```csv
Trainer,Day,Slot,Group,Module,Salle
"M. Alami","Lundi","S1","DEV101","M101-DEV","S01"
```

---

## 💾 Persistence des Données

| Clé localStorage | Page source | Contenu |
|-------------------|-------------|---------|
| `app_modules` | Modules | Tableau JSON de tous les modules |
| `app_groups` | Groupes | Tableau JSON de tous les groupes |
| `formateurTimetable` | Emploi Formateur | Objet JSON de la grille complète |

### Synchronisation entre pages

```javascript
// Dispatché après chaque modification
window.dispatchEvent(new CustomEvent('modules-updated'));
window.dispatchEvent(new CustomEvent('groups-updated'));

// Écouté par le tableau de bord pour rafraîchir les stats
window.addEventListener('modules-updated', () => this.loadModules());
```

---

## 🛠️ Technologies Utilisées

| Technologie | Version | Rôle |
|-------------|---------|------|
| **Laravel** | 10.x | Framework backend, routing, Blade templating |
| **PHP** | ≥ 8.1 | Runtime serveur |
| **Alpine.js** | 3.x | Réactivité frontend (composants, modals, dropdowns, filtres) |
| **Tailwind CSS** | 3.x | Framework CSS utility-first |
| **@tailwindcss/forms** | 0.5.x | Plugin formulaires stylisés |
| **Vite** | 5.x | Build tool et Hot Module Replacement |
| **Axios** | 1.6.x | Client HTTP (disponible) |
| **MySQL** | 8.x | Base de données (schéma défini, usage minimal) |
| **Laravel Breeze** | 1.29 | Scaffolding d'authentification |
| **Laravel Sanctum** | 3.x | API token authentication |

---

## 📊 Filières OFPPT

| Code | Filière | Couleur |
|------|---------|---------|
| **DEV** | Développement Digital | 🔵 Bleu |
| **NET** | Réseaux Informatiques | 🟣 Violet |
| **RESEAUX** | Infrastructure Digitale | 🟠 Orange |
| **Commun** | Modules transversaux | ⚪ Gris |

---

## 🎨 Thème & Design

| Élément | Style |
|---------|-------|
| **Sidebar** | Thème sombre `#1e293b`, navigation groupée, collapsible |
| **Top bar** | Blanche, branding ENSET, recherche, notifications, profil |
| **Emploi du temps** | Jours colorés, cellules colorées par type |
| **Progression** | 🟢 ≥75% · 🟡 ≥40% · 🔴 <40% |
| **Police** | Figtree (Bunny Fonts CDN) |

---

## 👥 Auteurs

| Rôle | Nom |
|------|-----|
| **Développeur** | ANASS |
| **Établissement** | ENSET — OFPPT |

---

## 📄 Licence

Ce projet est développé dans un cadre éducatif pour l'**OFPPT / ENSET**.

---

<p align="center">
  <strong>COPYRIGHT © 2023 ENSET</strong><br/>
  <em>eDT Pro — Système de Gestion des Emplois du Temps</em>
</p>
# Emploi-de-temps
# Emploi-de-temps
# emploiV1
# emploiTempsv1
# Emploi2VMAIN
