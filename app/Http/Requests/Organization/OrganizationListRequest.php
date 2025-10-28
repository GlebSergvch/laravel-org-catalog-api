<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // API key проверяется в middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Массивы ID
            'building_ids' => ['sometimes', 'array'],
            'building_ids.*' => ['integer'],

            'activity_ids' => ['sometimes', 'array'],
            'activity_ids.*' => ['integer'],

            // Устаревшее: activity_id (одно значение)
            'activity_id' => ['nullable', 'integer'],

            // Гео: радиус
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:1', 'max:100000'], // метров

            // BBox: может быть массивом
            'bbox' => ['nullable', 'array', 'size:4'],
            'bbox.*' => ['numeric'],

            // Поиск по имени
            'name' => ['nullable', 'string', 'max:255'],

            // Пагинация
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'building_ids.*.exists' => 'Указанное здание не существует.',
            'activity_ids.*.exists' => 'Указанный вид деятельности не существует.',
            'activity_id.exists' => 'Указанный вид деятельности не существует.',
            'activity_id.integer' => 'Вид деятельности должен быть числом',

            'lat.between' => 'Широта должна быть от -90 до 90.',
            'lng.between' => 'Долгота должна быть от -180 до 180.',
            'radius.min' => 'Радиус должен быть больше 0.',
            'radius.max' => 'Радиус не может превышать 100 км.',

            'bbox.size' => 'BBOX должен содержать ровно 4 значения: [minLng, minLat, maxLng, maxLat].',
            'bbox.*.numeric' => 'Все значения BBOX должны быть числами.',

            'name.max' => 'Название не должно превышать 255 символов.',
            'per_page.max' => 'Количество на странице не может превышать 100.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing([
            'page' => 1,
            'per_page' => 20,
        ]);

        // Поддержка bbox[] = [37.5, 55.7, 37.7, 55.8]
        if ($this->has('bbox') && is_array($this->input('bbox'))) {
            $bbox = $this->input('bbox');
            if (count($bbox) === 4 && !array_key_exists(0, $bbox)) {
                // Если пришел как ?bbox[]=37.5&bbox[]=55.7...
                $this->merge(['bbox' => array_values($bbox)]);
            }
        }

        // Поддержка building_ids[] и activity_ids[]
        foreach (['building_ids', 'activity_ids'] as $key) {
            $value = $this->query($key);
            if (is_string($value) && str_contains($value, ',')) {
                $this->merge([$key => array_map('intval', array_filter(explode(',', $value)))]);
            }
        }
    }
}
