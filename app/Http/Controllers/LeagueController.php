<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeagueRequest;
use App\Http\Requests\UpdateLeagueRequest;
use App\Http\Resources\LeagueResource;
use App\Models\Arena;
use App\Models\League;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeagueController extends Controller
{
    public function publicShow(League $league): LeagueResource
    {
        $league->load(['arena:id,name,city', 'stages']);
        return new LeagueResource($league);
    }

    public function open(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $leagues = League::with(['arena.logo', 'stages'])
            ->where(function ($q) {
                $q->whereNull('data_prevista_termino')
                  ->orWhereDate('data_prevista_termino', '>=', now());
            })
            ->when($request->search,  fn ($q, $v) => $q->where('nome', 'like', "%{$v}%"))
            ->when($request->nivel,   fn ($q, $v) => $q->where('nivel', $v))
            ->when($request->genero,  fn ($q, $v) => $q->where('genero', $v))
            ->when($request->city,    fn ($q, $v) => $q->whereHas('arena', fn ($a) => $a->where('city', 'like', "%{$v}%")))
            ->latest()
            ->get();

        return LeagueResource::collection($leagues);
    }

    public function index(Arena $arena): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $leagues = $arena->leagues()->with(['stages.registrations'])->get();
        return LeagueResource::collection($leagues);
    }

    public function store(StoreLeagueRequest $request, Arena $arena): LeagueResource|JsonResponse
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

    public function update(UpdateLeagueRequest $request, Arena $arena, League $league): LeagueResource|JsonResponse
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
