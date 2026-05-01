<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlayerResource;
use App\Http\Requests\UpdateMyPlayerRequest;
use App\Http\Requests\UpdatePlayerImageRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\FileService;

class PlayerProfileController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * Display the authenticated user's player profile.
     */
    public function show(Request $request): PlayerResource
    {
        $player = $request->user()->player()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['name' => $request->user()->name]
        );

        return new PlayerResource($player->load(['arenas.city', 'city']));
    }

    /**
     * Update the authenticated user's player profile.
     */
    public function update(UpdateMyPlayerRequest $request): PlayerResource
    {
        $player = $request->user()->player()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->validated()
        );

        return new PlayerResource($player->load(['arenas.city', 'image', 'city']));
    }

    /**
     * Update the authenticated user's profile image.
     */
    public function updateImage(UpdatePlayerImageRequest $request): PlayerResource
    {
        $player = $request->user()->player()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['name' => $request->user()->name]
        );

        if ($player->image_id) {
            $this->fileService->delete($player->image);
        }

        $file = $this->fileService->upload($request->file('image'), 'player');
        $player->update(['image_id' => $file->id]);

        return new PlayerResource($player->load(['image']));
    }
}
