<?php

namespace App\Services;

use App\Models\Building;
use Illuminate\Http\JsonResponse;

class BuildingService
{
    public function index(int $perPage = 20, int $page = 1): JsonResponse
    {
        $buildings = Building::select(['id', 'address', 'latitude', 'longitude'])
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($buildings->items());
    }

    public function show(int $id): JsonResponse
    {
        $building = Building::findOrFail($id);

        return response()->json([
            'id' => $building->id,
            'address' => $building->address,
            'latitude' => $building->latitude,
            'longitude' => $building->longitude,
        ]);
    }
}
