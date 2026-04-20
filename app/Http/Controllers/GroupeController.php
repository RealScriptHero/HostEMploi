<?php

namespace App\Http\Controllers;

use App\Models\Groupe;
use App\Models\EmploiDuTemps;
use App\Models\Stage;
use App\Models\AbsenceGroupe;
use App\Models\AbsenceFormateur;
use App\Services\RapportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PDO;

class GroupeController extends Controller
{
    /**
     * Display a listing of groupes.
     */
    public function index(Request $request): JsonResponse
    {
        // If caller requested all groups (for selects), return full list without pagination
        if ($request->query('all')) {
            $all = Groupe::with(['centre:id,shortName'])->orderBy('id', 'asc')->get();
            return response()->json(['data' => $all]);
        }

        $search = $request->query('search');
        $filiere = $request->query('filiere');
        $niveau = $request->query('niveau');
        $centre_id = $request->query('centre_id');

        $perPage = (int) $request->query('perPage', 6);
        $page = (int) $request->query('page', 1);
        $noPagination = $request->query('no_pagination') || $perPage >= 1000; // Allow high perPage to bypass pagination

        $query = Groupe::query()->orderBy('id', 'asc'); // Stable order

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomGroupe', 'like', "%{$search}%")
                  ->orWhere('filiere', 'like', "%{$search}%")
                  ->orWhere('niveau', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($filiere) {
            $query->where('filiere', $filiere);
        }

        if ($niveau) {
            $query->where('niveau', $niveau);
        }

        if ($centre_id) {
            $query->where('centre_id', $centre_id);
        }

        if ($noPagination) {
            // Return all groups without pagination
            $groups = $query->with(['centre','modules','emplois'])->get()->map(function (Groupe $groupe) {
                $groupe->load(['modules', 'emplois']);
                $groupe->avancement;
                $groupe->advancement;
                return $groupe;
            });

            // Debug: Log the results
            \Log::info('Groups fetched (no pagination)', [
                'count' => $groups->count(),
                'applied_filters' => [
                    'search' => $search,
                    'filiere' => $filiere,
                    'niveau' => $niveau,
                    'centre_id' => $centre_id,
                ],
                'cache_hit' => false,
            ]);

            return response()->json([
                'data' => $groups,
                'total' => $groups->count(),
                'perPage' => $groups->count(),
                'currentPage' => 1,
                'lastPage' => 1,
            ]);
        }

        $paginator = $query->with(['centre','modules','emplois'])->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function (Groupe $groupe) {
            // Ensure relationships are loaded and progress is calculated
            $groupe->load(['modules', 'emplois']);
            // Force refresh of appended attributes
            $groupe->avancement;
            $groupe->advancement;
            return $groupe;
        })->values();

        // Debug: Log the results
        \Log::info('Groups fetched (paginated)', [
            'count' => $items->count(),
            'total' => $paginator->total(),
            'page' => $page,
            'perPage' => $perPage,
            'applied_filters' => [
                'search' => $search,
                'filiere' => $filiere,
                'niveau' => $niveau,
                'centre_id' => $centre_id,
            ],
            'cache_hit' => false,
        ]);

        return response()->json([
            'data' => $items,
            'total' => $paginator->total(),
            'perPage' => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
        ]);
    }

    /**
     * Store a newly created groupe.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nomGroupe' => 'required|string|max:255|unique:groupes,nomGroupe',
            'centre_id' => 'required|exists:centres,id',
            'filiere' => 'required|string|max:255',
            'niveau' => 'required|string|max:255',
            'effectif' => 'required|integer|min:0',
            'notes' => 'sometimes|nullable|string',
            'active' => 'sometimes|boolean',
        ]);

        $groupe = Groupe::create($validated);
        // Load centre relation before returning
        $groupe->load('centre');
        Cache::flush();

        return response()->json($groupe, 201);
    }

    /**
     * Display the specified groupe.
     */
    public function show(Groupe $groupe): JsonResponse
    {
        return response()->json($groupe->load('centre'));
    }

    /**
     * Update the specified groupe.
     */
    public function update(Request $request, Groupe $groupe): JsonResponse
    {
        $validated = $request->validate([
            'nomGroupe' => 'sometimes|string|max:255',
            'centre_id' => 'sometimes|exists:centres,id',
            'filiere' => 'sometimes|string|max:255',
            'niveau' => 'sometimes|string|max:255',
            'effectif' => 'sometimes|integer|min:0',
            'notes' => 'sometimes|nullable|string',
            'active' => 'sometimes|boolean',
        ]);

        $groupe->update($validated);
        // Load centre relation before returning
        $groupe->load('centre');
        Cache::flush();

        return response()->json($groupe);
    }

    /**
     * Remove the specified groupe.
     */
    public function destroy(Groupe $groupe): JsonResponse
    {
        $groupeId = $groupe->id;

        try {
            DB::transaction(function () use ($groupe, $groupeId) {
                $groupe->modules()->detach();
                AbsenceFormateur::where('groupe_id', $groupeId)->update(['groupe_id' => null]);
                AbsenceGroupe::where('groupe_id', $groupeId)->delete();
                Stage::where('groupe_id', $groupeId)->delete();
                EmploiDuTemps::where('groupe_id', $groupeId)->delete();
                $groupe->delete();
            });

            Cache::flush();
        } catch (\Throwable $e) {
            \Log::error('Failed to delete groupe', [
                'id' => $groupeId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Could not delete group',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json(['message' => 'Groupe deleted successfully']);
    }

    /**
     * Get unique filières for dropdowns
     */
    public function getFilieres(): JsonResponse
    {
        $filieres = Groupe::query()
            ->whereNotNull('filiere')
            ->where('filiere', '!=', '')
            ->distinct()
            ->pluck('filiere')
            ->sort()
            ->values();

        return response()->json($filieres);
    }
}
