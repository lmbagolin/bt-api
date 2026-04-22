<?php

namespace App\Http\Controllers;

use App\Http\Resources\LeagueStageRegistrationResource;
use App\Models\Arena;
use App\Models\League;
use App\Models\LeagueStage;
use App\Models\LeagueStageRegistration;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class LeagueStageRegistrationController extends Controller
{
    // -------------------------------------------------------------------------
    // Público: jogador se inscreve na etapa
    // -------------------------------------------------------------------------

    public function selfRegister(Request $request, League $league, LeagueStage $stage): JsonResponse
    {
        $player = $request->user()->player;

        if (!$player) {
            return response()->json(['message' => 'Perfil de jogador não encontrado. Preencha seus dados antes de se inscrever.'], 422);
        }

        if ($stage->registrations()->where('player_id', $player->id)->exists()) {
            return response()->json(['message' => 'Você já está inscrito nesta etapa.'], 422);
        }

        $status = $this->resolveStatus($stage);

        $registration = $stage->registrations()->create([
            'player_id' => $player->id,
            'status'    => $status,
        ]);

        $registration->load('player');

        $message = $status === 'waitlist'
            ? 'Vagas esgotadas. Você foi adicionado à lista de espera.'
            : 'Inscrição realizada com sucesso! Aguarde a confirmação.';

        return response()->json([
            'message' => $message,
            'data'    => new LeagueStageRegistrationResource($registration),
        ], 201);
    }

    // -------------------------------------------------------------------------
    // Admin: listar inscrições
    // -------------------------------------------------------------------------

    public function index(Arena $arena, League $league, LeagueStage $stage): AnonymousResourceCollection|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $registrations = $stage->registrations()->with('player')->get();

        return LeagueStageRegistrationResource::collection($registrations);
    }

    // -------------------------------------------------------------------------
    // Admin: criar inscrição manual
    // -------------------------------------------------------------------------

    public function store(Request $request, Arena $arena, League $league, LeagueStage $stage): LeagueStageRegistrationResource|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'nickname'      => ['nullable', 'string', 'max:255'],
            'gender'        => ['nullable', 'string', 'max:50'],
            'level'         => ['nullable', 'string', 'max:100'],
            'whatsapp'      => ['nullable', 'string', 'max:50'],
            'status'        => ['nullable', Rule::in(['pending', 'confirmed', 'waitlist', 'cancelled'])],
            'valor_pago'    => ['nullable', 'numeric', 'min:0'],
            'posicao_grupo' => ['nullable', 'integer', 'min:1'],
            'observacoes'   => ['nullable', 'string'],
        ]);

        // Busca player existente nesta arena pelo nome, ou cria novo
        $player = $arena->players()
            ->where('name', $request->name)
            ->first();

        if (!$player) {
            $player = Player::create(array_filter([
                'name'     => $request->name,
                'nickname' => $request->nickname,
                'gender'   => $request->gender,
                'level'    => $request->level,
                'whatsapp' => $request->whatsapp,
            ], fn ($v) => !is_null($v)));

            $arena->players()->syncWithoutDetaching([$player->id]);
        }

        if ($stage->registrations()->where('player_id', $player->id)->exists()) {
            return response()->json(['message' => 'Jogador já está inscrito nesta etapa.'], 422);
        }

        $registration = $stage->registrations()->create([
            'player_id'     => $player->id,
            'status'        => $request->status ?? $this->resolveStatus($stage),
            'valor_pago'    => $request->valor_pago,
            'posicao_grupo' => $request->posicao_grupo,
            'observacoes'   => $request->observacoes,
        ]);

        $registration->load('player');

        return new LeagueStageRegistrationResource($registration);
    }

    // -------------------------------------------------------------------------
    // Admin: editar inscrição
    // -------------------------------------------------------------------------

    public function update(Request $request, Arena $arena, League $league, LeagueStage $stage, LeagueStageRegistration $registration): LeagueStageRegistrationResource|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'status'        => ['sometimes', Rule::in(['pending', 'confirmed', 'waitlist', 'cancelled'])],
            'valor_pago'    => ['nullable', 'numeric', 'min:0'],
            'posicao_grupo' => ['nullable', 'integer', 'min:1'],
            'observacoes'   => ['nullable', 'string'],
        ]);

        $registration->update($request->only(['status', 'valor_pago', 'posicao_grupo', 'observacoes']));
        $registration->load('player');

        return new LeagueStageRegistrationResource($registration);
    }

    // -------------------------------------------------------------------------
    // Admin: troca rápida de status
    // -------------------------------------------------------------------------

    public function updateStatus(Request $request, Arena $arena, League $league, LeagueStage $stage, LeagueStageRegistration $registration): LeagueStageRegistrationResource|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'waitlist', 'cancelled'])],
        ]);

        $registration->update(['status' => $request->status]);
        $registration->load('player');

        return new LeagueStageRegistrationResource($registration);
    }

    // -------------------------------------------------------------------------
    // Admin: remover inscrição
    // -------------------------------------------------------------------------

    public function destroy(Arena $arena, League $league, LeagueStage $stage, LeagueStageRegistration $registration): JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $registration->delete();

        return response()->json(['message' => 'Inscrição removida com sucesso.']);
    }

    // -------------------------------------------------------------------------

    private function resolveStatus(LeagueStage $stage): string
    {
        if (!$stage->vagas) {
            return 'pending';
        }

        $ocupadas = $stage->registrations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        return $ocupadas >= $stage->vagas ? 'waitlist' : 'pending';
    }
}
