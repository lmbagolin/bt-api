<?php

namespace App\Http\Controllers;

use App\Models\Arena;
use App\Models\League;
use Illuminate\Http\JsonResponse;

class LeagueRankingController extends Controller
{
    public function publicIndex(League $league): JsonResponse
    {
        return $this->buildRanking($league);
    }

    public function index(Arena $arena, League $league): JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return $this->buildRanking($league);
    }

    private function buildRanking(League $league): JsonResponse
    {
        // Only stages that have been closed (ranking generated)
        $stages = $league->stages()
            ->where('stage_status', 'closed')
            ->orderBy('data_etapa')
            ->with([
                'registrations.player',
                'registrations.groupRegistration',
                'rankings.registration.player',
                'rankings.registration.groupRegistration',
            ])
            ->get();

        // ── Step 1: seed all participants (even those without playoff ranking) ──
        $playerMap = [];

        foreach ($stages as $stage) {
            foreach ($stage->registrations as $reg) {
                $player = $reg->player;
                if (!$player) continue;

                $pid = $player->id;
                if (isset($playerMap[$pid])) continue;

                $playerMap[$pid] = [
                    'player' => [
                        'id'    => $player->id,
                        'name'  => $player->name,
                        'color' => $reg->groupRegistration?->color ?? '#64748b',
                    ],
                    'total_points' => 0,
                    'total_wins'   => 0,
                    'total_gp'     => 0,
                    'total_gc'     => 0,
                    'stage_scores' => [],
                ];
            }
        }

        // ── Step 2: overlay ranking data for stages player ranked in ──────────
        foreach ($stages as $stage) {
            foreach ($stage->rankings as $ranking) {
                $reg    = $ranking->registration;
                $player = $reg?->player;
                if (!$player) continue;

                $pid = $player->id;

                // Update color with ranking-context registration (may differ from seeded)
                if (isset($playerMap[$pid])) {
                    $playerMap[$pid]['player']['color'] = $reg->groupRegistration?->color
                        ?? $playerMap[$pid]['player']['color'];
                }

                $playerMap[$pid]['stage_scores'][$stage->id] = $ranking->points;
                $playerMap[$pid]['total_points'] += $ranking->points;
                $playerMap[$pid]['total_wins']   += $ranking->wins;
                $playerMap[$pid]['total_gp']     += $ranking->games_pro;
                $playerMap[$pid]['total_gc']     += $ranking->games_against;
            }
        }

        // Sort: pts desc → wins desc → SG desc → GP desc → GC asc
        usort($playerMap, function ($a, $b) {
            if ($b['total_points'] !== $a['total_points']) return $b['total_points'] <=> $a['total_points'];
            if ($b['total_wins']   !== $a['total_wins'])   return $b['total_wins']   <=> $a['total_wins'];
            $sgA = $a['total_gp'] - $a['total_gc'];
            $sgB = $b['total_gp'] - $b['total_gc'];
            if ($sgB !== $sgA)             return $sgB <=> $sgA;
            if ($b['total_gp'] !== $a['total_gp']) return $b['total_gp'] <=> $a['total_gp'];
            return $a['total_gc'] <=> $b['total_gc'];
        });

        // Assign positions — same position only when ALL criteria are equal
        $position = 1;
        foreach ($playerMap as $i => &$entry) {
            if ($i > 0) {
                $prev = $playerMap[$i - 1];
                $allEqual =
                    $entry['total_points'] === $prev['total_points'] &&
                    $entry['total_wins']   === $prev['total_wins']   &&
                    ($entry['total_gp'] - $entry['total_gc']) === ($prev['total_gp'] - $prev['total_gc']) &&
                    $entry['total_gp']     === $prev['total_gp']     &&
                    $entry['total_gc']     === $prev['total_gc'];

                $entry['position'] = $allEqual ? $prev['position'] : $position;
            } else {
                $entry['position'] = $position;
            }
            $position++;
        }
        unset($entry);

        return response()->json([
            'data' => [
                'stages'   => $stages->map(fn ($s) => [
                    'id'         => $s->id,
                    'tipo'       => $s->tipo,
                    'data_etapa' => $s->data_etapa?->format('Y-m-d H:i'),
                ]),
                'rankings' => array_values($playerMap),
            ],
        ]);
    }
}
