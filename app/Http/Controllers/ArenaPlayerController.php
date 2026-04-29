<?php

namespace App\Http\Controllers;

use App\Models\Arena;
use App\Models\Player;
use App\Http\Requests\RegisterArenaPlayerRequest;
use App\Http\Requests\UpdateMyPlayerRequest;
use App\Http\Requests\StorePlayerRequest;
use App\Http\Resources\ArenaResource;
use App\Http\Resources\PlayerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\FileService;

class ArenaPlayerController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Display a listing of players for the given arena.
     */
    public function index(Arena $arena): AnonymousResourceCollection|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return PlayerResource::collection($arena->players()->with('city')->latest()->get());
    }

    /**
     * Store a newly created player for the given arena.
     */
    public function store(StorePlayerRequest $request, Arena $arena): PlayerResource|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $file = $this->fileService->upload($request->file('image'), 'player');
            $data['image_id'] = $file->id;
        }

        $player = null;

        if (!empty($data['user_id'])) {
            $player = Player::updateOrCreate(['user_id' => $data['user_id']], $data);
        } else {
            $player = Player::create($data);
        }

        // Attach to arena if not already attached
        $arena->players()->syncWithoutDetaching([$player->id]);

        return new PlayerResource($player);
    }

    /**
     * Update the specified player's global profile.
     */
    public function update(StorePlayerRequest $request, Arena $arena, Player $player): PlayerResource|JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        // Verify if the player is linked to this arena
        if (!$arena->players()->where('player_id', $player->id)->exists()) {
            return response()->json(['message' => 'Jogador não pertence a esta arena.'], 404);
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($player->image_id) {
                $this->fileService->delete($player->image);
            }
            $file = $this->fileService->upload($request->file('image'), 'player');
            $data['image_id'] = $file->id;
        }

        $player->update($data);

        return new PlayerResource($player);
    }

    /**
     * Remove the specified player from the given arena (detaches the relationship).
     */
    public function destroy(Arena $arena, Player $player): JsonResponse
    {
        if ($arena->owner_id !== auth()->id()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $arena->players()->detach($player->id);

        return response()->json(['message' => 'Jogador removido da arena com sucesso.']);
    }

    /**
     * Join the authenticated user's player profile to the given arena.
     */
    public function join(Request $request, Arena $arena): JsonResponse
    {
        $user = $request->user();

        // Ensure user has a player profile
        $player = $user->player;

        if (!$player) {
            // Option 1: Error 404
            // return response()->json(['message' => 'Perfil de jogador não encontrado. Preencha seus dados primeiro.'], 404);
            
            // Option 2: Auto-create basic profile (more user friendly)
            $player = Player::create([
                'user_id' => $user->id,
                'name'    => $user->name,
            ]);
        }

        // Link to arena
        $arena->players()->syncWithoutDetaching([$player->id]);

        return response()->json([
            'message' => "Você se inscreveu com sucesso na arena '{$arena->name}'!",
            'player'  => new PlayerResource($player),
        ]);
    }

    /**
     * Register the authenticated user as a player in an arena.
     */
    public function register(RegisterArenaPlayerRequest $request, Arena $arena): JsonResponse
    {
        $user = $request->user();

        // Check if user is already a player in this arena
        $alreadyRegistered = $arena->players()->where('user_id', $user->id)->exists();

        if ($alreadyRegistered) {
            return response()->json([
                'message' => 'Você já está registrado como jogador nesta arena.'
            ], 422);
        }

        $data = $request->validated();

        // Find or create global player profile for this user
        $player = Player::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name'     => $data['name'] ?? $user->name,
                'nickname' => $data['nickname'] ?? null,
                'gender'   => $data['gender'] ?? null,
                'level'    => $data['level'] ?? null,
                'city_id'  => $data['city_id'] ?? null,
            ]
        );

        // Link to arena
        $arena->players()->syncWithoutDetaching([$player->id]);

        return response()->json([
            'message' => "Registro realizado com sucesso na arena '{$arena->name}'!",
            'player'  => new PlayerResource($player),
        ]);
    }

    /**
     * Get the authenticated user's player profile in an arena.
     */
    public function myPlayer(Request $request, Arena $arena): PlayerResource|JsonResponse
    {
        $player = $request->user()->player;

        if (!$player || !$arena->players()->where('player_id', $player->id)->exists()) {
            return response()->json([
                'message' => 'Você não está registrado como jogador nesta arena.'
            ], 404);
        }

        return new PlayerResource($player);
    }

    /**
     * Update the authenticated user's global player profile.
     */
    public function updateMyPlayer(UpdateMyPlayerRequest $request, Arena $arena): PlayerResource|JsonResponse
    {
        $player = $request->user()->player;

        if (!$player) {
            return response()->json(['message' => 'Perfil de jogador não encontrado.'], 404);
        }

        $player->update($request->validated());

        return new PlayerResource($player);
    }

    /**
     * List all arenas where the user is registered as a player.
     */
    public function myRegistrations(Request $request)
    {
        $user = $request->user();

        $arenas = Arena::whereHas('players', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with('logo')->latest()->get();

        return ArenaResource::collection($arenas);
    }
}
