<?php
declare(strict_types=1);
namespace App\Resources;

use App\Interfaces\ApiPaginationResourceInterface;
use App\Interfaces\ApiResourceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AbstractResource extends JsonResource implements ApiResourceInterface
{
    private static ?LengthAwarePaginator $paginator = null;
    private static array $related = [];

    /**
     * @param Model $resource
     * @param array $related
     * @return ApiResourceInterface
     */
    public static function modelWithRelated(Model $resource, array $related)
    {
        self::$related = $related;
        $collection = collect([$resource]);
        return self::collection($collection);
    }

    /**
     * @param mixed $resource
     * @param array $related
     * @return ApiResourceInterface|AnonymousResourceCollection
     */
    public static function collectWithRelated(mixed $resource, array $related): ApiResourceInterface|AnonymousResourceCollection
    {
        self::$related = $related;
        return self::collection($resource);
    }

    /**
     * @param mixed $resource
     * @return ApiResourceInterface|AnonymousResourceCollection
     */
    public static function collection(mixed $resource): ApiResourceInterface|AnonymousResourceCollection
    {
        $collection = parent::collection($resource);
        if ($resource instanceof LengthAwarePaginator) {
            self::$paginator = $resource;
            return self::createCollection($collection);
        }
        return $collection;
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function getCollection(): AnonymousResourceCollection
    {
        return static::collection($this->resource);
    }

    /**
     * @param AnonymousResourceCollection $collection
     * @return ApiResourceInterface
     */
    private static function createCollection(
        Collection|AnonymousResourceCollection $collection
    ): ApiResourceInterface {
        $result = new class($collection) extends ResourceCollection implements ApiPaginationResourceInterface {

            public ?LengthAwarePaginator $paginator = null;
            public ?array $related = null;

            /**
             * @param Request $request
             * @return array
             */
            public function toArray(Request $request): array
            {
                $data = [
                    'data' => $this->collection,
                ];

                if (!is_null($this->paginator)) {
                    $data['meta'] = [
                        'current_page' => $this->paginator->currentPage(),
                        'last_page' => $this->paginator->lastPage(),
                        'per_page' => $this->paginator->perPage(),
                        'total' => $this->paginator->total(),
                    ];
                }

                if (!is_null($this->related)) {
                    $data['related'] = $this->related;
                }

                return $data;
            }

            /**
             * @return AnonymousResourceCollection
             */
            public function getCollection(): AnonymousResourceCollection
            {
                return static::collection($this->resource);
            }
        };

        $result->paginator = self::$paginator;
        $result->related = self::$related;
        return $result;
    }
}
