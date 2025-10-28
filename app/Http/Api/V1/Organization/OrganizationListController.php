<?php
declare(strict_types=1);
namespace App\Http\Api\V1\Organization;

use App\DTO\OrganizationFilterDto;
use App\Http\Api\V1\AbstractController;
use App\Http\Requests\Organization\OrganizationListRequest;
use App\Services\OrganizationService;
use OpenApi\Annotations as OA;

class OrganizationListController extends AbstractController
{
    public function __construct(
        private readonly OrganizationService $service
    ) {}

    /**
     * @OA\Schema(
     *     schema="OrganizationListResponse",
     *     allOf={
     *         @OA\Schema(ref="#/components/schemas/Response"),
     *         @OA\Schema(
     *             @OA\Property(
     *                 property="body",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/OrganizationList")
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
     *     path="/api/v1/organizations",
     *     summary="Список всех организаций",
     *     tags={"Organizations"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Parameter(
     *          name="building_ids[]",
     *          in="query",
     *          description="Массив ID зданий",
     *          @OA\Schema(type="array", @OA\Items(type="integer"))
     *     ),
     *     @OA\Parameter(
     *          name="activity_ids[]",
     *          in="query",
     *          description="Массив ID видов деятельности. Вернёт все соответствующие учитывая потомков",
     *          @OA\Schema(type="array", @OA\Items(type="integer"))
     *      ),
     *      @OA\Parameter(name="activity_id", in="query", @OA\Schema(type="integer", default=1)),
     *      @OA\Parameter(name="lat", in="query", @OA\Schema(type="number")),
     *      @OA\Parameter(name="lng", in="query", @OA\Schema(type="number")),
     *      @OA\Parameter(name="radius", in="query", @OA\Schema(type="number")),
     *      @OA\Parameter(
     *          name="bbox[]",
     *          in="query",
     *          description="Bounding box: [minLng, minLat, maxLng, maxLat] (longitude,latitude). Например: 37.5,55.7,37.7,55.8",
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(type="number"),
     *              minItems=4,
     *              maxItems=4,
     *              example={56.08, 54.81, 56.09, 54.82}
     *          )
     *      ),
     *      @OA\Parameter(name="name", in="query", @OA\Schema(type="string")),
     *      @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", default=1)),
     *      @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=20)),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/OrganizationListResponse")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid.")
     *          )
     *      )
     * )
     */
    public function __invoke(OrganizationListRequest $request)
    {
        $filter = OrganizationFilterDto::fromRequest($request);


        return $this->service->read($filter);
    }
}
