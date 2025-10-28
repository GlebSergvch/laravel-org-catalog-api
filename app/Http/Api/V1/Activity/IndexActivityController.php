<?php
declare(strict_types=1);
namespace App\Http\Api\V1\Activity;

use App\Http\Api\V1\AbstractController;
use App\Models\Activity;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class IndexActivityController extends AbstractController
{
    public function __construct(private readonly ActivityService $activityService) {}

    /**
     * @OA\Schema(
     *     schema="ActivityListResponse",
     *     allOf={
     *         @OA\Schema(ref="#/components/schemas/Response"),
     *         @OA\Schema(
     *             @OA\Property(
     *                 property="body",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ActivityList")
     *                 ),
     *                 @OA\Property(ref="#/components/schemas/PaginationMeta"),
     *                 @OA\Property(
     *                     property="related",
     *                     type="array",
     *                     @OA\Items(),
     *                     example="[]"
     *                 )
     *             )
     *         )
     *     }
     * )
     *
     * @OA\Get(
     *     path="/api/v1/activities",
     *     summary="Список всех видов деятельности",
     *     tags={"Activities"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество элементов на странице",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/ActivityListResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Content",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */
    public function __invoke(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);

        return $this->activityService->read($perPage);
    }
}
