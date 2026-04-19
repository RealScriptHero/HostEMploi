<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormateurController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ProfileController;
use App\Models\Group;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/check-users', function () {
    return response()->json(User::all());
});

/*
use Illuminate\Support\Facades\Hash;

Route::get('/create-admin', function () {
    return User::create([
        'email' => 'zharimaha@gmail.com',
        'password' => Hash::make('ofppt1122'),
    ]);
});
*/

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Profile routes (used by navigation layout)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/emploi', function () {
        return view('emploi.global');
    })->name('emploi.alias');

    Route::get('/emploi-global', function () {
        return view('emploi.global');
    })->name('emploi.global');

    Route::get('/emploi-formateur', [FormateurController::class, 'emploi'])->name('emploi.formateur');

    Route::get('/modules', function () {
        return view('modules.index');
    })->name('modules.index');

    Route::get('/centres', function () {
        return view('centres.index');
    })->name('centres.index');

    Route::get('/salles', function () {
        return view('salles.index');
    })->name('salles.index');

    Route::get('/groupes', function () {
        return view('groupes.index');
    })->name('groupes.index');

    Route::get('/formateurs', function () {
        return view('formateurs.index');
    })->name('formateurs.index');

    Route::get('/emploi-du-temps', function () {
        return view('emploi-du-temps.index');
    })->name('emploi-du-temps.index');

    Route::get('/stages', function () {
        return view('stage.index');
    })->name('stages.index');

    Route::get('/absences/formateurs', function () {
        return view('absences.formateurs');
    })->name('absences.formateurs');

    Route::get('/absences/groupes', function () {
        return view('absences.groupes');
    })->name('absences.groupes');

    Route::get('/avancements', function () {
        return view('avancements.index');
    })->name('avancements.index');

    // Legacy routes
    Route::get('/stage', function () {
        return view('stage.index');
    })->name('stage.index');

    Route::get('/absence', function () {
        return view('absences.formateurs');
    })->name('absence.index');

    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');

    // Temporary test utility route. Remove after QA.
    Route::delete('/admin/reset-future-emplois', function () {
        \App\Models\EmploiDuTemps::query()
            ->whereDate('date', '>', now()->toDateString())
            ->delete();

        return back()->with('success', 'Données futures supprimées.');
    })->name('admin.reset-future-emplois');

    Route::get('/avancement', function () {
        return view('avancement.index');
    })->name('avancement.index');

    // Parametres / Settings routes
    Route::get('/parametres', [SettingsController::class, 'index'])->name('parametres.index');
    Route::put('/parametres/general', [SettingsController::class, 'updateGeneral'])->name('parametres.general');
    Route::put('/parametres/account', [SettingsController::class, 'updateAccount'])->name('parametres.password');
    Route::put('/parametres/timetable', [SettingsController::class, 'updateTimetable'])->name('settings.timetable.update');
    Route::put('/parametres/reports', [SettingsController::class, 'updateReports'])->name('settings.reports.update');
    Route::put('/parametres/appearance', [SettingsController::class, 'updateAppearance'])->name('parametres.appearance');
    Route::post('/parametres/theme', [SettingsController::class, 'updateTheme'])->name('parametres.theme');
    Route::post('/parametres/language', [SettingsController::class, 'updateLanguage'])->name('parametres.language');
    Route::post('/parametres/reset-year', [SettingsController::class, 'resetAcademicYear'])->name('settings.academic_year.reset');
});

require __DIR__.'/auth.php';
