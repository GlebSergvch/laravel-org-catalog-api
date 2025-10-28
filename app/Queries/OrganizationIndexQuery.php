<?php
declare(strict_types=1);
namespace App\Queries;

use App\DTO\OrganizationFilterDto;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrganizationIndexQuery
{
    public function handle(OrganizationFilterDto $filter): Builder
    {
        $query = Organization::query()
            ->with(['buildings', 'activities', 'phones'])
            ->select('organizations.*');

        if (isset($filter->buildingIds)) {
            $query->whereHas('buildings', fn($q) => $q->whereIn('buildings.id', $filter->buildingIds));
        }

        if (isset($filter->activityId)) {
            $query->whereHas('activities', fn($q) => $q->where('activities.id', $filter->activityId));
        }

        if ($filter->activityIds) {
            $query->where(function ($q) use ($filter) {
                foreach ($filter->activityIds as $activityId) {
                    $q->orWhereHas('activities', function ($sub) use ($activityId) {
                        $descendantIds = DB::table('activity_closure')
                            ->where('ancestor_id', $activityId)
                            ->pluck('descendant_id');

                        $sub->whereIn('activities.id', $descendantIds);
                    });
                }
            });
        }

        if ($filter->lat && $filter->lng && $filter->radius) {
            $query->whereHas('buildings', function ($q) use ($filter) {
                $q->whereRaw(
                    "ST_DWithin(location::geography, ST_SetSRID(ST_Point(?, ?), 4326)::geography, ?)",
                    [$filter->lng, $filter->lat, $filter->radius]
                );
            });
        }

        if ($filter->bbox) {
            [$minLng, $minLat, $maxLng, $maxLat] = $filter->bbox;
            $wkt = "POLYGON(($minLng $minLat, $maxLng $minLat, $maxLng $maxLat, $minLng $maxLat, $minLng $minLat))";

            $query->whereHas('buildings', function ($q) use ($wkt) {
                $q->whereRaw("ST_Intersects(location, ST_GeomFromText(?, 4326))", [$wkt]);
            });
        }

        if ($filter->name) {
            $query->where('name', 'ILIKE', "%{$filter->name}%");
        }

        return $query;
    }
}
