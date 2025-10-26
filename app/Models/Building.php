<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     schema="Building",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="address", type="string", example="г. Москва, ул. Ленина 1, офис 3"),
 *     @OA\Property(property="latitude", type="number", format="float", example=55.7558),
 *     @OA\Property(property="longitude", type="number", format="float", example=37.6173),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-05T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-05T12:00:00Z")
 * )
 */
class Building extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'address',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Организации в этом здании
     */
//    public function organizations(): HasMany
//    {
//        return $this->hasMany(Organization::class, 'building_id');
//    }

    /**
     * Haversine: расстояние до точки (в метрах)
     */
    public function distanceTo(float $lat, float $lng): float
    {
        $earthRadius = 6371000; // метров

        $lat1 = deg2rad($this->latitude);
        $lat2 = deg2rad($lat);
        $dLat = deg2rad($lat - $this->latitude);
        $dLng = deg2rad($lng - $this->longitude);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($lat1) * cos($lat2) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
