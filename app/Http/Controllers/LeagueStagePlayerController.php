<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Models\LeagueStage;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeagueStagePlayerController extends Controller
{
    public function index(LeagueStage $stage): AnonymousResourceCollection
    {
        $this->authorizeArenaOwner($stage->league->arena);

        $players = $stage->players()->get();
        return PlayerResource::collection($players);
    }

    public function store(Request $request, LeagueStage $stage): JsonResponse
    {
        $player = $request->user()->player;

        if (!$player) {
            return response()->json(['message' => 'Perfil de jogador não encontrado.'], 404);
        }

        if ($stage->players()->where('player_id', $player->id)->exists()) {
            return response()->json(['message' => 'Você já está inscrito nesta etapa.'], 422);
        }

        $status = $this->resolveStatus($stage);

        $stage->players()->attach($player->id, ['player_status' => $status]);

        $message = match ($status) {
            'alternate' => 'Vagas esgotadas. Você foi adicionado como suplente.',
            default     => 'Inscrição realizada com sucesso.',
        };

        return response()->json([
            'message'       => $message,
            'player_status' => $status,
            'player'        => new PlayerResource($player),
        ], 201);
    }

    public function confirm(LeagueStage $stage, Player $player): JsonResponse
    {
        if ($stage->league->arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        if (!$stage->players()->where('player_id', $player->id)->exists()) {
            return response()->json(['message' => 'Jogador não está inscrito nesta etapa.'], 404);
        }

        $stage->players()->updateExistingPivot($player->id, ['player_status' => 'confirmed']);

        return response()->json(['message' => 'Inscrição confirmada com sucesso.']);
    }

    public function destroy(Request $request, LeagueStage $stage, Player $player): JsonResponse
    {
        $user = $request->user();
        $isOwner = $stage->league->arena->owner_id === $user->id;
        $isOwnPlayer = $user->player?->id === $player->id;

        if (!$isOwner && !$isOwnPlayer) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        if (!$stage->players()->where('player_id', $player->id)->exists()) {
            return response()->json(['message' => 'Jogador não está inscrito nesta etapa.'], 404);
        }

        $stage->players()->detach($player->id);

        return response()->json(['message' => 'Inscrição removida com sucesso.']);
    }

    private function resolveStatus(LeagueStage $stage): string
    {
        if (!$stage->vagas) {
            return 'registered';
        }

        $ocupadas = $stage->players()
            ->wherePivotIn('player_status', ['registered', 'confirmed'])
            ->count();

        return $ocupadas >= $stage->vagas ? 'alternate' : 'registered';
    }
}
