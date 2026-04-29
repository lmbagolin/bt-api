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
        $this->authorizeLeague($arena, $league);

        $stages = $league->stages;
        return LeagueStageResource::collection($stages);
    }

    public function store(StoreLeagueStageRequest $request, Arena $arena, League $league): LeagueStageResource|JsonResponse
    {
        $this->authorizeLeague($arena, $league);

        $stage = $league->stages()->create($request->validated());
        return new LeagueStageResource($stage);
    }

    // Rota shallow: GET /stages/{stage} — sem Arena/League na URL
    public function show(LeagueStage $stage): LeagueStageResource
    {
        $this->authorizeArenaOwner($stage->league->arena);

        return new LeagueStageResource($stage);
    }

    // Rota shallow: PUT /stages/{stage} — sem Arena/League na URL
    public function update(UpdateLeagueStageRequest $request, LeagueStage $stage): LeagueStageResource|JsonResponse
    {
        $this->authorizeArenaOwner($stage->league->arena);

        $stage->update($request->validated());
        return new LeagueStageResource($stage);
    }

    public function destroy(Arena $arena, League $league, LeagueStage $stage): JsonResponse
    {
        $this->authorizeStage($arena, $league, $stage);

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
