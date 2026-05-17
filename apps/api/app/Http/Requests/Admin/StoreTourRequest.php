<?php

namespace App\Http\Requests\Admin;

use App\Enums\TourStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTourRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:tours,slug'],
            'short_description' => ['nullable', 'string'],
            'full_description' => ['nullable', 'string'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'duration_nights' => ['nullable', 'integer', 'min:0', 'max:364'],
            'status' => ['nullable', Rule::enum(TourStatus::class)],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }
}
