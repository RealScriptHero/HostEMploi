<?php

namespace App\Http\Controllers;

use App\Models\Centre;
use App\Models\Salle;
use App\Models\Groupe;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CentreController extends Controller
{
    /**
     * Display a listing of centres.
     */
    public function index(): JsonResponse
    {
        // List centres only once per id (no eager salles needed for dropdowns; keeps payload small)
        $centres = Centre::query()
            ->orderBy('nomCentre')
            ->get()
            ->unique('id')
            ->values();

        return response()->json($centres);
    }

    /**
     * Store a newly created centre.
     */
    public function store(Request $request): JsonResponse
    {
        // Accept multiple field name variants
        $name = $request->input('nomCentre') ?? $request->input('name') ?? $request->input('nom');
        $short = $request->input('shortName') ?? $request->input('short') ?? $request->input('nomCourt') ?? $request->input('abbreviation');
        $ville = $request->input('ville');
        $adresse = $request->input('adresse');

        if (!$name) {
            return response()->json(['message' => 'Center name is required.'], 422);
        }

        $centre = Centre::create([
            'nomCentre' => $name,
            'shortName' => $short,
            'ville' => $ville,
            'adresse' => $adresse,
        ]);

        Cache::flush();
        return response()->json($centre, 201);
    }

    /**
     * Display the specified centre.
     */
    public function show(Centre $centre): JsonResponse
    {
        return response()->json($centre->load('salles'));
    }

    /**
     * Update the specified centre.
     */
    public function update(Request $request, Centre $centre): JsonResponse
    {
        // Accept multiple field name variants
        $data = [];
        
        $name = $request->input('nomCentre') ?? $request->input('name') ?? $request->input('nom');
        if ($name) {
            $data['nomCentre'] = $name;
        }
        
        $short = $request->input('shortName') ?? $request->input('short') ?? $request->input('nomCourt') ?? $request->input('abbreviation');
        if ($short !== null) {
            $data['shortName'] = $short;
        }
        
        if ($request->has('ville')) {
            $data['ville'] = $request->input('ville');
        }
        
        if ($request->has('adresse')) {
            $data['adresse'] = $request->input('adresse');
        }

        $centre->update($data);
        Cache::flush();
        return response()->json($centre);
    }

    /**
     * Remove the specified centre.
     */
    public function destroy(Centre $centre): JsonResponse
    {
        try {
            DB::transaction(function () use ($centre) {
                Salle::where('centre_id', $centre->id)->delete();
                Groupe::where('centre_id', $centre->id)->update(['centre_id' => null]);
                Timetable::where('centre_id', $centre->id)->update(['centre_id' => null]);
                $centre->delete();
            });

            Cache::flush();
        } catch (\Throwable $e) {
            \Log::error('Failed to delete centre', [
                'id' => $centre->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Could not delete centre',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json(['message' => 'Centre deleted successfully']);
    }
}
