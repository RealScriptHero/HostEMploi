<?php

namespace App\Http\Controllers;

use App\Models\Formateur;
use App\Models\EmploiDuTemps;
use App\Models\Stage;
use App\Models\AbsenceFormateur;
use App\Models\Avancement;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FormateurController extends Controller
{
    public function emploi(): View
    {
        return view('emploi.formateur');
    }

    /**
     * Display a listing of formateurs.
     */
    public function index(): JsonResponse
    {
        $formateurs = Formateur::with(['modules', 'emplois'])->get();

        // Debug: Log the results
        \Log::info('Formateurs fetched', [
            'count' => $formateurs->count(),
            'cache_hit' => false,
        ]);

        return response()->json($formateurs);
    }

    /**
     * Store a newly created formateur.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'specialite' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'email' => 'required|email|unique:formateurs,email',
            'modules' => 'nullable|array',
            'modules.*' => 'exists:modules,id',
        ]);

        $modules = $validated['modules'] ?? [];
        unset($validated['modules']);

        $formateur = Formateur::create($validated);
        
        if (!empty($modules)) {
            $formateur->modules()->sync($modules);
        }
        
        $formateur->load('modules');
        Cache::flush();
        return response()->json($formateur, 201);
    }

    /**
     * Display the specified formateur.
     */
    public function show(Formateur $formateur): JsonResponse
    {
        $formateur->load('modules');
        return response()->json($formateur);
    }

    /**
     * Update the specified formateur.
     */
    public function update(Request $request, Formateur $formateur): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'specialite' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'email' => 'sometimes|email|unique:formateurs,email,' . $formateur->id,
            'modules' => 'nullable|array',
            'modules.*' => 'exists:modules,id',
        ]);

        $modules = $validated['modules'] ?? null;
        unset($validated['modules']);

        $formateur->update($validated);
        
        if ($modules !== null) {
            $formateur->modules()->sync($modules);
        }
        
        $formateur->load('modules');
        Cache::flush();
        return response()->json($formateur);
    }

    /**
     * Remove the specified formateur.
     */
    public function destroy(Formateur $formateur): JsonResponse
    {
        $formateurId = $formateur->id;

        try {
            DB::transaction(function () use ($formateur, $formateurId) {
                $formateur->modules()->detach();
                EmploiDuTemps::where('formateur_id', $formateurId)->delete();
                Stage::where('formateur_id', $formateurId)->delete();
                AbsenceFormateur::where('formateur_id', $formateurId)->delete();
                Avancement::where('formateur_id', $formateurId)->delete();
                $formateur->delete();
            });

            Cache::flush();
        } catch (\Throwable $e) {
            \Log::error('Failed to delete formateur', [
                'id' => $formateurId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Could not delete formateur',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json(['message' => 'Formateur deleted successfully']);
    }
}
