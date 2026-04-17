<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\CentreController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\GroupeController;
use App\Http\Controllers\FormateurController;
use App\Http\Controllers\EmploiDuTempsController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\AbsenceFormateurController;
use App\Http\Controllers\AbsenceGroupeController;
use App\Http\Controllers\AvancementController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\RapportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API routes for data fetching and CRUD operations
Route::name('api.')->group(function () {
    Route::apiResources([
        'modules' => ModuleController::class,
        'centres' => CentreController::class,
        'salles' => SalleController::class,
        'groupes' => GroupeController::class,
        'formateurs' => FormateurController::class,
        'emploi-du-temps' => EmploiDuTempsController::class,
        'stages' => StageController::class,
        'absence-formateurs' => AbsenceFormateurController::class,
        'absence-groupes' => AbsenceGroupeController::class,
        'avancements' => AvancementController::class,
        'utilisateurs' => UtilisateurController::class,
    ]);
});

// Custom routes
Route::get('/modules/by-groupe/{groupeId}', [ModuleController::class, 'getByGroupe']);
Route::get('/groupes/filieres', [GroupeController::class, 'getFilieres']);
Route::get('/salles/available', [SalleController::class, 'getAvailableSalles']);

// Timetable storage for group and formateur views (shared underlying table)
Route::get('/timetable-groupe/{date}', [TimetableController::class, 'showByDate']);
Route::post('/timetable-groupe', [TimetableController::class, 'store']);

// Unified emploi du temps API

// Formateur timetable
Route::get('/timetable-formateur/{date}', [EmploiDuTempsController::class, 'getForFormateur']);
Route::post('/timetable-formateur', [EmploiDuTempsController::class, 'saveForFormateur']);
Route::post('/timetable-formateurs', [EmploiDuTempsController::class, 'saveForFormateurs']);

// Centre/Group timetable
Route::get('/timetable-centre/{date}', [EmploiDuTempsController::class, 'getForCentre']);
Route::post('/timetable-centre', [EmploiDuTempsController::class, 'saveTimetableForCentre']);

// Emploi du temps Groupe (explicit endpoints for centre + week)
Route::get('/modules-for-groupe/{groupeId}', [EmploiDuTempsController::class, 'modulesForGroupe']);
Route::get('/formateurs-for-groupe/{groupeId}', [EmploiDuTempsController::class, 'getFormateursForGroupe']);
Route::get('/modules-for-groupe-formateur', [EmploiDuTempsController::class, 'getModulesForGroupeFormateur']);
Route::get('/groupes-for-formateur/{formateurId}', [EmploiDuTempsController::class, 'getGroupesForFormateur']);
Route::get('/modules-for-formateur-groupe', [EmploiDuTempsController::class, 'getModulesForFormateurGroupe']);
Route::get('/emploi-groupe/load', [EmploiDuTempsController::class, 'loadGroupeTimetable']);
Route::post('/emploi-groupe/save', [EmploiDuTempsController::class, 'saveGroupeTimetable']);

// Delete
Route::delete('/timetable/{id}', [EmploiDuTempsController::class, 'destroy']);
Route::put('/timetable-groupe/{timetable}', [TimetableController::class, 'update']);


// Additional routes
Route::get('/salles/by-centre/{centreId}', [SalleController::class, 'byCentre']);
Route::get('/emploi-du-temps/by-group/{groupName}', [EmploiDuTempsController::class, 'byGroup']);
Route::get('/emploi-du-temps/by-formateur/{formateurId}', [EmploiDuTempsController::class, 'byFormateur']);
Route::get('/timetable-export', [EmploiDuTempsController::class, 'exportExcel']);

// Reports
Route::get('/rapports', [RapportController::class, 'index']);
Route::get('/rapports/analytics', [RapportController::class, 'analytics']);
Route::post('/rapports/generate-pdf', [RapportController::class, 'generatePdf']);
