<?php

namespace App\Http\Controllers;

use App\Models\EmploiDuTemps;
use App\Models\Formateur;
use App\Models\Groupe;
use App\Models\Module;
use App\Models\Salle;
use App\Models\Centre;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class EmploiDuTempsController extends Controller
{
    /**
     * Helper to determine session type from salle_id
     */
    private function getTypeSession($salleId): string
    {
        if ($salleId === 'teams') return 'distance';
        if ($salleId === 'efm') return 'efm';
        return 'presentiel';
    }

    /**
     * Get timetable for a formateur (trainer view)
     * GET /api/timetable-formateur/{date}?formateur_id={id}
     */
    public function getForFormateur(Request $request, $date)
    {
        $formateurId = $request->get('formateur_id');
        $cacheKey = 'timetable_formateur_' . $formateurId . '_' . $date;

        $timetable = Cache::remember($cacheKey, 3600, function() use ($formateurId, $date) {
            return EmploiDuTemps::select('id', 'groupe_id', 'formateur_id', 'module_id', 'salle_id', 'jour', 'creneau', 'date', 'type_session')
                ->with(['groupe.centre', 'module', 'salle.centre'])
                ->where('formateur_id', $formateurId)
                ->whereDate('date', $date)
                ->get();
        });
        
        return response()->json($timetable);
    }
    
    /**
     * Get timetable for centre (group view)
     * GET /api/timetable-centre/{date}?centre_id={id}
     */
    public function getForCentre(Request $request, $date)
    {
        $centreId = $request->get('centre_id');
        $cacheKey = 'timetable_centre_' . ($centreId ?? 'all') . '_' . $date;

        $timetable = Cache::remember($cacheKey, 3600, function() use ($centreId, $date) {
            return EmploiDuTemps::select('id', 'groupe_id', 'formateur_id', 'module_id', 'salle_id', 'jour', 'creneau', 'date', 'type_session')
                ->with(['groupe', 'formateur', 'module', 'salle'])
                ->when($centreId, function ($q) use ($centreId) {
                    $q->whereHas('groupe', function ($g) use ($centreId) {
                        $g->where('centre_id', $centreId);
                    });
                })
                ->whereDate('date', $date)
                ->get()
                ->map(function ($emploi) {
                    if ($emploi->groupe && $emploi->groupe->centre) {
                        $emploi->groupe->display_name = strtoupper($emploi->groupe->centre->shortName) . ' - ' . $emploi->groupe->nomGroupe;
                    }
                    return $emploi;
                });
        });

        return response()->json($timetable);
    }

    /**
     * Get modules assigned to a specific group
     * GET /api/modules-for-groupe/{groupeId}
     */
    public function modulesForGroupe($groupeId)
    {
        $cacheKey = 'modules_groupe_' . $groupeId;
        $modules = Cache::remember($cacheKey, 43200, function() use ($groupeId) {
            return \App\Models\Module::whereHas('groupes', function ($q) use ($groupeId) {
                $q->where('groupes.id', $groupeId);
            })->orderBy('codeModule')->get(['id', 'codeModule', 'nomModule', 'volumeHoraire', 'advancement']);
        });
        return response()->json(['data' => $modules]);
    }

    /**
     * Emploi Groupe: formateurs who teach at least one module assigned to this group.
     * GET /api/formateurs-for-groupe/{groupeId}
     */
    public function getFormateursForGroupe($groupeId)
    {
        $cacheKey = 'formateurs_for_groupe_' . $groupeId;
        return response()->json(['data' => Cache::remember($cacheKey, 43200, function() use ($groupeId) {
            $groupe = Groupe::with('modules')->findOrFail($groupeId);
            $groupModuleIds = $groupe->modules->pluck('id');
            if ($groupModuleIds->isEmpty()) return [];
            return Formateur::whereHas('modules', function ($query) use ($groupModuleIds) {
                $query->whereIn('modules.id', $groupModuleIds);
            })->distinct()->orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'specialite'])->toArray();
        })]);
    }

    /**
     * Emploi Groupe: modules for formateur cell (intersection, then groupe modules, then formateur modules).
     * GET /api/modules-for-groupe-formateur?groupe_id=&formateur_id=
     */
    public function getModulesForGroupeFormateur(Request $request)
    {
        $groupeId = $request->input('groupe_id');
        $formateurId = $request->input('formateur_id');

        if (! $groupeId || ! $formateurId) {
            return response()->json(['data' => []]);
        }

        $byCode = fn ($q) => $q->orderBy('codeModule')->get(['id', 'codeModule', 'nomModule']);

        $intersection = Module::query()
            ->whereHas('groupes', fn ($query) => $query->where('groupes.id', $groupeId))
            ->whereHas('formateurs', fn ($query) => $query->where('formateurs.id', $formateurId));

        $modules = $byCode($intersection);
        if ($modules->isNotEmpty()) {
            return response()->json(['data' => $modules]);
        }

        $groupOnly = Module::query()
            ->whereHas('groupes', fn ($query) => $query->where('groupes.id', $groupeId));

        $modules = $byCode($groupOnly);
        if ($modules->isNotEmpty()) {
            return response()->json(['data' => $modules]);
        }

        $formateurOnly = Module::query()
            ->whereHas('formateurs', fn ($query) => $query->where('formateurs.id', $formateurId));

        return response()->json(['data' => $byCode($formateurOnly)]);
    }

    /**
     * Emploi Formateur: groupes that have at least one module this formateur teaches.
     * GET /api/groupes-for-formateur/{formateurId}
     */
    public function getGroupesForFormateur($formateurId)
    {
        $cacheKey = 'groupes_for_formateur_' . $formateurId;
        return response()->json(['data' => Cache::remember($cacheKey, 43200, function() use ($formateurId) {
            Formateur::findOrFail($formateurId);
            return Groupe::query()->where(function ($query) use ($formateurId) {
                $query->whereHas('modules', function ($moduleQuery) use ($formateurId) {
                    $moduleQuery->whereHas('formateurs', function ($formateurQuery) use ($formateurId) {
                        $formateurQuery->where('formateurs.id', $formateurId);
                    });
                })->orWhereHas('emplois', function ($emploiQuery) use ($formateurId) {
                    $emploiQuery->where('formateur_id', $formateurId);
                });
            })->orderBy('nomGroupe')->get(['id', 'nomGroupe', 'filiere', 'centre_id'])->toArray();
        })]);
    }

    /**
     * Emploi Formateur: modules for the module dropdown when a groupe is selected.
     * Prefer intersection (formateur ∩ groupe); if empty, use groupe modules, then formateur modules.
     * GET /api/modules-for-formateur-groupe?formateur_id=&groupe_id=
     */
    public function getModulesForFormateurGroupe(Request $request)
    {
        $formateurId = $request->input('formateur_id');
        $groupeId = $request->input('groupe_id');

        if (! $formateurId || ! $groupeId) {
            return response()->json(['data' => []]);
        }

        $cacheKey = 'modules_formateur_groupe_'.$formateurId.'_'.$groupeId;
        return response()->json(['data' => Cache::remember($cacheKey, 43200, function() use ($formateurId, $groupeId) {
            if (!Groupe::where('id', $groupeId)->exists()) {
                return [];
            }

            $byCode = fn ($q) => $q->orderBy('codeModule')->get(['id', 'codeModule', 'nomModule'])->toArray();

            $intersection = Module::query()
                ->whereHas('formateurs', fn ($query) => $query->where('formateurs.id', $formateurId))
                ->whereHas('groupes', fn ($query) => $query->where('groupes.id', $groupeId));

            $modules = $byCode($intersection);
            if (!empty($modules)) {
                return $modules;
            }

            $groupOnly = Module::query()
                ->whereHas('groupes', fn ($query) => $query->where('groupes.id', $groupeId));

            $modules = $byCode($groupOnly);
            if (!empty($modules)) {
                return $modules;
            }

            $formateurOnly = Module::query()
                ->whereHas('formateurs', fn ($query) => $query->where('formateurs.id', $formateurId));

            return $byCode($formateurOnly);
        })]);

    }

    /**
     * GET /api/salles/disponibles?date=YYYY-MM-DD&jour=Lundi&seance=S1[&exclude_emploi_id=1]
     */
    public function getSallesDisponibles(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'jour' => ['required', 'string', 'in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi'],
            'seance' => ['required', 'string', 'in:S1,S2,S3,S4'],
            'exclude_emploi_id' => ['nullable', 'integer'],
        ]);

        $cacheKey = 'salles_disponibles_' . $validated['date'] . '_' . $validated['jour'] . '_' . $validated['seance'] . '_' . ($validated['exclude_emploi_id'] ?? 'none');

        $salles = Cache::remember($cacheKey, 1800, function() use ($validated) {
            $occupied = EmploiDuTemps::query()
                ->whereDate('date', $validated['date'])
                ->where('jour', $validated['jour'])
                ->where('creneau', $validated['seance'])
                ->when(
                    !empty($validated['exclude_emploi_id']),
                    fn ($q) => $q->where('id', '!=', $validated['exclude_emploi_id'])
                )
                ->whereNotNull('salle_id')
                ->pluck('salle_id')
                ->toArray();

            return Salle::query()
                ->when(!empty($occupied), fn ($q) => $q->whereNotIn('id', $occupied))
                ->orderBy('nomSalle')
                ->get();
        });

        return response()->json($salles);
    }

    /**
     * Save timetable for all formateurs (formateur view)
     * POST /api/timetable-formateurs
     */
    public function saveForFormateurs(Request $request)
    {
        $date = Carbon::parse($request->input('date'))->format('Y-m-d');
        $timetable = $request->input('timetable', []); // trainerId -> type -> dayIndex -> slotIndex -> value
        $entries = $request->input('entries', []); // optional alternate format (array of entries)
        $entries = array_map(function ($entry) use ($date) {
            if (!empty($entry['date'])) {
                $entry['date'] = Carbon::parse($entry['date'])->format('Y-m-d');
            } else {
                $entry['date'] = $date;
            }
            return $entry;
        }, $entries);

        if (empty($date)) {
            return response()->json(['error' => 'Date is required'], 422);
        }

        if (!is_array($timetable) && !is_array($entries)) {
            return response()->json(['error' => 'No timetable data provided'], 422);
        }

        $currentYear = Setting::get('academic_year');

        DB::beginTransaction();
        try {
            // Determine which formateurs are being updated
            $formateurIds = [];

            if (is_array($timetable)) {
                $formateurIds = array_map('intval', array_filter(array_keys($timetable)));
            }

            if (is_array($entries)) {
                foreach ($entries as $entry) {
                    if (!empty($entry['formateur_id'])) {
                        $formateurIds[] = intval($entry['formateur_id']);
                    }
                }
            }

            if (empty($formateurIds) && is_array($request->input('formateur_ids'))) {
                $formateurIds = array_map('intval', array_filter($request->input('formateur_ids')));
            }

            $formateurIds = array_values(array_unique(array_filter($formateurIds)));

            // Delete existing entries for these formateurs this week
            if (!empty($formateurIds)) {
                $query = EmploiDuTemps::whereIn('formateur_id', $formateurIds)
                    ->whereDate('date', $date);

                if ($currentYear) {
                    $query->where('academic_year', $currentYear);
                }

                $query->delete();
            }

            $toInsert = [];

            // If timetable format is provided, convert it to flat entries.
            // support both nested style (day_index => [slot => value]) and compact day-slot keys ("d-s" => value).
            if (is_array($timetable)) {
                foreach ($timetable as $trainerId => $types) {
                    foreach (['group', 'module', 'salle'] as $type) {
                        if (empty($types[$type]) || !is_array($types[$type])) continue;

                        foreach ($types[$type] as $dayKey => $slotData) {
                            if (is_array($slotData)) {
                                // old nested format: dayIndex => [slotIndex => value]
                                foreach ($slotData as $slotIndex => $value) {
                                    if (!$value) continue;
                                    $day = $this->indexToDayName($dayKey);
                                    $creneau = $this->indexToCreneau($slotIndex);
                                    if (!$day || !$creneau) continue;

                                    $entryKey = "{$trainerId}-{$day}-{$creneau}";
                                    if (!isset($toInsert[$entryKey])) {
                                        $toInsert[$entryKey] = [
                                            'formateur_id' => $trainerId,
                                            'groupe_id' => null,
                                            'module_id' => null,
                                            'salle_id' => null,
                                            'jour' => $day,
                                            'creneau' => $creneau,
                                            'date' => $date,
                                        ];
                                    }
                                    if ($type === 'group') {
                                        $toInsert[$entryKey]['groupe_id'] = $value;
                                    } elseif ($type === 'module') {
                                        $toInsert[$entryKey]['module_id'] = $value;
                                    } elseif ($type === 'salle') {
                                        $toInsert[$entryKey]['salle_id'] = $value;
                                        $toInsert[$entryKey]['type_session'] = $this->getTypeSession($value);
                                    }
                                }
                            } elseif (is_string($dayKey) && strpos($dayKey, '-') !== false) {
                                // compact format: "day-slot" keys
                                [$dayIndex, $slotIndex] = explode('-', $dayKey);
                                $day = $this->indexToDayName($dayIndex);
                                $creneau = $this->indexToCreneau($slotIndex);
                                $value = $slotData;
                                if (!$day || !$creneau || !$value) continue;

                                $entryKey = "{$trainerId}-{$day}-{$creneau}";
                                if (!isset($toInsert[$entryKey])) {
                                    $toInsert[$entryKey] = [
                                        'formateur_id' => $trainerId,
                                        'groupe_id' => null,
                                        'module_id' => null,
                                        'salle_id' => null,
                                        'jour' => $day,
                                        'creneau' => $creneau,
                                        'date' => $date,
                                    ];
                                }
                                if ($type === 'group') {
                                    $toInsert[$entryKey]['groupe_id'] = $value;
                                } elseif ($type === 'module') {
                                    $toInsert[$entryKey]['module_id'] = $value;
                                } elseif ($type === 'salle') {
                                    $toInsert[$entryKey]['salle_id'] = $value;
                                    $toInsert[$entryKey]['type_session'] = $this->getTypeSession($value);
                                }
                            }
                        }
                    }
                }
            }

            // If entries array format is provided, use it directly (dedupe by formateur+jour+creneau+date)
            if (is_array($entries)) {
                foreach ($entries as $entry) {
                    if (empty($entry['formateur_id'])) continue;
                    if (empty($entry['jour']) || empty($entry['creneau']) || empty($entry['date'])) continue;

                    $entryKey = sprintf('%s-%s-%s-%s', $entry['formateur_id'], $entry['jour'], $entry['creneau'], $entry['date']);

                    // Handle Teams selection
                    $salleId = $entry['salle_id'] ?? null;
                    $incomingType = $entry['type_session'] ?? null;
                    $typeSession = in_array($incomingType, ['distance', 'presentiel', 'efm'], true)
                        ? $incomingType
                        : $this->getTypeSession($salleId);

                    if ($typeSession === 'distance') {
                        $salleId = null;
                    }

                    if (!isset($toInsert[$entryKey])) {
                        $toInsert[$entryKey] = [
                            'formateur_id' => $entry['formateur_id'],
                            'groupe_id' => $entry['groupe_id'] ?? null,
                            'module_id' => $entry['module_id'] ?? null,
                            'salle_id' => $salleId,
                            'type_session' => $typeSession,
                            'jour' => $entry['jour'],
                            'creneau' => $entry['creneau'],
                            'date' => $entry['date'],
                        ];
                    } else {
                        if (!empty($entry['groupe_id'])) {
                            $toInsert[$entryKey]['groupe_id'] = $entry['groupe_id'];
                        }
                        if (!empty($entry['module_id'])) {
                            $toInsert[$entryKey]['module_id'] = $entry['module_id'];
                        }
                        if ($salleId !== null) {
                            $toInsert[$entryKey]['salle_id'] = $salleId;
                        }
                        $toInsert[$entryKey]['type_session'] = $typeSession;
                    }
                }
            }

            // One DB row per (groupe_id, date, jour, creneau) — keep a single entry (last wins)
            $byGroupeSlot = [];
            $withoutGroupeSlot = [];
            foreach ($toInsert as $entry) {
                if (! empty($entry['groupe_id'])) {
                    $sk = $entry['groupe_id'].'|'.$entry['date'].'|'.$entry['jour'].'|'.$entry['creneau'];
                    $byGroupeSlot[$sk] = $entry;
                } else {
                    $withoutGroupeSlot[] = $entry;
                }
            }
            $toInsert = array_merge(array_values($byGroupeSlot), $withoutGroupeSlot);

            // Remove existing rows for those group slots (any formateur) to avoid unique constraint violations
            foreach ($byGroupeSlot as $entry) {
                EmploiDuTemps::query()
                    ->where('groupe_id', $entry['groupe_id'])
                    ->whereDate('date', $entry['date'])
                    ->where('jour', $entry['jour'])
                    ->where('creneau', $entry['creneau'])
                    ->delete();
            }

            // Insert entries
            foreach ($toInsert as $entry) {
                if (empty($entry['formateur_id']) || empty($entry['date']) || empty($entry['jour']) || empty($entry['creneau'])) {
                    continue;
                }

                EmploiDuTemps::withoutGlobalScope('academic_year')->updateOrCreate(
                    [
                        'formateur_id' => $entry['formateur_id'],
                        'groupe_id' => $entry['groupe_id'],
                        'date' => $entry['date'],
                        'jour' => $entry['jour'],
                        'creneau' => $entry['creneau'],
                    ],
                    array_merge($entry, ['academic_year' => $currentYear])
                );
            }

            DB::commit();

            // Clear related caches
            foreach ($formateurIds as $formateurId) {
                Cache::forget('timetable_formateur_' . $formateurId . '_' . $date);
                Cache::forget('groupes_for_formateur_' . $formateurId);
            }
            // Clear centre caches (since formateur changes affect centre views)
            Cache::forget('timetable_centre_all_' . $date);
            // Clear salle availability caches for this date
            Cache::forget('salles_disponibles_' . $date . '_Lundi_S1_none');
            Cache::forget('salles_disponibles_' . $date . '_Lundi_S2_none');
            Cache::forget('salles_disponibles_' . $date . '_Lundi_S3_none');
            Cache::forget('salles_disponibles_' . $date . '_Lundi_S4_none');
            Cache::forget('salles_disponibles_' . $date . '_Mardi_S1_none');
            Cache::forget('salles_disponibles_' . $date . '_Mardi_S2_none');
            Cache::forget('salles_disponibles_' . $date . '_Mardi_S3_none');
            Cache::forget('salles_disponibles_' . $date . '_Mardi_S4_none');
            Cache::forget('salles_disponibles_' . $date . '_Mercredi_S1_none');
            Cache::forget('salles_disponibles_' . $date . '_Mercredi_S2_none');
            Cache::forget('salles_disponibles_' . $date . '_Mercredi_S3_none');
            Cache::forget('salles_disponibles_' . $date . '_Mercredi_S4_none');
            Cache::forget('salles_disponibles_' . $date . '_Jeudi_S1_none');
            Cache::forget('salles_disponibles_' . $date . '_Jeudi_S2_none');
            Cache::forget('salles_disponibles_' . $date . '_Jeudi_S3_none');
            Cache::forget('salles_disponibles_' . $date . '_Jeudi_S4_none');
            Cache::forget('salles_disponibles_' . $date . '_Vendredi_S1_none');
            Cache::forget('salles_disponibles_' . $date . '_Vendredi_S2_none');
            Cache::forget('salles_disponibles_' . $date . '_Vendredi_S3_none');
            Cache::forget('salles_disponibles_' . $date . '_Vendredi_S4_none');
            Cache::forget('salles_disponibles_' . $date . '_Samedi_S1_none');
            Cache::forget('salles_disponibles_' . $date . '_Samedi_S2_none');
            Cache::forget('salles_disponibles_' . $date . '_Samedi_S3_none');
            Cache::forget('salles_disponibles_' . $date . '_Samedi_S4_none');

            return response()->json(['success' => true, 'message' => 'Timetable saved successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function saveForFormateur(Request $request)
    {
        // This endpoint is kept for backward compatibility with older frontends.
        return $this->saveForFormateurs($request);
    }

    protected function indexToDayName($index)
    {
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        return $days[$index] ?? null;
    }

    protected function indexToCreneau($index)
    {
        if (!is_numeric($index)) {
            return null;
        }

        $i = intval($index);
        if ($i < 0 || $i > 3) {
            return null;
        }

        return 'S' . ($i + 1);
    }
    
    /**
     * Save timetable for centre (group view)
     * POST /api/timetable-centre
     */
    public function saveTimetableForCentre(Request $request)
    {
        $centreId = $request->input('centre_id');
        $date = Carbon::parse($request->input('date'))->format('Y-m-d');
        $entries = $request->input('entries', []);
        $currentYear = Setting::get('academic_year');
        $entries = array_map(function ($entry) use ($date) {
            if (!empty($entry['date'])) {
                $entry['date'] = Carbon::parse($entry['date'])->format('Y-m-d');
            } else {
                $entry['date'] = $date;
            }
            return $entry;
        }, $entries);
        
        DB::beginTransaction();
        try {
            // Insert new entries, deleting existing for each slot
            foreach ($entries as $entry) {
                $salleId = $entry['salle_id'] ?? null;
                $typeSession = $this->getTypeSession($salleId);
                if ($typeSession === 'distance') {
                    $salleId = null;
                }

                // Save any entry that has at least one non-empty value or is a Teams session
                if (!empty($entry['formateur_id']) || !empty($entry['module_id']) || $typeSession === 'distance') {
                    // Ensure the group belongs to the selected centre (prevents wrong-row issues)
                    $groupCentreId = \App\Models\Groupe::where('id', $entry['groupe_id'])->value('centre_id');
                    if ((string) $groupCentreId !== (string) $centreId) {
                        continue;
                    }

                    EmploiDuTemps::withoutGlobalScope('academic_year')->updateOrCreate(
                        [
                            'groupe_id' => $entry['groupe_id'],
                            'date' => $entry['date'],
                            'jour' => $entry['jour'],
                            'creneau' => $entry['creneau'],
                        ],
                        [
                            'formateur_id' => $entry['formateur_id'] ?? null,
                            'module_id' => $entry['module_id'] ?? null,
                            'salle_id' => $salleId,
                            'type_session' => $typeSession,
                            'academic_year' => $currentYear,
                            'duree_heures' => 2,
                        ]
                    );
                }
            }
            
            DB::commit();

            // Clear related caches
            Cache::forget('timetable_centre_' . ($centreId ?? 'all') . '_' . $date);
            Cache::forget('emploi_groupe_' . ($centreId ?? 'all') . '_' . $date);
            // Clear salle availability caches for this date
            $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            $seances = ['S1', 'S2', 'S3', 'S4'];
            foreach ($jours as $jour) {
                foreach ($seances as $seance) {
                    Cache::forget('salles_disponibles_' . $date . '_' . $jour . '_' . $seance . '_none');
                }
            }

            return response()->json(['success' => true, 'message' => 'Timetable saved successfully']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Delete a timetable entry
     * DELETE /api/timetable/{id}
     */
    public function destroy($id)
    {
        try {
            $emploi = EmploiDuTemps::findOrFail($id);
            $date = $emploi->date;
            $formateurId = $emploi->formateur_id;
            $groupe = $emploi->groupe;
            $centreId = $groupe ? $groupe->centre_id : null;

            $emploi->delete();

            // Clear related caches
            if ($formateurId) {
                Cache::forget('timetable_formateur_' . $formateurId . '_' . $date);
                Cache::forget('groupes_for_formateur_' . $formateurId);
            }
            Cache::forget('timetable_centre_' . ($centreId ?? 'all') . '_' . $date);
            Cache::forget('emploi_groupe_' . ($centreId ?? 'all') . '_' . $date);
            // Clear salle availability caches for this date
            $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            $seances = ['S1', 'S2', 'S3', 'S4'];
            foreach ($jours as $jour) {
                foreach ($seances as $seance) {
                    Cache::forget('salles_disponibles_' . $date . '_' . $jour . '_' . $seance . '_none');
                }
            }

            return response()->json(['message' => 'Entry deleted']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Emploi du temps Groupe loading
     * GET /api/emploi-groupe/load?centre_id={id}&date=YYYY-MM-DD
     */
    public function loadGroupeTimetable(Request $request)
    {
        $validated = $request->validate([
            'centre_id' => ['nullable', 'integer', 'exists:centres,id'],
            'date' => ['required', 'date'],
        ]);

        $centreId = $validated['centre_id'] ?? null;
        $date = $validated['date'];
        $cacheKey = 'emploi_groupe_' . ($centreId ?? 'all') . '_' . $date;

        $timetable = Cache::remember($cacheKey, 3600, function() use ($centreId, $date) {
            // Optimized query - select only needed columns
            $query = EmploiDuTemps::query()
                ->select(['id', 'groupe_id', 'formateur_id', 'module_id', 'salle_id', 'jour', 'creneau', 'date', 'type_session'])
                ->with([
                    'groupe:id,nomGroupe,centre_id',
                    'formateur:id,nom,prenom',
                    'module:id,codeModule',
                    'salle:id,nomSalle',
                ])
                ->whereDate('date', $date);

            if ($centreId) {
                $query->whereHas('groupe', function ($g) use ($centreId) {
                    $g->where('centre_id', $centreId);
                });
            }

            return $query->get();
        });

        return response()->json(['data' => $timetable]);
    }

    /**
     * Emploi du temps Groupe saving
     * POST /api/emploi-groupe/save
     *
     * Payload: { centre_id, date, entries: [{groupe_id,formateur_id,module_id,salle_id,jour,creneau,date}] }
     */
    public function saveGroupeTimetable(Request $request)
    {
        $validated = $request->validate([
            'centre_id' => ['nullable', 'integer', 'exists:centres,id'],
            'date' => ['required', 'date'],
            'entries' => ['nullable', 'array'],
            'entries.*.groupe_id' => ['required_with:entries', 'integer', 'exists:groupes,id'],
            'entries.*.formateur_id' => ['nullable', 'integer', 'exists:formateurs,id'],
            'entries.*.module_id' => ['nullable', 'integer', 'exists:modules,id'],
            'entries.*.salle_id' => ['nullable'], // Can be null, numeric, or 'teams'
            'entries.*.type_session' => ['nullable', 'in:presentiel,distance,efm'],
            'entries.*.jour' => ['required_with:entries', 'string', 'in:Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi'],
            'entries.*.creneau' => ['required_with:entries', 'string', 'in:S1,S2,S3,S4'],
            'entries.*.date' => ['required_with:entries', 'date'],
        ]);

        $centreId = $validated['centre_id'] ?? null;
        $date = Carbon::parse($validated['date'])->format('Y-m-d');
        $entries = $validated['entries'] ?? [];
        $currentYear = Setting::get('academic_year');

        $entries = array_map(function ($entry) use ($date) {
            if (!empty($entry['date'])) {
                $entry['date'] = Carbon::parse($entry['date'])->format('Y-m-d');
            } else {
                $entry['date'] = $date;
            }
            return $entry;
        }, $entries);

        DB::beginTransaction();
        try {
            if (empty($entries)) {
                $deleteQuery = EmploiDuTemps::query()
                    ->whereDate('date', $date);

                if ($currentYear) {
                    $deleteQuery->where('academic_year', $currentYear);
                }

                if ($centreId) {
                    $deleteQuery->whereHas('groupe', function ($q) use ($centreId) {
                        $q->where('centre_id', $centreId);
                    });
                }

                $deleteQuery->delete();

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Emploi du temps supprimé avec succès']);
            }

            $groupIds = array_unique(array_filter(array_map(function ($entry) {
                return $entry['groupe_id'] ?? null;
            }, $entries)));

            if (!empty($groupIds)) {
                $deleteQuery = EmploiDuTemps::query()
                    ->whereIn('groupe_id', $groupIds)
                    ->whereDate('date', $date);

                if ($currentYear) {
                    $deleteQuery->where('academic_year', $currentYear);
                }

                if ($centreId) {
                    $deleteQuery->whereHas('groupe', function ($q) use ($centreId) {
                        $q->where('centre_id', $centreId);
                    });
                }

                $deleteQuery->delete();
            }

            $uniqueEntries = [];
            foreach ($entries as $entry) {
                if (empty($entry['groupe_id'])) {
                    continue;
                }

                $slotKey = sprintf('%s|%s|%s|%s', $entry['groupe_id'], $entry['date'], $entry['jour'], $entry['creneau']);
                $uniqueEntries[$slotKey] = $entry;
            }

            foreach ($uniqueEntries as $entry) {
                if (empty($entry['groupe_id'])) {
                    continue;
                }

                // Ensure group belongs to selected centre
                if ($centreId !== null) {
                    $groupCentreId = \App\Models\Groupe::where('id', $entry['groupe_id'])->value('centre_id');
                    if ((string) $groupCentreId !== (string) $centreId) {
                        continue;
                    }
                }

                $rawSalle = $entry['salle_id'] ?? null;
                $incomingType = $entry['type_session'] ?? null;
                $typeSession = in_array($incomingType, ['distance', 'presentiel', 'efm'], true)
                    ? $incomingType
                    : $this->getTypeSession($rawSalle);

                $hasPayload = ! empty($entry['formateur_id'])
                    || ! empty($entry['module_id'])
                    || ! empty($rawSalle)
                    || $typeSession === 'distance'
                    || $typeSession === 'efm';

                if (! $hasPayload) {
                    continue;
                }

                $salleId = null;
                if ($rawSalle) {
                    if (is_numeric($rawSalle)) {
                        $salleId = (int) $rawSalle;
                    } elseif ($rawSalle === 'teams') {
                        // handled by typeSession
                    } else {
                        // Custom salle name like "EFM" - find or create salle
                        $salle = Salle::firstOrCreate(
                            ['nomSalle' => strtoupper($rawSalle)],
                            ['nomSalle' => strtoupper($rawSalle), 'capacite' => 0, 'centre_id' => null]
                        );
                        $salleId = $salle->id;
                    }
                }

                if ($typeSession === 'distance') {
                    $salleId = null;
                }

                EmploiDuTemps::withoutGlobalScope('academic_year')->updateOrCreate(
                    [
                        'groupe_id' => $entry['groupe_id'],
                        'date' => $entry['date'],
                        'jour' => $entry['jour'],
                        'creneau' => $entry['creneau'],
                    ],
                    [
                        'formateur_id' => $entry['formateur_id'] ?? null,
                        'module_id' => $entry['module_id'] ?? null,
                        'salle_id' => $salleId,
                        'type_session' => $typeSession,
                        'academic_year' => $currentYear,
                        'duree_heures' => 2,
                    ]
                );
            }

            DB::commit();

            // Clear related caches
            Cache::forget('timetable_centre_' . ($centreId ?? 'all') . '_' . $date);
            Cache::forget('emploi_groupe_' . ($centreId ?? 'all') . '_' . $date);
            // Clear salle availability caches for this date
            $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            $seances = ['S1', 'S2', 'S3', 'S4'];
            foreach ($jours as $jour) {
                foreach ($seances as $seance) {
                    Cache::forget('salles_disponibles_' . $date . '_' . $jour . '_' . $seance . '_none');
                }
            }

            return response()->json(['success' => true, 'message' => 'Emploi du temps chargé avec succès!']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Export the timetable as an Excel workbook for a selected date.
     * GET /api/timetable-export?type=centre&centre_id={id}&date=YYYY-MM-DD&groupe_id={id}
     * GET /api/timetable-export?type=formateur&date=YYYY-MM-DD
     */
    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'type' => ['required', 'string', 'in:centre,formateur'],
            'centre_id' => ['required_if:type,centre', 'integer', 'exists:centres,id'],
            'groupe_id' => ['nullable', 'integer', 'exists:groupes,id'],
        ]);

        $date = $validated['date'];
        $type = $validated['type'];

        if ($type === 'centre') {
            // Keep the existing centre logic
            $centreId = $validated['centre_id'] ?? null;
            $groupeId = $validated['groupe_id'] ?? null;

            $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            $seances = [
                'S1' => '08h30-11h',
                'S2' => '11h-13h30',
                'S3' => '13h30-16h',
                'S4' => '16h-18h30',
            ];

            $query = EmploiDuTemps::with(['groupe.centre', 'formateur', 'module', 'salle.centre'])
                ->whereDate('date', $date)
                ->orderBy('groupe_id')
                ->orderBy('jour')
                ->orderBy('creneau');

            $query->whereHas('groupe', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });

            // Filter by specific group if groupe_id is provided
            if ($groupeId) {
                $query->where('groupe_id', $groupeId);
            }

            $emplois = $query->get();
            $centre = $centreId ? Centre::find($centreId) : null;
            $centreName = $centre?->nomCentre ?? 'Centre de Formation';

            $year = Carbon::parse($date)->year;
            $month = Carbon::parse($date)->month;
            $academicYear = $month >= 9 ? "$year-" . ($year + 1) : ($year - 1) . "-$year";

            // Get all groups for the centre, using the same logic as the groups API
            $query = Groupe::query()->where('centre_id', $centreId)->orderBy('id', 'desc');
            $groupes = $query->with(['centre','modules','emplois'])->get()->map(function (Groupe $groupe) {
                $groupe->load(['modules', 'emplois']);
                $groupe->avancement;
                $groupe->advancement;
                return $groupe;
            });

            if ($groupeId) {
                $groupes = $groupes->where('id', $groupeId);
            }

            $rowsByParent = [];
            foreach ($groupes as $groupe) {
                $parentKey = $groupe->id;
                $parentLabel = $groupe->centre ? strtoupper($groupe->centre->shortName) . ' - ' . $groupe->nomGroupe : $groupe->nomGroupe;
                $rowsByParent[$parentKey] = [
                    'label' => $parentLabel,
                    'cells' => [
                        'formateur' => [],
                        'module' => [],
                        'salle' => [],
                    ],
                ];
            }
            foreach ($emplois as $emploi) {
                if (!$emploi->groupe) {
                    continue;
                }
                $parentKey = $emploi->groupe->id;
                // $parentLabel already set above

                $key = $emploi->jour . '|' . $emploi->creneau;

                $rowsByParent[$parentKey]['cells']['formateur'][$key] = $emploi->formateur ? trim($emploi->formateur->nom . ' ' . $emploi->formateur->prenom) : '';
                $rowsByParent[$parentKey]['cells']['module'][$key] = $emploi->module?->nomModule ?? '';
                $rowsByParent[$parentKey]['cells']['salle'][$key] = $emploi->type_session === 'distance' || strtolower($emploi->salle?->nomSalle ?? '') === 'teams'
                    ? 'TEAMS'
                    : ($emploi->type_session === 'efm' ? 'EFM' : ($emploi->salle?->display_name ?? ''));
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Emploi de Temps');

            $totalCols = 2 + (count($jours) * count($seances));
            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

            $titleStyle = [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF1E3A5F']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            ];
            $subtitleLeftStyle = [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E3A5F']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            ];
            $subtitleRightStyle = [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFCC0000']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            ];
            $dayHeaderStyle = [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
            ];
            $subHeaderStyle = [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E6DA4']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']]],
            ];
            $groupStyle = [
                'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF1E3A5F']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8F0FE']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
            ];
            $labelStyle = [
                'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF1F2937']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
            ];
            $cellStyle = [
                'font' => ['size' => 9, 'color' => ['argb' => 'FF1F2937']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
            ];
            $teamsStyle = [
                'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF2563EB']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
            ];
            $efmStyle = [
                'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF22C55E']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
            ];

            $sheet->mergeCells("A1:{$lastCol}1");
            $sheet->setCellValue('A1', "EMPLOI DE TEMPS {$academicYear}");
            $sheet->getStyle('A1')->applyFromArray($titleStyle);
            $sheet->getRowDimension(1)->setRowHeight(28);

            $sheet->mergeCells('A2:M2');
            $sheet->setCellValue('A2', strtoupper($centreName));
            $sheet->getStyle('A2')->applyFromArray($subtitleLeftStyle);

            $sheet->mergeCells("N2:{$lastCol}2");
            $sheet->setCellValue("N2", 'A PARTIR DU ' . Carbon::parse($date)->format('d-m-Y'));
            $sheet->getStyle("N2:{$lastCol}2")->applyFromArray($subtitleRightStyle);
            $sheet->getRowDimension(2)->setRowHeight(24);
            $sheet->getRowDimension(3)->setRowHeight(10);

            $sheet->setCellValue('A4', '');
            $sheet->setCellValue('B4', '');
            $col = 3;
            foreach ($jours as $jour) {
                $start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $end = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 3);
                $sheet->mergeCells("{$start}4:{$end}4");
                $sheet->setCellValue("{$start}4", $jour);
                $sheet->getStyle("{$start}4:{$end}4")->applyFromArray($dayHeaderStyle);
                $col += 4;
            }
            $sheet->getRowDimension(4)->setRowHeight(22);

            $sheet->setCellValue('A5', '');
            $sheet->setCellValue('B5', '');
            $col = 3;
            foreach ($jours as $jour) {
                $s1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $s2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                $sheet->mergeCells("{$s1}5:{$s2}5");
                $sheet->setCellValue("{$s1}5", 'Matin');
                $sheet->getStyle("{$s1}5:{$s2}5")->applyFromArray($subHeaderStyle);

                $s3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 2);
                $s4 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 3);
                $sheet->mergeCells("{$s3}5:{$s4}5");
                $sheet->setCellValue("{$s3}5", 'AM');
                $sheet->getStyle("{$s3}5:{$s4}5")->applyFromArray($subHeaderStyle);

                $col += 4;
            }
            $sheet->getRowDimension(5)->setRowHeight(20);

            $sheet->setCellValue('A6', '');
            $sheet->setCellValue('B6', '');
            $col = 3;
            foreach ($jours as $jour) {
                foreach ($seances as $time) {
                    $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $sheet->setCellValue("{$letter}6", $time);
                    $sheet->getStyle("{$letter}6")->applyFromArray($subHeaderStyle);
                    $sheet->getColumnDimension($letter)->setWidth(15);
                    $col++;
                }
            }
            $sheet->getRowDimension(6)->setRowHeight(20);

            $sheet->getColumnDimension('A')->setWidth(22);
            $sheet->getColumnDimension('B')->setWidth(16);

            $sheet->getStyle("A4:{$lastCol}6")->getAlignment()->setWrapText(true);

            $currentRow = 7;
            foreach ($rowsByParent as $parent) {
                $sheet->mergeCells("A{$currentRow}:A" . ($currentRow + 2));
                $sheet->setCellValue("A{$currentRow}", $parent['label']);
                $sheet->getStyle("A{$currentRow}:A" . ($currentRow + 2))->applyFromArray($groupStyle);

                $sheet->setCellValue("B{$currentRow}", 'FORMATEUR');
                $sheet->setCellValue("B" . ($currentRow + 1), 'MODULE');
                $sheet->setCellValue("B" . ($currentRow + 2), 'EFP / SALLE');
                $sheet->getStyle("B{$currentRow}:B" . ($currentRow + 2))->applyFromArray($labelStyle);

                for ($dayIndex = 0; $dayIndex < count($jours); $dayIndex++) {
                    $jour = $jours[$dayIndex];
                    foreach (array_keys($seances) as $slotIndex => $slotKey) {
                        $colIndex = 3 + ($dayIndex * 4) + $slotIndex;
                        $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                        $cellKey = $jour . '|' . $slotKey;

                        $firstRowValue = $parent['cells']['formateur'][$cellKey] ?? '';
                        $moduleValue = $parent['cells']['module'][$cellKey] ?? '';
                        $salleValue = $parent['cells']['salle'][$cellKey] ?? '';

                        $sheet->setCellValue("{$letter}{$currentRow}", $firstRowValue);
                        $sheet->setCellValue("{$letter}" . ($currentRow + 1), $moduleValue);
                        $sheet->setCellValue("{$letter}" . ($currentRow + 2), $salleValue);

                        $sheet->getStyle("{$letter}{$currentRow}:{$letter}" . ($currentRow + 2))->applyFromArray($cellStyle);

                        if (strtoupper($salleValue) === 'TEAMS') {
                            $sheet->getStyle("{$letter}" . ($currentRow + 2))->applyFromArray($teamsStyle);
                        } elseif (strtoupper($salleValue) === 'EFM') {
                            $sheet->getStyle("{$letter}" . ($currentRow + 2))->applyFromArray($efmStyle);
                        }
                    }
                }

                $sheet->getRowDimension($currentRow)->setRowHeight(22);
                $sheet->getRowDimension($currentRow + 1)->setRowHeight(22);
                $sheet->getRowDimension($currentRow + 2)->setRowHeight(22);
                $currentRow += 3;
            }

            $sheet->freezePane('C7');

            $fileName = sprintf('emploi-temps-%s-%s.xlsx', str_replace([' ', '/'], '_', strtolower($centreName)), $date);
            $writer = new Xlsx($spreadsheet);

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } else {
            // Formateur export - FIXED to include ALL formateurs
            // ══════════════════════════════════════════════════════
            // Get ALL formateurs in the same order as the page
            // ══════════════════════════════════════════════════════
            $formateurs = \App\Models\Formateur::all();
            
            // Then get emplois for this date
            $emplois = \App\Models\EmploiDuTemps::with(['groupe', 'module', 'salle.centre', 'formateur'])
                ->whereDate('date', $date)
                ->get();
            
            // Index emplois by formateur for fast lookup
            $emploisByFormateur = $emplois->groupBy('formateur_id');
            
            // ══════════════════════════════════════════════════════
            // Define time structure
            // ══════════════════════════════════════════════════════
            $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            $seances = [
                'S1' => '08h30-11h',
                'S2' => '11h-13h30',
                'S3' => '13h30-16h',
                'S4' => '16h-18h30'
            ];
            
            // ══════════════════════════════════════════════════════
            // Create spreadsheet
            // ══════════════════════════════════════════════════════
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Emploi Formateurs');
            
            // ══════════════════════════════════════════════════════
            // Styles
            // ══════════════════════════════════════════════════════
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ];
            
            $subHeaderStyle = [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E6DA4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ];
            
            $formateurStyle = [
                'font' => ['bold' => true, 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8F0FE']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ];
            
            $cellStyle = [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'font' => ['size' => 9]
            ];
            
            $teamsStyle = [
                'font' => ['bold' => true, 'color' => ['argb' => 'FF2563EB'], 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ];
            $efmStyle = [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF22C55E']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ];
            
            // ══════════════════════════════════════════════════════
            // Header rows
            // ══════════════════════════════════════════════════════
            $totalCols = 2 + (count($jours) * count($seances));
            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);
            
            // Row 1: Title
            $sheet->mergeCells("A1:{$lastCol}1");
            $sheet->setCellValue('A1', 'EMPLOI DES FORMATEURS - ' . \Carbon\Carbon::parse($date)->format('d/m/Y'));
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1E3A5F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            $sheet->getRowDimension(1)->setRowHeight(24);
            
            // Row 2: Empty
            $sheet->getRowDimension(2)->setRowHeight(8);
            
            // Row 3: Day headers
            $sheet->setCellValue('A3', '');
            $sheet->setCellValue('B3', '');
            $col = 3;
            foreach ($jours as $jour) {
                $startCol = $col;
                $endCol = $col + count($seances) - 1;
                $startLtr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol);
                $endLtr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endCol);
                $sheet->mergeCells("{$startLtr}3:{$endLtr}3");
                $sheet->setCellValue("{$startLtr}3", $jour);
                $sheet->getStyle("{$startLtr}3:{$endLtr}3")->applyFromArray($headerStyle);
                $col += count($seances);
            }
            $sheet->getRowDimension(3)->setRowHeight(20);
            
            // Row 4: Matin/AM
            $sheet->setCellValue('A4', 'Formateur');
            $sheet->setCellValue('B4', 'Type');
            $col = 3;
            foreach ($jours as $jour) {
                $s1Ltr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $s2Ltr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                $sheet->mergeCells("{$s1Ltr}4:{$s2Ltr}4");
                $sheet->setCellValue("{$s1Ltr}4", 'Matin');
                $sheet->getStyle("{$s1Ltr}4:{$s2Ltr}4")->applyFromArray($subHeaderStyle);
                
                $s3Ltr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 2);
                $s4Ltr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 3);
                $sheet->mergeCells("{$s3Ltr}4:{$s4Ltr}4");
                $sheet->setCellValue("{$s3Ltr}4", 'AM');
                $sheet->getStyle("{$s3Ltr}4:{$s4Ltr}4")->applyFromArray($subHeaderStyle);
                
                $col += count($seances);
            }
            $sheet->getRowDimension(4)->setRowHeight(18);
            
            // Row 5: Time slots
            $sheet->setCellValue('A5', '');
            $sheet->setCellValue('B5', '');
            $col = 3;
            foreach ($jours as $jour) {
                foreach ($seances as $key => $time) {
                    $ltr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $sheet->setCellValue("{$ltr}5", $time);
                    $sheet->getStyle("{$ltr}5")->applyFromArray($subHeaderStyle);
                    $sheet->getColumnDimension($ltr)->setWidth(14);
                    $col++;
                }
            }
            $sheet->getRowDimension(5)->setRowHeight(18);
            
            $sheet->getStyle('A3:B5')->applyFromArray($headerStyle);
            $sheet->getColumnDimension('A')->setWidth(22);
            $sheet->getColumnDimension('B')->setWidth(14);
            
            // ══════════════════════════════════════════════════════
            // Data rows - LOOP THROUGH ALL FORMATEURS
            // ══════════════════════════════════════════════════════
            $currentRow = 6;
            
            foreach ($formateurs as $formateur) {
                $rowGroupe = $currentRow;
                $rowModule = $currentRow + 1;
                $rowSalle = $currentRow + 2;
                
                // Column A: Formateur name (merged 3 rows)
                $formateurName = $formateur->nom . ' ' . $formateur->prenom;
                $sheet->mergeCells("A{$rowGroupe}:A{$rowSalle}");
                $sheet->setCellValue("A{$rowGroupe}", $formateurName);
                $sheet->getStyle("A{$rowGroupe}:A{$rowSalle}")->applyFromArray($formateurStyle);
                
                // Column B: Labels
                $sheet->setCellValue("B{$rowGroupe}", 'GROUPE');
                $sheet->setCellValue("B{$rowModule}", 'MODULE');
                $sheet->setCellValue("B{$rowSalle}", 'SALLE');
                $sheet->getStyle("B{$rowGroupe}:B{$rowSalle}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ]);
                
                // Get emplois for this formateur (may be empty)
                $formateurEmplois = $emploisByFormateur->get($formateur->id, collect());
                
                // Index emplois by jour|seance
                $emploisIndex = [];
                foreach ($formateurEmplois as $emp) {
                    $key = $emp->jour . '|' . $emp->creneau;
                    $emploisIndex[$key] = $emp;
                }
                
                // Fill cells
                $col = 3;
                foreach ($jours as $jour) {
                    foreach (array_keys($seances) as $seanceKey) {
                        $ltr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $key = $jour . '|' . $seanceKey;
                        
                        if (isset($emploisIndex[$key])) {
                            $emp = $emploisIndex[$key];
                            
                            // GROUPE
                            $groupeName = $emp->groupe->nomGroupe ?? '';
                            $sheet->setCellValue("{$ltr}{$rowGroupe}", $groupeName);
                            $sheet->getStyle("{$ltr}{$rowGroupe}")->applyFromArray($cellStyle);
                            
                            // MODULE
                            $moduleName = $emp->module->nomModule ?? '';
                            $sheet->setCellValue("{$ltr}{$rowModule}", $moduleName);
                            $sheet->getStyle("{$ltr}{$rowModule}")->applyFromArray($cellStyle);
                            
                            // SALLE
                            if ($emp->type_session === 'distance') {
                                $sheet->setCellValue("{$ltr}{$rowSalle}", 'TEAMS');
                                $sheet->getStyle("{$ltr}{$rowSalle}")->applyFromArray($teamsStyle);
                            } elseif ($emp->type_session === 'efm') {
                                $sheet->setCellValue("{$ltr}{$rowSalle}", 'EFM');
                                $sheet->getStyle("{$ltr}{$rowSalle}")->applyFromArray($efmStyle);
                            } else {
                                $salleName = $emp->salle->display_name ?? '';
                                $sheet->setCellValue("{$ltr}{$rowSalle}", $salleName);
                                $sheet->getStyle("{$ltr}{$rowSalle}")->applyFromArray($cellStyle);
                            }
                        } else {
                            // Empty cells for formateurs with no emploi at this slot
                            $sheet->setCellValue("{$ltr}{$rowGroupe}", '');
                            $sheet->setCellValue("{$ltr}{$rowModule}", '');
                            $sheet->setCellValue("{$ltr}{$rowSalle}", '');
                            $sheet->getStyle("{$ltr}{$rowGroupe}:{$ltr}{$rowSalle}")->applyFromArray($cellStyle);
                        }
                        
                        $col++;
                    }
                }
                
                $sheet->getRowDimension($rowGroupe)->setRowHeight(22);
                $sheet->getRowDimension($rowModule)->setRowHeight(22);
                $sheet->getRowDimension($rowSalle)->setRowHeight(22);
                
                $currentRow += 3;
            }
            
            // Freeze panes
            $sheet->freezePane('C6');
            
            // Download
            $fileName = 'emploi-formateurs-' . $date . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            
            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ]);
        }
    }
}
