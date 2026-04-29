<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeagueStageRequest;
use App\Http\Requests\UpdateLeagueStageRequest;
use App\Http\Resources\LeagueStageResource;
use App\Models\Arena;
use App\Models\League;
use App\Models\LeagueStage;
use Illuminate\Http\JsonResponse;

class LeagueStageController extends Controller
{
    public function index(Arena $arena, League $league): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $stages = $league->stages;
        return LeagueStageResource::collection($stages);
    }

    public function store(StoreLeagueStageRequest $request, Arena $arena, League $league): LeagueStageResource|JsonResponse
    {
        if ($league->arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $stage = $league->stages()->create($request->validated());
        return new LeagueStageResource($stage);
    }

    public function show(LeagueStage $stage): LeagueStageResource
    {
        return new LeagueStageResource($stage);
    }

    public function update(UpdateLeagueStageRequest $request, LeagueStage $stage): LeagueStageResource|JsonResponse
    {
        if ($stage->league->arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $stage->update($request->validated());
        return new LeagueStageResource($stage);
    }

    public function destroy(Arena $arena, League $league, LeagueStage $stage): JsonResponse
    {
        if ($league->arena_id !== $arena->id) {
            return response()->json(['message' => 'Liga não pertence a esta arena.'], 404);
        }

        if ($stage->league_id !== $league->id) {
            return response()->json(['message' => 'Etapa não pertence a esta liga.'], 404);
        }

        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $deletableStatuses = ['created', 'registrations_open'];
        if (! in_array($stage->getRawOriginal('stage_status'), $deletableStatuses)) {
            return response()->json([
                'message' => 'Não é possível excluir uma etapa que já foi iniciada.',
            ], 422);
        }

        $stage->delete();

        return response()->json(status: 204);
    }
}
