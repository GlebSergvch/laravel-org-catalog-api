<?php
declare(strict_types=1);
namespace App\Services;

use App\Interfaces\ApiPaginationResourceInterface;
use App\Interfaces\ApiResourceInterface;
use App\Interfaces\DtoInterface;
use App\Resources\AbstractResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use function response;

abstract class AbstractApiService
{
    /**
     * @var string
     */
    protected string $resource;

    protected string $direct = 'DESC';

    /**
     * @param $class
     * @return void
     */
    public function setResource($class): void
    {
        $this->resource = $class;
    }

    protected array $errors = [];

    /**
     * @param DtoInterface $dto
     * @param array $rules
     * @param array $messages
     * @return bool
     */
    protected function validate(DtoInterface $dto, array $rules, array $messages): bool
    {
        $validator = Validator::make((array)$dto, $rules, $messages);
        $this->errors = $validator->errors()->messages();
        return $validator->messages()->isEmpty();
    }

    /**
     * Формирует успешный JSON-ответ.
     *
     * @param mixed $data Данные для ответа (коллекция, ресурс или массив).
     * @param string $message Сообщение для ответа.
     * @return JsonResponse
     */
    public function success(mixed $data = [], string $message = ''): JsonResponse
    {
        return response()->json([
            'success' => true,
            'body' => $this->normalizeData($data),
            'message' => $message,
        ]);
    }

    /**
     * Нормализует данные в массив для единообразного ответа API.
     *
     * @param mixed $data Данные (коллекция, ресурс, массив или null).
     * @return array
     */
    protected function normalizeData(mixed $data): array
    {
        if ($data instanceof AnonymousResourceCollection) {
            return $data->toArray(request());
        }

        if ($data instanceof JsonResource) {
            return $data->toArray(request());
        }

        if (is_array($data)) {
            return $data;
        }

        return [];
    }

    /**
     * @param string|null $message
     * @param array $errors
     * @param int $code
     * @return JsonResponse
     */
    public function error(?string $message = 'Error', array $errors = [], int $code = 404): JsonResponse
    {
        $response = [
            'message' => $message,
            'errors'  => $errors
        ];

        return response()->json($response, $code);
    }
}
