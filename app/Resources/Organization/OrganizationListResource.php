<?php

namespace App\Resources\Organization;

use App\Resources\AbstractResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;

class OrganizationListResource extends AbstractResource
{
    /**
     * @OA\Schema(
     *     schema="OrganizationList",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="ООО Мясной Двор"),
     *     @OA\Property(property="description", type="integer", example="Производство колбас"),
     * )
     */
    public function toArray(Request $request): array|Arrayable
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
