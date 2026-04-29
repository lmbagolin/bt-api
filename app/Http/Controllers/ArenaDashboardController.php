<?php

namespace App\Http\Controllers;

use App\Models\Arena;
use App\Models\LeagueStageMatch;
use Illuminate\Http\JsonResponse;

class ArenaDashboardController extends Controller
{
    public function index(Arena $arena): JsonResponse
    {
        $this->authorizeArenaOwner($arena);

        $arena->load([
            'leagues.stages.registrations',
            'leagues.stages.groups',
        ]);

        $leagues = $arena->leagues;

        // ── Global stats ─────────────────────────────────────────────────────
        $totalLeagues  = $leagues->count();
        $totalPlayers  = $arena->players()->count();

        // Active leagues: have at least one non-closed stage
        $activeLeagues = $leagues->filter(function ($league) {
            return $league->stages->contains(
                fn ($s) => $s->stage_status !== 'closed'
            );
        })->count();

        // Total group matches with scores across all leagues in this arena
        $stageIds = $leagues->flatMap(fn ($l) => $l->stages->pluck('id'));
        $totalMatchesPlayed = LeagueStageMatch::whereHas('group', fn ($q) =>
            $q->whereIn('league_stage_id', $stageIds)
        )->whereNotNull('score_p')->whereNotNull('score_q')->count();

        // Active stages count
        $totalActiveStages = $leagues->flatMap(fn ($l) => $l->stages)
            ->filter(fn ($s) => !in_array($s->stage_status, ['closed', 'created']))->count();

        // ── Per-league summary ────────────────────────────────────────────────
        $leagueSummaries = $leagues->map(function ($league) {
            $stages      = $league->stages;
            $totalStages = $stages->count();
            $closedStages = $stages->where('stage_status', 'closed')->count();

            // Most recent non-closed stage
            $activeStage = $stages
                ->where('stage_status', '!=', 'closed')
                ->sortByDesc('data_etapa')
                ->first();

            // Latest closed stage for last result
            $lastClosed = $stages
                ->where('stage_status', 'closed')
                ->sortByDesc('data_etapa')
                ->first();

            return [
                'id'                  => $league->id,
                'nome'                => $league->nome,
                'total_stages'        => $totalStages,
                'closed_stages'       => $closedStages,
                'active_stage'        => $activeStage ? [
                    'id'           => $activeStage->id,
                    'tipo'         => $activeStage->tipo,
                    'data_etapa'   => $activeStage->data_etapa,
                    'stage_status' => $activeStage->stage_status,
                    'registrations'=> $activeStage->registrations->count(),
                ] : null,
                'last_closed_stage'   => $lastClosed ? [
                    'id'         => $lastClosed->id,
                    'tipo'       => $lastClosed->tipo,
                    'data_etapa' => $lastClosed->data_etapa,
                ] : null,
            ];
        })->values();

        return response()->json([
            'total_leagues'        => $totalLeagues,
            'active_leagues'       => $activeLeagues,
            'total_players'        => $totalPlayers,
            'total_matches_played' => $totalMatchesPlayed,
            'total_active_stages'  => $totalActiveStages,
            'leagues'              => $leagueSummaries,
        ]);
    }
}
