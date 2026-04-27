<?php

namespace App\Http\Controllers;

use App\Http\Resources\LeagueStageRankingResource;
use App\Models\Arena;
use App\Models\League;
use App\Models\LeagueStage;
use App\Models\LeagueStageMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeagueStageRankingController extends Controller
{
    public function index(Arena $arena, League $league, LeagueStage $stage): AnonymousResourceCollection|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $rankings = $stage->rankings()
            ->with(['registration.player', 'registration.partner', 'registration.groupRegistration'])
            ->orderBy('position')
            ->orderByDesc('wins')
            ->orderByRaw('(CAST(games_pro AS SIGNED) - CAST(games_against AS SIGNED)) DESC')
            ->orderByDesc('games_pro')
            ->orderBy('games_against')
            ->get();

        return LeagueStageRankingResource::collection($rankings);
    }

    public function store(Arena $arena, League $league, LeagueStage $stage): AnonymousResourceCollection|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        // ── 1. Validate final was played ─────────────────────────────────────
        $matches      = $stage->playoffMatches()->get();
        $finalMatch   = $matches->firstWhere('round_name', 'final');
        if (!$finalMatch || !$finalMatch->winner_pair_id) {
            return response()->json(['message' => 'A final ainda não foi disputada.'], 422);
        }

        // ── 2. Map pair_id → playoff tier (1-4 podium, 5 classified) ─────────
        $pairTier = [];

        $pairTier[$finalMatch->winner_pair_id] = 1;
        $finalLoserId = $finalMatch->pair1_id === $finalMatch->winner_pair_id
            ? $finalMatch->pair2_id : $finalMatch->pair1_id;
        $pairTier[$finalLoserId] = 2;

        $terceiroMatch = $matches->firstWhere('round_name', 'terceiro');
        $hasTerceiro   = $terceiroMatch && $terceiroMatch->winner_pair_id;

        if ($hasTerceiro) {
            $pairTier[$terceiroMatch->winner_pair_id] = 3;
            $tercLoserId = $terceiroMatch->pair1_id === $terceiroMatch->winner_pair_id
                ? $terceiroMatch->pair2_id : $terceiroMatch->pair1_id;
            $pairTier[$tercLoserId] = 4;
        } else {
            foreach ($matches->where('round_name', 'semi') as $semi) {
                if ($semi->winner_pair_id) {
                    $loserId = $semi->pair1_id === $semi->winner_pair_id
                        ? $semi->pair2_id : $semi->pair1_id;
                    $pairTier[$loserId] = 3; // both semi-losers share 3rd
                }
            }
        }

        $allPairs = $stage->playoffPairs()->with(['finalist1', 'finalist2'])->get();
        foreach ($allPairs as $pair) {
            $pairTier[$pair->id] ??= 5; // classified
        }

        // Points per tier
        $tierPoints = [
            1 => $stage->pontuacao_1,
            2 => $stage->pontuacao_2,
            3 => $stage->pontuacao_3,
            4 => $stage->pontuacao_4,
            5 => $stage->pontuacao_classificados,
        ];
        // Without terceiro dispute, both 3rd-tier pairs get pontuacao_3
        $getPoints = fn($tier) => match(true) {
            $tier === 4 && !$hasTerceiro => $stage->pontuacao_3,
            default                      => $tierPoints[$tier] ?? $stage->pontuacao_classificados,
        };

        // registration_id → [tier, points] for playoff finalists
        $playoffRegData = [];
        foreach ($allPairs as $pair) {
            $tier = $pairTier[$pair->id];
            $pts  = $getPoints($tier);
            foreach ([$pair->finalist1, $pair->finalist2] as $finalist) {
                if ($finalist) {
                    $playoffRegData[$finalist->registration_id] = ['tier' => $tier, 'points' => $pts];
                }
            }
        }

        // ── 3. Group-stage stats for ALL registrations ────────────────────────
        $groupIds    = $stage->groups()->pluck('id');
        $groupMatches = LeagueStageMatch::whereIn('group_id', $groupIds)
            ->whereNotNull('score_p')->whereNotNull('score_q')->get();

        $stats = [];
        foreach ($groupMatches as $m) {
            $pWon = $m->score_p > $m->score_q;
            foreach ([$m->p1_registration_id, $m->p2_registration_id] as $regId) {
                if (!$regId) continue;
                $stats[$regId] ??= ['wins' => 0, 'matches' => 0, 'gp' => 0, 'gc' => 0];
                $stats[$regId]['matches']++;
                $stats[$regId]['gp'] += $m->score_p;
                $stats[$regId]['gc'] += $m->score_q;
                if ($pWon) $stats[$regId]['wins']++;
            }
            foreach ([$m->q1_registration_id, $m->q2_registration_id] as $regId) {
                if (!$regId) continue;
                $stats[$regId] ??= ['wins' => 0, 'matches' => 0, 'gp' => 0, 'gc' => 0];
                $stats[$regId]['matches']++;
                $stats[$regId]['gp'] += $m->score_q;
                $stats[$regId]['gc'] += $m->score_p;
                if (!$pWon) $stats[$regId]['wins']++;
            }
        }

        // ── 4. Bucket all registrations ───────────────────────────────────────
        $allRegIds = $stage->registrations()->pluck('id')->all();

        $podium     = []; // tier 1-4
        $classified = []; // tier 5
        $groupOnly  = []; // not in playoffs

        foreach ($allRegIds as $regId) {
            if (isset($playoffRegData[$regId])) {
                $tier = $playoffRegData[$regId]['tier'];
                if ($tier <= 4) {
                    $podium[$regId] = $playoffRegData[$regId];
                } else {
                    $classified[$regId] = $playoffRegData[$regId];
                }
            } else {
                $groupOnly[] = $regId;
            }
        }

        // Sort classified and group-only by group-stage performance
        $sortByStats = function ($aId, $bId) use ($stats) {
            $a = $stats[$aId] ?? ['wins' => 0, 'gp' => 0, 'gc' => 0];
            $b = $stats[$bId] ?? ['wins' => 0, 'gp' => 0, 'gc' => 0];
            if ($b['wins'] !== $a['wins'])            return $b['wins'] - $a['wins'];
            $sgA = $a['gp'] - $a['gc'];
            $sgB = $b['gp'] - $b['gc'];
            if ($sgB !== $sgA)                        return $sgB - $sgA;
            if ($b['gp'] !== $a['gp'])                return $b['gp'] - $a['gp'];
            return $a['gc'] - $b['gc'];
        };

        $classifiedIds = array_keys($classified);
        usort($classifiedIds, $sortByStats);
        usort($groupOnly, $sortByStats);

        // ── 5. Assign final positions & persist ───────────────────────────────
        $stage->rankings()->delete();

        $save = function ($regId, $position, $points) use ($stage, $stats) {
            $s = $stats[$regId] ?? ['wins' => 0, 'matches' => 0, 'gp' => 0, 'gc' => 0];
            $stage->rankings()->create([
                'registration_id' => $regId,
                'position'        => $position,
                'points'          => $points,
                'wins'            => $s['wins'],
                'matches_played'  => $s['matches'],
                'games_pro'       => $s['gp'],
                'games_against'   => $s['gc'],
            ]);
        };

        // Podium (fixed positions 1-4)
        foreach ($podium as $regId => $data) {
            $save($regId, $data['tier'], $data['points']);
        }

        // Classified (positions 5, 6, 7…)
        $nextPos = 5;
        foreach ($classifiedIds as $regId) {
            $save($regId, $nextPos++, $classified[$regId]['points']);
        }

        // Group-stage only (positions continuing after classified)
        foreach ($groupOnly as $regId) {
            $save($regId, $nextPos++, $stage->pontuacao_fase_grupo);
        }

        // ── 6. Close stage ────────────────────────────────────────────────────
        $stage->update(['stage_status' => 'closed']);

        return $this->index($arena, $league, $stage);
    }
}
