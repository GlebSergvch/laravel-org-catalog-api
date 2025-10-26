<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Building;

use App\Http\Api\V1\AbstractController;
use App\Services\BuildingService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class ShowBuildingController extends AbstractController
{
    public function __construct(
        private readonly BuildingService $buildingService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/buildings/{id}",
     *     summary="Информация о здании по ID",
     *     tags={"Buildings"},
     *     security={{"ApiKeyAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID здания",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Здание найдено",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="address", type="string", example="г. Москва, ул. Ленина 1"),
     *             @OA\Property(property="latitude", type="number", format="float", example=55.7558),
     *             @OA\Property(property="longitude", type="number", format="float", example=37.6173)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Здание не найдено"),
     *     @OA\Response(response=401, description="Invalid API key")
     * )
     */
    public function __invoke(int $id)
    {
//        return $this->buildingService->show($id);
        return $id;
    }
}
