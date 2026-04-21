<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeagueRequest;
use App\Http\Requests\UpdateLeagueRequest;
use App\Http\Resources\LeagueResource;
use App\Models\Arena;
use App\Models\League;
use Illuminate\Http\JsonResponse;

class LeagueController extends Controller
{
    public function index(Arena $arena): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $leagues = $arena->leagues()->with('stages')->get();
        return LeagueResource::collection($leagues);
    }

    public function store(StoreLeagueRequest $request, Arena $arena): LeagueResource
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $league = $arena->leagues()->create($request->validated());
        return new LeagueResource($league);
    }

    public function show(Arena $arena, League $league): LeagueResource
    {
        $league->load('stages');
        return new LeagueResource($league);
    }

    public function update(UpdateLeagueRequest $request, Arena $arena, League $league): LeagueResource
    {
        if ($league->arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $data = $request->validated();
        unset($data['arena_id']);
        $league->update($data);
        return new LeagueResource($league);
    }

    public function destroy(Arena $arena, League $league): JsonResponse
    {
        if ($league->arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $league->delete();
        return response()->json(status: 204);
    }
}
