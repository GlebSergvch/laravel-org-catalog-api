<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\Activity;
use App\Resources\Activity\ActivityListResource;
use Illuminate\Http\JsonResponse;

class ActivityService extends AbstractApiService
{
    public function read(int $perPage = 15): JsonResponse
    {
        $activities = Activity::query()->paginate($perPage);

        return $this->success(
            ActivityListResource::collection($activities),
            $this->langMessage('success')
        );
    }

    public function getActivitiesTree(): \Illuminate\Database\Eloquent\Collection|array
    {
        return Activity::with('children.children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }

    private function langMessage(string $name): string
    {
        return trans('dialogue.' . $name);
    }
}
