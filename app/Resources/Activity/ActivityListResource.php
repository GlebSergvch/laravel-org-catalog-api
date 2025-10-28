<?php
declare(strict_types=1);
namespace App\Resources\Activity;

use App\Resources\AbstractResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;

class ActivityListResource extends AbstractResource
{
    /**
     * @OA\Schema(
     *     schema="ActivityList",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Еда"),
     *     @OA\Property(property="level", type="integer", example=1),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-27T12:00:00Z")
     * )
     */
    public function toArray(Request $request): array|Arrayable
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'level' => $this->level,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
