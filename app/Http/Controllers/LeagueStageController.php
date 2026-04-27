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

    public function store(StoreLeagueStageRequest $request, Arena $arena, League $league): LeagueStageResource
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

    public function update(UpdateLeagueStageRequest $request, LeagueStage $stage): LeagueStageResource
    {
        if ($stage->league->arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $stage->update($request->validated());
        return new LeagueStageResource($stage);
    }

    public function destroy(LeagueStage $stage): JsonResponse
    {
        if ($stage->league->arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $stage->delete();
        return response()->json(status: 204);
    }
}
