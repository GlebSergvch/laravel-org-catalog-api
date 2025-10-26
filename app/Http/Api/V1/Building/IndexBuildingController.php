<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Building;

use App\Http\Api\V1\AbstractController;
use App\Services\BuildingService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class IndexBuildingController extends AbstractController
{
    public function __construct(
        private readonly BuildingService $buildingService,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/buildings",
     *     summary="Список всех зданий",
     *     tags={"Buildings"},
     *     security={{"ApiKeyAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество на странице",
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="address", type="string", example="г. Москва, ул. Ленина 1"),
     *                 @OA\Property(property="latitude", type="number", format="float", example=55.7558),
     *                 @OA\Property(property="longitude", type="number", format="float", example=37.6173)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid API key")
     * )
     */
    public function __invoke()
    {
//        return $this->buildingService->index(
//            perPage: (int) request()->query('per_page', 20),
//            page: (int) request()->query('page', 1)
//        );
        return 1;
    }
}
