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
        $isDupla = $stage->tipo === 'dupla-fixa';

        if (!$player) {
            return response()->json(['message' => 'Perfil de jogador não encontrado. Preencha seus dados antes de se inscrever.'], 422);
        }

        if (!$this->isGenderAllowed($league->genero, $player->gender)) {
            $generoLabel = ['masculino' => 'Masculino', 'feminino' => 'Feminino'][$league->genero] ?? $league->genero;
            return response()->json(['message' => "Esta liga é exclusiva para o gênero {$generoLabel}. Seu perfil não corresponde ao gênero da liga."], 422);
        }

        if ($stage->registrations()->where('player_id', $player->id)->exists()) {
            return response()->json(['message' => 'Você já está inscrito nesta etapa.'], 422);
        }

        $partnerPlayerId = null;
        $partnerName     = null;

        if ($isDupla) {
            $request->validate([
                'partner_player_id' => ['nullable', 'exists:players,id'],
                'partner_name'      => ['required_without:partner_player_id', 'nullable', 'string', 'max:255'],
            ]);

            if ($request->partner_player_id) {
                $partner = Player::find($request->partner_player_id);
                if (!$this->isGenderAllowed($league->genero, $partner?->gender)) {
                    $generoLabel = ['masculino' => 'Masculino', 'feminino' => 'Feminino'][$league->genero] ?? $league->genero;
                    return response()->json(['message' => "O parceiro não corresponde ao gênero {$generoLabel} da liga."], 422);
                }
                $partnerPlayerId = $partner->id;
            } else {
                $partnerName = $request->partner_name;
            }
        }

        $status = $this->resolveStatus($stage);

        $registration = $stage->registrations()->create([
            'player_id'         => $player->id,
            'partner_player_id' => $partnerPlayerId,
            'partner_name'      => $partnerName,
            'status'            => $status,
        ]);

        $registration->load(['player', 'partner']);

        $message = $status === 'waitlist'
            ? 'Vagas esgotadas. Você foi adicionado à lista de espera.'
            : 'Inscrição realizada com sucesso! Aguarde a confirmação.';

        return response()->json([
            'message' => $message,
            'data'    => new LeagueStageRegistrationResource($registration),
        ], 201);
    }

    // -------------------------------------------------------------------------
    // Público: listar inscrições da etapa
    // -------------------------------------------------------------------------

    public function publicIndex(League $league, LeagueStage $stage): AnonymousResourceCollection
    {
        $registrations = $stage->registrations()->with(['player', 'partner'])->orderBy('created_at')->get();

        return LeagueStageRegistrationResource::collection($registrations);
    }

    // -------------------------------------------------------------------------
    // Admin: listar inscrições
    // -------------------------------------------------------------------------

    public function index(Arena $arena, League $league, LeagueStage $stage): AnonymousResourceCollection|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $registrations = $stage->registrations()->with(['player', 'partner'])->orderBy('created_at')->get();

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

        $isDupla = $stage->tipo === 'dupla-fixa';

        $request->validate([
            'player_id'         => ['nullable', 'exists:players,id'],
            'name'              => ['required_without:player_id', 'nullable', 'string', 'max:255'],
            'nickname'          => ['nullable', 'string', 'max:255'],
            'gender'            => ['nullable', 'string', 'max:50'],
            'level'             => ['nullable', 'string', 'max:100'],
            'whatsapp'          => ['nullable', 'string', 'max:50'],
            'partner_player_id' => ['nullable', 'exists:players,id'],
            'partner_name'      => ['nullable', 'string', 'max:255'],
            'status'            => ['nullable', Rule::in(['pending', 'confirmed', 'waitlist', 'cancelled'])],
            'valor_pago'        => ['nullable', 'numeric', 'min:0'],
            'posicao_grupo'     => ['nullable', 'integer', 'min:1'],
            'observacoes'       => ['nullable', 'string'],
        ]);

        // Resolve o jogador principal
        if ($request->player_id) {
            $player = Player::find($request->player_id);
        } else {
            $player = $arena->players()->where('name', $request->name)->first();
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
        }

        if (!$this->isGenderAllowed($league->genero, $player->gender)) {
            $generoLabel = ['masculino' => 'Masculino', 'feminino' => 'Feminino'][$league->genero] ?? $league->genero;
            return response()->json(['message' => "Esta liga é exclusiva para o gênero {$generoLabel}. O jogador não corresponde ao gênero da liga."], 422);
        }

        if ($stage->registrations()->where('player_id', $player->id)->exists()) {
            return response()->json(['message' => 'Jogador já está inscrito nesta etapa.'], 422);
        }

        // Resolve o parceiro (apenas para dupla-fixa)
        $partnerPlayerId = null;
        $partnerName     = null;

        if ($isDupla) {
            if ($request->partner_player_id) {
                $partner = Player::find($request->partner_player_id);
                if (!$this->isGenderAllowed($league->genero, $partner?->gender)) {
                    $generoLabel = ['masculino' => 'Masculino', 'feminino' => 'Feminino'][$league->genero] ?? $league->genero;
                    return response()->json(['message' => "O parceiro não corresponde ao gênero {$generoLabel} da liga."], 422);
                }
                $partnerPlayerId = $partner->id;
            } elseif ($request->partner_name) {
                $partnerName = $request->partner_name;
            }
        }

        $registration = $stage->registrations()->create([
            'player_id'         => $player->id,
            'partner_player_id' => $partnerPlayerId,
            'partner_name'      => $partnerName,
            'status'            => $request->status ?? $this->resolveStatus($stage),
            'valor_pago'        => $request->valor_pago,
            'posicao_grupo'     => $request->posicao_grupo,
            'observacoes'       => $request->observacoes,
        ]);

        $registration->load(['player', 'partner']);

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
        $registration->load(['player', 'partner']);

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

    private function isGenderAllowed(?string $leagueGenero, ?string $playerGender): bool
    {
        return match ($leagueGenero) {
            'masculino' => $playerGender === 'male',
            'feminino'  => $playerGender === 'female',
            default     => true,
        };
    }

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
