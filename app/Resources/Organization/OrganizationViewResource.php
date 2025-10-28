<?php

namespace App\Resources\Organization;

use App\Resources\AbstractResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;

class OrganizationViewResource extends AbstractResource
{
    /**
     * @OA\Schema(
     *     schema="OrganizationPhone",
     *     type="object",
     *     @OA\Property(property="phone", type="string", example="+7(906)666-77-88"),
     *     @OA\Property(property="is_main", type="boolean", example=true),
     *     @OA\Property(property="type", type="string", example="офис", nullable=true)
     * )
     *
     * @OA\Schema(
     *     schema="OrganizationBuilding",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="address", type="string", example="ул. Тверская, 7"),
     *     @OA\Property(property="city", type="string", example="Москва")
     * )
     *
     * @OA\Schema(
     *     schema="OrganizationActivity",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=6),
     *     @OA\Property(property="name", type="string", example="Мясная продукция"),
     *     @OA\Property(property="level", type="integer", example=2)
     * )
     *
     * @OA\Schema(
     *     schema="OrganizationView",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="ООО Мясной Двор"),
     *     @OA\Property(property="description", type="string", example="Производство колбас", nullable=true),
     *     @OA\Property(property="inn", type="string", example="7701234567"),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-27T12:00:00Z"),
     *     @OA\Property(
     *         property="phones",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/OrganizationPhone")
     *     ),
     *     @OA\Property(
     *         property="buildings",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/OrganizationBuilding")
     *     ),
     *     @OA\Property(
     *         property="activities",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/OrganizationActivity")
     *     )
     * )
     */
    public function toArray(Request $request): array|Arrayable
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'inn' => $this->inn,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'phones' => $this->phones->map(fn ($phone) => [
                'phone' => $phone->phone,
                'is_main' => $phone->is_main,
                'type' => $phone->type,
            ]),
            'buildings' => $this->buildings->map(fn ($building) => [
                'id' => $building->id,
                'address' => $building->address,
                'city' => $building->city,
            ]),
            'activities' => $this->activities->map(fn ($activity) => [
                'id' => $activity->id,
                'name' => $activity->name,
                'level' => $activity->level,
            ]),
        ];
    }
}
