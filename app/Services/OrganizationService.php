<?php
declare(strict_types=1);
namespace App\Services;

use App\DTO\OrganizationFilterDto;
use App\Models\Organization;
use App\Queries\OrganizationIndexQuery;
use App\Resources\Organization\OrganizationListResource;
use App\Resources\Organization\OrganizationViewResource;
use Illuminate\Http\JsonResponse;

class OrganizationService extends AbstractApiService
{
    public function __construct(
        private readonly OrganizationIndexQuery $indexQuery
    ) {}

    public function read(OrganizationFilterDto $filter): JsonResponse
    {
        $query = $this->indexQuery->handle($filter);
        return $this->success(
            OrganizationListResource::collection($query->paginate($filter->perPage)),
            $this->langMessage('success')
        );
    }

    public function show(int $id): JsonResponse
    {
        $organisation = Organization::with(['buildings', 'activities', 'phones'])->findOrFail($id);

        return $this->success(
            new OrganizationViewResource($organisation),
            $this->langMessage('success')
        );
    }

    private function langMessage(string $name): string
    {
        return trans('dialogue.' . $name);
    }
}
