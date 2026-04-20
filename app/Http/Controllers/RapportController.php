<?php

namespace App\Http\Controllers;

use App\Models\EmploiDuTemps;
use App\Models\Formateur;
use App\Models\Groupe;
use App\Models\Module;
use App\Models\Salle;
use App\Models\AbsenceFormateur;
use App\Models\AbsenceGroupe;
use App\Services\RapportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    public function index()
    {
        $recent = EmploiDuTemps::query()
            ->selectRaw('DATE(date) as created_at')
            ->selectRaw('COUNT(*) as sessions_count')
            ->groupBy('created_at')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get()
            ->values()
            ->map(function ($row, $index) {
                return [
                    'id' => $index + 1,
                    'name' => 'Rapport emploi du temps - ' . $row->created_at,
                    'type' => 'Emploi du Temps',
                    'created_at' => $row->created_at,
                    'sessions_count' => (int) $row->sessions_count,
                ];
            });

        return response()->json([
            'data' => $recent,
            'stats' => [
                'total_seances' => EmploiDuTemps::count(),
                'total_formateurs' => Formateur::count(),
                'total_groupes' => Groupe::count(),
                'total_salles' => Salle::count(),
                'total_modules' => Module::count(),
            ],
        ]);
    }

    public function analytics()
    {
        $groupes = Groupe::query()
            ->with(['emplois.module', 'emplois.formateur', 'modules'])
            ->orderBy('nomGroupe')
            ->get();

        $formateurs = Formateur::query()
            ->withCount('emplois')
            ->with(['emplois' => fn ($q) => $q->orderBy('date'), 'modules'])
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        $groupStats = $groupes->map(function (Groupe $groupe) {
            $totalSeances = (int) $groupe->emplois->count();
            $modulesActifs = (int) $groupe->emplois->pluck('module_id')->filter()->unique()->count();
            $avancement = RapportService::avancement($groupe);
            $tauxAbsence = RapportService::tauxAbsence($groupe);
            $totalHeures = (float) $groupe->emplois->sum('duree_heures');

            return [
                'id' => $groupe->id,
                'nomGroupe' => $groupe->nomGroupe,
                'advancement' => $avancement,
                'avancement_presentiel' => $groupe->avancement_presentiel,
                'avancement_distanciel' => $groupe->avancement_distanciel,
                'taux_absence' => $tauxAbsence,
                'modules_actifs' => $modulesActifs,
                'total_seances' => $totalSeances,
                'total_heures' => $totalHeures,
            ];
        })->values();

        $workload = $formateurs->map(function (Formateur $formateur) {
            return [
                'id' => $formateur->id,
                'nom' => $formateur->nom,
                'prenom' => $formateur->prenom,
                'emplois_count' => (int) $formateur->emplois_count,
                'heures' => (int) $formateur->modules->sum('volumeHoraire'),
                'charge_travail' => RapportService::chargeFormateur($formateur),
                'avancement' => $formateur->avancement ?? 0,
            ];
        })->values();

        return response()->json([
            'groupes' => $groupes,
            'modules' => Module::query()->orderBy('codeModule')->get(),
            'formateurs' => $formateurs,
            'groupStats' => $groupStats,
            'workload' => $workload,
            'absences' => [
                'formateurs' => \App\Models\AbsenceFormateur::query()
                    ->select('formateur_id', 'dateAbsence')
                    ->with('formateur:id,nom,prenom')
                    ->groupBy('formateur_id', 'dateAbsence')
                    ->orderByDesc('dateAbsence')
                    ->get(),
            ],
            'metrics' => [
                'total_groupes' => $groupes->count(),
                'total_formateurs' => Formateur::count(),
                'total_salles' => Salle::count(),
                'total_seances' => EmploiDuTemps::count(),
                'modules_actifs' => EmploiDuTemps::query()->whereNotNull('module_id')->distinct('module_id')->count('module_id'),
            ],
        ]);
    }

    public function modulesProgress()
    {
        $modules = Module::query()
            ->orderBy('codeModule')
            ->get(['id', 'codeModule', 'nomModule', 'volumeHoraire'])
            ->map(function (Module $m) {
                return [
                    'id' => $m->id,
                    'code' => $m->codeModule,
                    'nom' => $m->nomModule,
                    'heures' => (int) ($m->volumeHoraire ?? 0),
                    'advancement' => (int) round((float) ($m->advancement ?? 0)),
                ];
            })
            ->values();

        return response()->json($modules);
    }

    public function groupesProgress()
    {
        $groupes = Groupe::query()
            ->with('centre')
            ->select('id', 'nomGroupe', 'filiere', 'niveau', 'effectif', 'centre_id')
            ->get()
            ->map(function (Groupe $g) {
                return [
                    'id' => $g->id,
                    'nom' => $g->nomGroupe,
                    'filiere' => $g->filiere,
                    'niveau' => $g->niveau,
                    'effectif' => (int) ($g->effectif ?? 0),
                    'centre' => $g->centre?->nomCentre ?? 'Non assigné',
                    'advancement' => (int) round((float) ($g->advancement ?? 0)),
                ];
            })
            ->values();

        return response()->json($groupes);
    }

    public function formateursWorkload()
    {
        $formateurs = Formateur::query()
            ->with(['emplois.module'])
            ->get(['id', 'nom', 'prenom', 'specialite'])
            ->map(function (Formateur $f) {
                // Get unique module IDs this trainer teaches through emplois
                $moduleIds = $f->emplois()->pluck('module_id')->filter()->unique();
                
                // Sum volumeHoraire from those modules
                $heures = (float) Module::whereIn('id', $moduleIds)->sum('volumeHoraire') ?? 0;

                return [
                    'id' => $f->id,
                    'nom' => trim(($f->nom ?? '') . ' ' . ($f->prenom ?? '')),
                    'specialite' => $f->specialite,
                    'progress' => (int) round((float) ($f->progress ?? 0)),
                    'slots' => 0,
                    'heures' => $heures,
                ];
            })
            ->values();

        return response()->json($formateurs);
    }

    public function generatePdf(Request $request)
    {
        $validated = $request->validate([
            'reportId' => 'nullable',
            'reportName' => 'nullable|string|max:255',
            'reportType' => 'nullable|string|max:255',
            'dateFrom' => 'nullable|string|max:100',
            'dateTo' => 'nullable|string|max:100',
            'sections' => 'nullable|array',
            'metrics' => 'required|array',
            'chartImages' => 'required|array',
            'chartImages.mainChart' => 'required|string',
            'chartImages.moduleChart' => 'required|string',
            'chartImages.absenceChart' => 'required|string',
            'chartImages.workloadChart' => 'required|string',
            'groupes' => 'nullable|array',
            'modules' => 'nullable|array',
            'formateurs' => 'nullable|array',
            'absences' => 'nullable|array',
            'recentReports' => 'nullable|array',
        ]);

        $sections = $validated['sections'] ?? [
            'avancement' => true,
            'absence' => true,
            'emploi' => true
        ];

        // Parse date range if provided
        $dateFrom = $validated['dateFrom'] ? \Carbon\Carbon::createFromFormat('Y-m-d', $validated['dateFrom'])->startOfDay() : null;
        $dateTo = $validated['dateTo'] ? \Carbon\Carbon::createFromFormat('Y-m-d', $validated['dateTo'])->endOfDay() : null;

        // Filter data by date range using database queries
        $groupes = $this->filterGroupesByDateRange($validated['groupes'] ?? [], $dateFrom, $dateTo);
        $modules = $this->filterModulesByDateRange($validated['modules'] ?? [], $dateFrom, $dateTo);
        $formateurs = $this->filterFormateursByDateRange($validated['formateurs'] ?? [], $dateFrom, $dateTo);
        $absences = $this->filterAbsencesByDateRange($validated['absences'] ?? [], $dateFrom, $dateTo);
        
        // Enrich absences with complete data from database including motif
        $absences = $this->enrichAbsencesWithMotif($absences, $dateFrom, $dateTo);

        $data = [
            'reportId' => $validated['reportId'] ?? null,
            'reportName' => $validated['reportName'] ?? 'Rapport Analytique',
            'reportType' => $validated['reportType'] ?? 'Complet',
            'date' => now()->format('d/m/Y H:i'),
            'dateFrom' => $validated['dateFrom'] ?? null,
            'dateTo' => $validated['dateTo'] ?? null,
            'sections' => $sections,
            'metrics' => $validated['metrics'],
            'chartImages' => $validated['chartImages'],
            'groupes' => $groupes,
            'modules' => $modules,
            'formateurs' => $formateurs,
            'absences' => $absences,
            'recentReports' => $validated['recentReports'] ?? [],
        ];

        $groupesPayload = $data['groupes'] ?? [];
        if ($groupesPayload !== []) {
            $groupeIds = collect($groupesPayload)->pluck('id')->filter()->unique()->values()->all();
            if ($groupeIds !== []) {
                // Total requis = somme des volumeHoraire des modules liés au groupe (même logique que formateurs)
                $heuresParGroupe = DB::table('module_groupe')
                    ->join('modules', 'modules.id', '=', 'module_groupe.module_id')
                    ->whereIn('module_groupe.groupe_id', $groupeIds)
                    ->groupBy('module_groupe.groupe_id')
                    ->selectRaw('module_groupe.groupe_id as groupe_id, COALESCE(SUM(modules.volumeHoraire), 0) as total_heures')
                    ->pluck('total_heures', 'groupe_id');
                $data['groupes'] = collect($groupesPayload)
                    ->map(function ($g) use ($heuresParGroupe) {
                        $id = $g['id'] ?? null;
                        $g['total_heures'] = $id !== null
                            ? (float) ($heuresParGroupe[$id] ?? 0)
                            : 0.0;

                        return $g;
                    })
                    ->all();
            }
        }

        $formateursPayload = $data['formateurs'] ?? [];
        if ($formateursPayload !== []) {
            $formateurIds = collect($formateursPayload)->pluck('id')->filter()->unique()->values()->all();
            if ($formateurIds !== []) {
                $seancesParFormateur = EmploiDuTemps::query()
                    ->whereIn('formateur_id', $formateurIds)
                    ->selectRaw('formateur_id, COUNT(*) as cnt')
                    ->groupBy('formateur_id')
                    ->pluck('cnt', 'formateur_id');

                $formateursDb = Formateur::query()
                    ->whereIn('id', $formateurIds)
                    ->with('modules')
                    ->get()
                    ->keyBy('id');

                $data['formateurs'] = collect($formateursPayload)
                    ->map(function ($f) use ($seancesParFormateur, $formateursDb) {
                        $id = $f['id'] ?? null;
                        if ($id !== null) {
                            $f['emplois_count'] = (int) ($seancesParFormateur[$id] ?? 0);
                            $row = $formateursDb->get($id);
                            if ($row) {
                                $f['heures'] = (int) $row->modules->sum('volumeHoraire');
                            }
                        }

                        return $f;
                    })
                    ->all();
            }
        }

        $pdf = Pdf::loadView('pdf.rapport-professionnel', $data)
            ->setPaper('a4', 'portrait');

        $safeName = Str::slug($data['reportName'] ?: 'rapport-analytique');
        $filename = ($safeName !== '' ? $safeName : 'rapport-analytique') . '-' . now()->format('Y-m-d-His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Filter groupes data by date range
     */
    private function filterGroupesByDateRange(array $groupes, $dateFrom, $dateTo)
    {
        if (!$dateFrom && !$dateTo) {
            return $groupes;
        }

        return collect($groupes)->filter(function ($groupe) use ($dateFrom, $dateTo) {
            $groupeId = $groupe['id'] ?? null;
            if (!$groupeId) return true;

            $emploisCount = EmploiDuTemps::query()
                ->where('groupe_id', $groupeId)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
                ->count();

            return $emploisCount > 0;
        })->values()->all();
    }

    /**
     * Filter modules data by date range
     */
    private function filterModulesByDateRange(array $modules, $dateFrom, $dateTo)
    {
        if (!$dateFrom && !$dateTo) {
            return $modules;
        }

        return collect($modules)->filter(function ($module) use ($dateFrom, $dateTo) {
            $moduleId = $module['id'] ?? null;
            if (!$moduleId) return true;

            $emploisCount = EmploiDuTemps::query()
                ->where('module_id', $moduleId)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
                ->count();

            return $emploisCount > 0;
        })->values()->all();
    }

    /**
     * Filter formateurs data by date range
     */
    private function filterFormateursByDateRange(array $formateurs, $dateFrom, $dateTo)
    {
        if (!$dateFrom && !$dateTo) {
            return $formateurs;
        }

        return collect($formateurs)->filter(function ($formateur) use ($dateFrom, $dateTo) {
            $formateurId = $formateur['id'] ?? null;
            if (!$formateurId) return true;

            $emploisCount = EmploiDuTemps::query()
                ->where('formateur_id', $formateurId)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
                ->count();

            return $emploisCount > 0;
        })->values()->all();
    }

    /**
     * Filter absences data by date range
     */
    private function filterAbsencesByDateRange(array $absences, $dateFrom, $dateTo)
    {
        if (!$dateFrom && !$dateTo) {
            return $absences;
        }

        return collect($absences)->filter(function ($absence) use ($dateFrom, $dateTo) {
            $absenceDate = $absence['date'] ?? $absence['dateAbsence'] ?? null;
            if (!$absenceDate) return true;

            try {
                $date = \Carbon\Carbon::parse($absenceDate);
                $inRange = true;

                if ($dateFrom) {
                    $inRange = $inRange && $date->gte($dateFrom);
                }
                if ($dateTo) {
                    $inRange = $inRange && $date->lte($dateTo);
                }

                return $inRange;
            } catch (\Exception $e) {
                return true; // Keep absence if date parsing fails
            }
        })->values()->all();
    }

    /**
     * Enrich absences data with motif field from database
     */
    private function enrichAbsencesWithMotif($absences, $dateFrom, $dateTo)
    {
        if (empty($absences)) {
            return [];
        }

        $enriched = [];
        
        foreach ($absences as $absence) {
            try {
                // Handle both 'date' and 'dateAbsence' field names
                $absenceDate = $absence['dateAbsence'] ?? $absence['date'] ?? null;
                
                if (!$absenceDate) {
                    $enriched[] = $absence;
                    continue;
                }
                
                // Check if it's a formateur absence
                if (isset($absence['formateur_id'])) {
                    try {
                        $dbAbsence = AbsenceFormateur::where('formateur_id', $absence['formateur_id'])
                            ->where('dateAbsence', $absenceDate)
                            ->with('formateur')
                            ->first();
                        
                        if ($dbAbsence) {
                            $absence['motif'] = $dbAbsence->motif ?? 'Non spécifié';
                            $absence['dateAbsence'] = $dbAbsence->dateAbsence;
                            
                            // Safely set formateur data with null checks
                            if ($dbAbsence->formateur && !is_null($dbAbsence->formateur)) {
                                $absence['formateur'] = [
                                    'nom' => $dbAbsence->formateur->nom ?? $dbAbsence->formateur->name ?? 'Unknown',
                                    'prenom' => $dbAbsence->formateur->prenom ?? ''
                                ];
                            } else {
                                $absence['formateur'] = ['nom' => 'Unknown', 'prenom' => ''];
                            }
                        } else {
                            $absence['formateur'] = ['nom' => 'Unknown', 'prenom' => ''];
                        }
                    } catch (\Exception $e) {
                        // Fallback if relationship fails
                        $absence['formateur'] = ['nom' => 'Unknown', 'prenom' => ''];
                        $absence['motif'] = 'Non spécifié';
                    }
                }
                // Check if it's a groupe absence
                elseif (isset($absence['groupe_id'])) {
                    try {
                        $dbAbsence = AbsenceGroupe::where('groupe_id', $absence['groupe_id'])
                            ->where('dateAbsence', $absenceDate)
                            ->with('groupe')
                            ->first();
                        
                        if ($dbAbsence) {
                            $absence['motif'] = $dbAbsence->motif ?? 'Non spécifié';
                            $absence['dateAbsence'] = $dbAbsence->dateAbsence;
                            
                            // Safely set groupe data with null checks
                            if ($dbAbsence->groupe && !is_null($dbAbsence->groupe)) {
                                $absence['groupe'] = [
                                    'nomGroupe' => $dbAbsence->groupe->nomGroupe ?? $dbAbsence->groupe->name ?? 'Unknown'
                                ];
                            } else {
                                $absence['groupe'] = ['nomGroupe' => 'Unknown'];
                            }
                        } else {
                            $absence['groupe'] = ['nomGroupe' => 'Unknown'];
                        }
                    } catch (\Exception $e) {
                        // Fallback if relationship fails
                        $absence['groupe'] = ['nomGroupe' => 'Unknown'];
                        $absence['motif'] = 'Non spécifié';
                    }
                }
                
                $enriched[] = $absence;
            } catch (\Exception $e) {
                // If anything goes wrong with this absence, keep the original but ensure minimal required fields
                $enriched[] = array_merge(['motif' => 'Non spécifié'], $absence);
            }
        }
        
        return $enriched;
    }
}
