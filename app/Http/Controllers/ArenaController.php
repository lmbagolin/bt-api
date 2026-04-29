<?php

namespace App\Http\Controllers;

use App\Models\Arena;
use App\Http\Requests\StoreArenaRequest;
use App\Http\Requests\UpdateArenaRequest;
use App\Http\Resources\ArenaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Services\FileService;

class ArenaController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * List all arenas of the authenticated user.
     */
    public function index(Request $request)
    {
        $arenas = $request->user()->arenas()->with(['logo', 'city'])->get();
        return ArenaResource::collection($arenas);
    }

    /**
     * List all arenas for discovery.
     */
    public function publicIndex(Request $request)
    {
        $query = Arena::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        return ArenaResource::collection($query->with(['logo', 'city'])->latest()->get());
    }

    /**
     * Create a new arena for the authenticated user.
     */
    public function store(StoreArenaRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $file = $this->fileService->upload($request->file('logo'), 'arena');
            $data['logo_id'] = $file->id;
        }

        $arena = $request->user()->arenas()->create($data);
        return new ArenaResource($arena->load(['logo', 'city']));
    }

    /**
     * Display the specified arena.
     */
    public function show(Request $request, Arena $arena)
    {
        if ($arena->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return new ArenaResource($arena);
    }

    /**
     * Update the specified arena.
     */
    public function update(UpdateArenaRequest $request, Arena $arena)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($arena->logo_id) {
                $this->fileService->delete($arena->logo);
            }
            $file = $this->fileService->upload($request->file('logo'), 'arena');
            $data['logo_id'] = $file->id;
        }

        $arena->update($data);
        return new ArenaResource($arena->load(['logo', 'city']));
    }

    /**
     * Remove the specified arena.
     */
    public function destroy(Request $request, Arena $arena)
    {
        if ($arena->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        if ($arena->logo_id) {
            $this->fileService->delete($arena->logo);
        }

        $arena->delete();
        return response()->noContent();
    }
}
