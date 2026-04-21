<?php

namespace App\Http\Controllers;

use App\Models\Arena;
use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;

class ArenaDashboardController extends Controller
{
    /**
     * Get dashboard statistics for a specific arena.
     *
     * @param int $arenaId
     * @return JsonResponse
     */
    public function index($arenaId): JsonResponse
    {
        // Verify arena existence
        $arena = Arena::findOrFail($arenaId);

        $totalTournaments = Tournament::where('arena_id', $arenaId)->count();
        $totalPlayers = Player::where('arena_id', $arenaId)->count();
        
        // Considering "active" anything that is not finished
        $activeTournaments = Tournament::where('arena_id', $arenaId)
            ->where('status', '!=', 'finished')
            ->count();

        // Recent activities: last 5 tournaments or major events
        $recentActivities = Tournament::where('arena_id', $arenaId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($tournament) {
                return [
                    'type' => 'tournament_created',
                    'description' => "Novo torneio '{$tournament->name}' criado.",
                    'date' => $tournament->created_at->diffForHumans(),
                    'data' => $tournament
                ];
            });

        return response()->json([
            'total_tournaments' => $totalTournaments,
            'total_players' => $totalPlayers,
            'active_tournaments' => $activeTournaments,
            'recent_activities' => $recentActivities,
        ]);
    }
}
