<?php

namespace App\Http\Controllers;

use App\Http\Resources\LeagueStageFinalistResource;
use App\Models\Arena;
use App\Models\League;
use App\Models\LeagueStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeagueStageFinalistController extends Controller
{
    // -------------------------------------------------------------------------
    // GET /arenas/{arena}/leagues/{league}/stages/{stage}/finalists
    // -------------------------------------------------------------------------
    public function index(Arena $arena, League $league, LeagueStage $stage): AnonymousResourceCollection|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $finalists = $stage->finalists()
            ->with(['registration.player', 'group.groupRegistrations'])
            ->orderBy('group_position')
            ->orderBy('pts', 'desc')
            ->get();

        return LeagueStageFinalistResource::collection($finalists);
    }

    // -------------------------------------------------------------------------
    // POST /arenas/{arena}/leagues/{league}/stages/{stage}/finalists
    // Replaces all finalists for the stage.
    // -------------------------------------------------------------------------
    public function store(Request $request, Arena $arena, League $league, LeagueStage $stage): AnonymousResourceCollection|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'finalists'                    => ['required', 'array', 'min:1'],
            'finalists.*.registration_id'  => ['required', 'integer', 'exists:league_stage_registrations,id'],
            'finalists.*.group_id'         => ['required', 'integer', 'exists:league_stage_groups,id'],
            'finalists.*.group_position'   => ['required', 'integer', 'min:1'],
            'finalists.*.pts'              => ['required', 'integer', 'min:0'],
            'finalists.*.gp'               => ['required', 'integer', 'min:0'],
            'finalists.*.gc'               => ['required', 'integer', 'min:0'],
        ]);

        $stage->finalists()->delete();

        foreach ($request->finalists as $item) {
            $stage->finalists()->create([
                'registration_id' => $item['registration_id'],
                'group_id'        => $item['group_id'],
                'group_position'  => $item['group_position'],
                'pts'             => $item['pts'],
                'gp'              => $item['gp'],
                'gc'              => $item['gc'],
            ]);
        }

        $finalists = $stage->finalists()
            ->with(['registration.player', 'group.groupRegistrations'])
            ->orderBy('group_position')
            ->orderBy('pts', 'desc')
            ->get();

        return LeagueStageFinalistResource::collection($finalists);
    }

    // -------------------------------------------------------------------------
    // DELETE /arenas/{arena}/leagues/{league}/stages/{stage}/finalists
    // -------------------------------------------------------------------------
    public function destroy(Arena $arena, League $league, LeagueStage $stage): JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $stage->finalists()->delete();

        return response()->json(['message' => 'Finalistas removidos com sucesso.']);
    }
}
