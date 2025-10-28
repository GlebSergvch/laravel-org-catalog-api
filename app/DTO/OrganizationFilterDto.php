<?php
declare(strict_types=1);

namespace App\DTO;

use Illuminate\Http\Request;

final class OrganizationFilterDto
{
    public function __construct(
        public ?array $buildingIds = null,
        public ?array $activityIds = null,
        public ?int $activityId = null,
        public ?float $lat = null,
        public ?float $lng = null,
        public ?float $radius = null, // meters
        public ?array $bbox = null,   // [minLng, minLat, maxLng, maxLat]
        public ?string $name = null,
        public int $perPage = 20,
        public int $page = 1,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $buildingIds = $request->query('building_ids') ?? $request->query('building_ids[]') ?? null;
        $activityIds = $request->query('activity_ids') ?? $request->query('activity_ids[]') ?? null;

        $activityId = $request->filled('activity_id') ? (int) $request->query('activity_id') : null;
        $lat = $request->filled('lat') ? (float) $request->query('lat') : null;
        $lng = $request->filled('lng') ? (float) $request->query('lng') : null;
        $radius = $request->filled('radius') ? (float) $request->query('radius') : null;

        // bbox can be passed as array, or string "37.5,55.7,37.7,55.8"
        $rawBbox = $request->query('bbox');
        // also support bbox[] notation (e.g. ?bbox[]=37.5&bbox[]=55.7...)
        if (is_null($rawBbox) && $request->query->has('bbox') && is_array($request->query->get('bbox'))) {
            $rawBbox = $request->query->get('bbox');
        }

        $bbox = self::parseBBox($rawBbox);

        $name = $request->filled('name') ? trim((string) $request->query('name')) : null;
        if ($name === '') {
            $name = null;
        }

        $perPage = (int) $request->query('per_page', 20);
        $page = (int) $request->query('page', 1);

        return new self(
            buildingIds: $buildingIds,
            activityIds: $activityIds,
            activityId: $activityId,
            lat: $lat,
            lng: $lng,
            radius: $radius,
            bbox: $bbox,
            name: $name,
            perPage: $perPage > 0 ? $perPage : 20,
            page: $page > 0 ? $page : 1,
        );
    }

    /**
     * Normalize IDs input to array of ints or null.
     * Accepts:
     *  - array of values,
     *  - comma-separated string "1,2,3",
     *  - single numeric value.
     */
    private static function parseIds(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && str_contains($value, ',')) {
            $parts = array_map('trim', explode(',', $value));
        } elseif (is_array($value)) {
            $parts = array_values($value);
        } else {
            $parts = [$value];
        }

        $result = [];
        foreach ($parts as $p) {
            if ($p === '' || $p === null) {
                continue;
            }
            if (!is_numeric($p)) {
                // skip non-numeric tokens
                continue;
            }
            $result[] = (int) $p;
        }

        return count($result) ? array_values(array_unique($result)) : null;
    }

    /**
     * Parse bbox input and normalize to [minLng,minLat,maxLng,maxLat] or null.
     * Supported inputs:
     *  - array(4) [minLng, minLat, maxLng, maxLat]
     *  - array(3) [minLng, minLat, maxLng] -> maxLat = minLat (degenerate)
     *  - array(2) [lng, lat] -> degenerate bbox
     *  - string "37.5,55.7,37.7,55.8"
     * Returns null on invalid input.
     */
    private static function parseBBox(mixed $raw): ?array
    {
        if ($raw === null) {
            return null;
        }

        // If string, explode by comma
        if (is_string($raw) && str_contains($raw, ',')) {
            $parts = array_map('trim', explode(',', $raw));
        } elseif (is_array($raw)) {
            $parts = array_values($raw);
        } else {
            // unsupported type (e.g. numeric single) -> ignore
            return null;
        }

        // cast numeric-like to float; reject non-numeric values
        $floats = [];
        foreach ($parts as $p) {
            if ($p === '' || $p === null) {
                continue;
            }
            if (!is_numeric($p)) {
                return null;
            }
            // preserve 0 as valid numeric
            $floats[] = (float) $p;
        }

        $count = count($floats);

        if ($count >= 4) {
            [$minLng, $minLat, $maxLng, $maxLat] = array_slice($floats, 0, 4);
        } elseif ($count === 3) {
            [$minLng, $minLat, $maxLng] = $floats;
            $maxLat = $minLat; // degenerate
        } elseif ($count === 2) {
            [$minLng, $minLat] = $floats;
            $maxLng = $minLng;
            $maxLat = $minLat;
        } else {
            // less than 2 values → ignore
            return null;
        }

        // validate ranges
        if ($minLng < -180 || $minLng > 180 || $maxLng < -180 || $maxLng > 180) {
            return null;
        }
        if ($minLat < -90 || $minLat > 90 || $maxLat < -90 || $maxLat > 90) {
            return null;
        }

        // ensure min <= max
        if ($minLng > $maxLng) {
            [$minLng, $maxLng] = [$maxLng, $minLng];
        }
        if ($minLat > $maxLat) {
            [$minLat, $maxLat] = [$maxLat, $minLat];
        }

        return [$minLng, $minLat, $maxLng, $maxLat];
    }
}
