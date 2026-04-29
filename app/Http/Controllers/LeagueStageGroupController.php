<?php

namespace App\Http\Controllers;

use App\Http\Resources\LeagueStageGroupResource;
use App\Models\Arena;
use App\Models\League;
use App\Models\LeagueStage;
use App\Models\LeagueStageGroup;
use App\Models\LeagueStageMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeagueStageGroupController extends Controller
{
    private const PLAYER_COLORS = [
        '#0284c7', '#a855f7', '#F37021', '#3AAA35', '#FAB818', '#ef4444',
        '#06b6d4', '#1B2E6E', '#f59e0b', '#ec4899', '#14b8a6', '#8b5cf6',
        '#64748b', '#0369a1', '#15803d', '#b91c1c',
    ];

    // -------------------------------------------------------------------------
    // GET /arenas/{arena}/leagues/{league}/stages/{stage}/groups
    // -------------------------------------------------------------------------
    public function index(Arena $arena, League $league, LeagueStage $stage): AnonymousResourceCollection|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $groups = $stage->groups()->with([
            'groupRegistrations.registration.player',
            'groupRegistrations.registration.partner',
            'matches',
        ])->orderBy('letter')->get();

        return LeagueStageGroupResource::collection($groups);
    }

    // -------------------------------------------------------------------------
    // POST /arenas/{arena}/leagues/{league}/stages/{stage}/groups/draw
    // -------------------------------------------------------------------------
    public function draw(Arena $arena, League $league, LeagueStage $stage): AnonymousResourceCollection|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $registrations = $stage->registrations()->with(['player', 'partner'])->get();
        $confirmed     = $registrations->filter(fn ($r) => $r->status === 'confirmed');
        $pool          = ($confirmed->count() >= 4 ? $confirmed : $registrations)->values();

        if ($pool->count() < 4) {
            return response()->json([
                'message' => 'São necessários pelo menos 4 jogadores inscritos para sortear grupos.',
            ], 422);
        }

        // Delete current groups (cascades to group_registrations and matches)
        $stage->groups()->delete();

        $shuffled  = $pool->shuffle();
        $groupSize = $stage->jogadores_por_grupo ?: 4;
        $letters   = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $groupCount = (int) ceil($shuffled->count() / $groupSize);
        $colorIdx  = 0;

        for ($gi = 0; $gi < $groupCount && $gi < count($letters); $gi++) {
            $gRegs = $shuffled->slice($gi * $groupSize, $groupSize)->values();

            if ($gRegs->count() < 4) {
                continue;
            }

            $group = $stage->groups()->create(['letter' => $letters[$gi]]);

            // Assign players with colors and build a local map
            $colorMap = [];
            foreach ($gRegs as $reg) {
                $color = self::PLAYER_COLORS[$colorIdx % count(self::PLAYER_COLORS)];
                $group->groupRegistrations()->create([
                    'registration_id' => $reg->id,
                    'color'           => $color,
                ]);
                $colorMap[$reg->id] = $color;
                $colorIdx++;
            }

            if ($stage->tipo === 'dupla-fixa') {
                // Round-robin completo: cada dupla joga contra todas as outras
                // C(n,2) jogos — cada lado tem UMA dupla (d1_player2/d2_player2 = null)
                $n = $gRegs->count();
                $matchNum = 1;
                for ($i = 0; $i < $n; $i++) {
                    for ($j = $i + 1; $j < $n; $j++) {
                        $group->matches()->create([
                            'match_number' => $matchNum++,
                            'd1_player1_id' => $gRegs[$i]->player_id,
                            'd1_player2_id' => $gRegs[$i]->partner_player_id,
                            'd2_player1_id' => $gRegs[$j]->player_id,
                            'd2_player2_id' => $gRegs[$j]->partner_player_id,
                        ]);
                    }
                }
            } else {
                // Formato americano: 3 jogos com duplas rotativas para 4 jogadores
                $matchDefs = [
                    [0, 1, 2, 3],
                    [0, 2, 1, 3],
                    [0, 3, 1, 2],
                ];

                foreach ($matchDefs as $num => $def) {
                    $group->matches()->create([
                        'match_number'  => $num + 1,
                        'd1_player1_id' => $gRegs[$def[0]]->player_id,
                        'd1_player2_id' => $gRegs[$def[1]]->player_id,
                        'd2_player1_id' => $gRegs[$def[2]]->player_id,
                        'd2_player2_id' => $gRegs[$def[3]]->player_id,
                    ]);
                }
            }
        }

        $groups = $stage->groups()->with([
            'groupRegistrations.registration.player',
            'groupRegistrations.registration.partner',
            'matches',
        ])->orderBy('letter')->get();

        return LeagueStageGroupResource::collection($groups);
    }

    // -------------------------------------------------------------------------
    // DELETE /arenas/{arena}/leagues/{league}/stages/{stage}/groups
    // -------------------------------------------------------------------------
    public function reset(Arena $arena, League $league, LeagueStage $stage): JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $stage->groups()->delete();

        return response()->json(['message' => 'Grupos removidos com sucesso.']);
    }

    // -------------------------------------------------------------------------
    // PATCH /arenas/{arena}/leagues/{league}/stages/{stage}/groups/{group}/matches/{match}
    // -------------------------------------------------------------------------
    public function updateMatchScore(
        Request $request,
        Arena $arena,
        League $league,
        LeagueStage $stage,
        LeagueStageGroup $group,
        LeagueStageMatch $match
    ): JsonResponse {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'score_p' => ['nullable', 'integer', 'min:0', 'max:99'],
            'score_q' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);

        $match->update($request->only(['score_p', 'score_q']));

        return response()->json([
            'data' => [
                'id'      => $match->id,
                'score_p' => $match->score_p,
                'score_q' => $match->score_q,
            ],
        ]);
    }
}
