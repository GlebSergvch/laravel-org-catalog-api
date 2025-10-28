<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Organization;

use App\Http\Api\V1\AbstractController;
use App\Resources\Organization\OrganizationViewResource;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class OrganizationShowController extends AbstractController
{
    public function __construct(
        private readonly OrganizationService $organizationService
    ) {}

    /**
     * @OA\Schema(
     *     schema="OrganizationShowResponse",
     *     allOf={
     *         @OA\Schema(ref="#/components/schemas/Response"),
     *         @OA\Schema(
     *             @OA\Property(
     *                 property="body",
     *                 ref="#/components/schemas/OrganizationView"
     *             )
     *         )
     *     }
     * )
     *
     * @OA\Get(
     *     path="/api/v1/organizations/{id}",
     *     summary="Информация об организации",
     *     tags={"Organizations"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/OrganizationShowResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Organization not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Organization not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function __invoke(int $id): JsonResponse
    {
        return $this->organizationService->show($id);
    }
}
