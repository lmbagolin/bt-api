<?php

namespace App\Http\Controllers;

use App\Http\Resources\LeagueStagePlayoffMatchResource;
use App\Http\Resources\LeagueStagePlayoffPairResource;
use App\Models\Arena;
use App\Models\League;
use App\Models\LeagueStage;
use App\Models\LeagueStagePlayoffMatch;
use App\Models\LeagueStagePlayoffPair;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeagueStagePlayoffController extends Controller
{
    private function pairEagerLoad(): array
    {
        return [
            'finalist1.registration.player',
            'finalist1.registration.partner',
            'finalist1.group.groupRegistrations',
            'finalist2.registration.player',
            'finalist2.registration.partner',
            'finalist2.group.groupRegistrations',
        ];
    }

    // ── Pairs ─────────────────────────────────────────────────────────────────

    public function indexPairs(Arena $arena, League $league, LeagueStage $stage): AnonymousResourceCollection|JsonResponse
    {
        $this->authorizeStage($arena, $league, $stage);

        $pairs = $stage->playoffPairs()
            ->with($this->pairEagerLoad())
            ->orderBy('pair_rank')
            ->get();

        return LeagueStagePlayoffPairResource::collection($pairs);
    }

    public function storePairs(Request $request, Arena $arena, League $league, LeagueStage $stage): AnonymousResourceCollection|JsonResponse
    {
        $this->authorizeStage($arena, $league, $stage);

        $request->validate([
            'pairs'                  => ['required', 'array', 'min:1'],
            'pairs.*.finalist1_id'   => ['required', 'integer', 'exists:league_stage_finalists,id'],
            'pairs.*.finalist2_id'   => ['nullable', 'integer', 'exists:league_stage_finalists,id'],
            'pairs.*.pair_rank'      => ['required', 'integer', 'min:1'],
            'pairs.*.pts_total'      => ['required', 'integer', 'min:0'],
            'pairs.*.gp_total'       => ['required', 'integer', 'min:0'],
            'pairs.*.gc_total'       => ['required', 'integer', 'min:0'],
            'pairs.*.position_sum'   => ['required', 'integer', 'min:0'],
        ]);

        // Reset pairs and all bracket matches
        $stage->playoffMatches()->delete();
        $stage->playoffPairs()->delete();

        foreach ($request->pairs as $item) {
            $stage->playoffPairs()->create($item);
        }

        $pairs = $stage->playoffPairs()->with($this->pairEagerLoad())->orderBy('pair_rank')->get();

        return LeagueStagePlayoffPairResource::collection($pairs);
    }

    // ── Bracket matches ───────────────────────────────────────────────────────

    public function indexMatches(Arena $arena, League $league, LeagueStage $stage): AnonymousResourceCollection|JsonResponse
    {
        $this->authorizeStage($arena, $league, $stage);

        $matches = $stage->playoffMatches()
            ->with([
                'pair1' => fn ($q) => $q->with($this->pairEagerLoad()),
                'pair2' => fn ($q) => $q->with($this->pairEagerLoad()),
            ])
            ->orderByRaw("FIELD(round_name,'oitavas','quartas','semi','terceiro','final')")
            ->orderBy('match_number')
            ->get();

        return LeagueStagePlayoffMatchResource::collection($matches);
    }

    public function storeMatches(Request $request, Arena $arena, League $league, LeagueStage $stage): AnonymousResourceCollection|JsonResponse
    {
        $this->authorizeStage($arena, $league, $stage);

        $request->validate([
            'matches'                  => ['required', 'array'],
            'matches.*.round_name'     => ['required', 'string'],
            'matches.*.match_number'   => ['required', 'integer', 'min:1'],
            'matches.*.pair1_id'       => ['nullable', 'integer', 'exists:league_stage_playoff_pairs,id'],
            'matches.*.pair2_id'       => ['nullable', 'integer', 'exists:league_stage_playoff_pairs,id'],
            'matches.*.is_bye'         => ['boolean'],
            'matches.*.winner_pair_id' => ['nullable', 'integer', 'exists:league_stage_playoff_pairs,id'],
        ]);

        $stage->playoffMatches()->delete();

        foreach ($request->matches as $item) {
            $stage->playoffMatches()->create([
                'round_name'     => $item['round_name'],
                'match_number'   => $item['match_number'],
                'pair1_id'       => $item['pair1_id'] ?? null,
                'pair2_id'       => $item['pair2_id'] ?? null,
                'is_bye'         => $item['is_bye'] ?? false,
                'winner_pair_id' => $item['winner_pair_id'] ?? null,
                'score_p'        => null,
                'score_q'        => null,
            ]);
        }

        return $this->indexMatches($arena, $league, $stage);
    }

    public function updateMatch(Request $request, Arena $arena, League $league, LeagueStage $stage, LeagueStagePlayoffMatch $match): JsonResponse
    {
        $this->authorizeStage($arena, $league, $stage);

        $request->validate([
            'score_p'        => ['nullable', 'integer', 'min:0'],
            'score_q'        => ['nullable', 'integer', 'min:0'],
            'winner_pair_id' => ['nullable', 'integer', 'exists:league_stage_playoff_pairs,id'],
            'pair1_id'       => ['nullable', 'integer', 'exists:league_stage_playoff_pairs,id'],
            'pair2_id'       => ['nullable', 'integer', 'exists:league_stage_playoff_pairs,id'],
        ]);

        $match->update($request->only(['score_p', 'score_q', 'winner_pair_id', 'pair1_id', 'pair2_id']));

        return response()->json(['data' => [
            'id'             => $match->id,
            'score_p'        => $match->score_p,
            'score_q'        => $match->score_q,
            'winner_pair_id' => $match->winner_pair_id,
            'pair1_id'       => $match->pair1_id,
            'pair2_id'       => $match->pair2_id,
        ]]);
    }
}
