<?php

namespace App\Http\Requests\Admin;

use App\Enums\TourStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tourId = $this->route('tour')?->id ?? $this->route('tour');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('tours', 'slug')->ignore($tourId),
            ],
            'short_description' => ['sometimes', 'nullable', 'string'],
            'full_description' => ['sometimes', 'nullable', 'string'],
            'duration_days' => ['sometimes', 'required', 'integer', 'min:1', 'max:365'],
            'duration_nights' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:364'],
            'status' => ['sometimes', Rule::enum(TourStatus::class)],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }
}
