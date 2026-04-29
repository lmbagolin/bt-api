<?php

namespace App\Http\Controllers;

use App\Mail\FriendRequestMail;
use App\Models\Player;
use App\Models\PlayerFriend;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PlayerFriendController extends Controller
{
    private function myPlayer(): ?Player
    {
        return auth()->user()?->player;
    }

    // GET /friends — amigos confirmados
    public function index(): JsonResponse
    {
        $player = $this->myPlayer();
        if (!$player) return response()->json(['data' => []]);

        $friends = PlayerFriend::with(['requester.user', 'addressee.user'])
            ->where('status', 'accepted')
            ->where(fn($q) => $q->where('requester_id', $player->id)->orWhere('addressee_id', $player->id))
            ->get()
            ->map(fn($f) => $this->formatFriend($f, $player->id));

        return response()->json(['data' => $friends]);
    }

    // GET /friends/requests — pedidos recebidos pendentes
    public function requests(): JsonResponse
    {
        $player = $this->myPlayer();
        if (!$player) return response()->json(['data' => []]);

        $requests = PlayerFriend::with(['requester.user'])
            ->where('addressee_id', $player->id)
            ->where('status', 'pending')
            ->get()
            ->map(fn($f) => [
                'id'             => $f->id,
                'status'         => $f->status,
                'created_at'     => $f->created_at,
                'requester'      => $this->formatPlayer($f->requester),
            ]);

        return response()->json(['data' => $requests]);
    }

    // GET /friends/sent — pedidos enviados pendentes
    public function sent(): JsonResponse
    {
        $player = $this->myPlayer();
        if (!$player) return response()->json(['data' => []]);

        $sent = PlayerFriend::with(['addressee.user'])
            ->where('requester_id', $player->id)
            ->where('status', 'pending')
            ->get()
            ->map(fn($f) => [
                'id'         => $f->id,
                'status'     => $f->status,
                'created_at' => $f->created_at,
                'addressee'  => $this->formatPlayer($f->addressee),
            ]);

        return response()->json(['data' => $sent]);
    }

    // POST /friends — enviar pedido por e-mail
    public function store(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $myPlayer = $this->myPlayer();
        if (!$myPlayer) return response()->json(['message' => 'Perfil de jogador não encontrado.'], 422);

        $targetUser = User::where('email', $request->email)->first();
        if (!$targetUser) {
            return response()->json(['message' => 'Nenhum jogador encontrado com este e-mail.'], 404);
        }

        $targetPlayer = $targetUser->player;
        if (!$targetPlayer) {
            return response()->json(['message' => 'Este usuário ainda não configurou seu perfil de jogador.'], 422);
        }

        if ($targetPlayer->id === $myPlayer->id) {
            return response()->json(['message' => 'Você não pode adicionar a si mesmo.'], 422);
        }

        $existing = PlayerFriend::where(fn($q) =>
            $q->where('requester_id', $myPlayer->id)->where('addressee_id', $targetPlayer->id)
        )->orWhere(fn($q) =>
            $q->where('requester_id', $targetPlayer->id)->where('addressee_id', $myPlayer->id)
        )->first();

        if ($existing) {
            if ($existing->status === 'accepted') {
                return response()->json(['message' => 'Vocês já são amigos.'], 422);
            }
            return response()->json(['message' => 'Já existe um pedido pendente entre vocês.'], 422);
        }

        $token    = Str::random(64);
        $expireAt = now()->addDays(7);

        $friendship = PlayerFriend::create([
            'requester_id'     => $myPlayer->id,
            'addressee_id'     => $targetPlayer->id,
            'status'           => 'pending',
            'token'            => $token,
            'token_expires_at' => $expireAt,
        ]);

        $friendship->load(['requester', 'addressee']);

        $acceptUrl = config('app.frontend_url') . '/amigos/aceitar/' . $token;
        Mail::to($targetUser->email)->send(new FriendRequestMail($friendship, $acceptUrl));

        return response()->json(['message' => 'Pedido de amizade enviado com sucesso!'], 201);
    }

    // POST /friends/{id}/accept — aceitar autenticado
    public function accept(PlayerFriend $friend): JsonResponse
    {
        $player = $this->myPlayer();
        if ($friend->addressee_id !== $player?->id) abort(403);
        if ($friend->status !== 'pending') return response()->json(['message' => 'Pedido não está pendente.'], 422);

        $friend->update(['status' => 'accepted', 'token' => null, 'token_expires_at' => null]);

        return response()->json(['message' => 'Amizade confirmada!']);
    }

    // POST /friends/{id}/reject — rejeitar autenticado
    public function reject(PlayerFriend $friend): JsonResponse
    {
        $player = $this->myPlayer();
        if ($friend->addressee_id !== $player?->id) abort(403);
        if ($friend->status !== 'pending') return response()->json(['message' => 'Pedido não está pendente.'], 422);

        $friend->update(['status' => 'rejected', 'token' => null, 'token_expires_at' => null]);

        return response()->json(['message' => 'Pedido recusado.']);
    }

    // DELETE /friends/{id} — desfazer amizade / cancelar pedido
    public function destroy(PlayerFriend $friend): JsonResponse
    {
        $player = $this->myPlayer();
        $isInvolved = $friend->requester_id === $player?->id || $friend->addressee_id === $player?->id;
        if (!$isInvolved) abort(403);

        $friend->delete();

        return response()->json(['message' => 'Amizade removida.']);
    }

    // GET /friends/token/{token} — info pública do pedido (sem auth)
    public function showByToken(string $token): JsonResponse
    {
        $friendship = PlayerFriend::with(['requester', 'addressee'])
            ->where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            return response()->json(['message' => 'Pedido não encontrado ou já processado.'], 404);
        }

        if ($friendship->token_expires_at && $friendship->token_expires_at->isPast()) {
            return response()->json(['message' => 'Este link expirou.'], 410);
        }

        return response()->json([
            'data' => [
                'requester_name' => $friendship->requester->name ?? '—',
                'addressee_name' => $friendship->addressee->name ?? '—',
            ],
        ]);
    }

    // POST /friends/token/{token} — aceitar via link do e-mail (sem auth)
    public function acceptByToken(string $token): JsonResponse
    {
        $friendship = PlayerFriend::where('token', $token)->where('status', 'pending')->first();

        if (!$friendship) {
            return response()->json(['message' => 'Pedido não encontrado ou já processado.'], 404);
        }

        if ($friendship->token_expires_at && $friendship->token_expires_at->isPast()) {
            return response()->json(['message' => 'Este link expirou. Peça um novo convite.'], 410);
        }

        $friendship->update(['status' => 'accepted', 'token' => null, 'token_expires_at' => null]);

        return response()->json(['message' => 'Amizade confirmada! Bem-vindo ao BT Tournament.']);
    }

    private function formatFriend(PlayerFriend $f, int $myPlayerId): array
    {
        $friend = $f->requester_id === $myPlayerId ? $f->addressee : $f->requester;
        return [
            'id'         => $f->id,
            'status'     => $f->status,
            'created_at' => $f->created_at,
            'friend'     => $this->formatPlayer($friend),
        ];
    }

    private function formatPlayer(?Player $player): array
    {
        if (!$player) return [];
        return [
            'id'        => $player->id,
            'name'      => $player->name,
            'nickname'  => $player->nickname,
            'level'     => $player->level,
            'gender'    => $player->gender,
            'city'      => $player->city,
            'image_url' => $player->image?->url ?? null,
        ];
    }
}
