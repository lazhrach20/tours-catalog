<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TourIndexRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:500'],
            'page' => ['nullable', 'integer', 'min:1'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:48'],
            'sort' => ['nullable', 'string', Rule::in(['nearest_date', 'price_asc', 'duration', 'newest'])],
            'categories' => ['nullable', 'string'],
            'minDays' => ['nullable', 'integer', 'min:1'],
            'maxDays' => ['nullable', 'integer', 'min:1'],
            'priceMin' => ['nullable', 'integer', 'min:0'],
            'priceMax' => ['nullable', 'integer', 'min:0'],
            'dateFrom' => ['nullable', 'date'],
            'dateTo' => ['nullable', 'date'],
        ];
    }

    public function searchQuery(): string
    {
        return trim((string) $this->input('q', ''));
    }

    public function page(): int
    {
        return max(1, (int) $this->input('page', 1));
    }

    public function perPage(): int
    {
        return max(1, min(48, (int) $this->input('perPage', 12)));
    }

    public function sort(): string
    {
        return (string) $this->input('sort', 'nearest_date');
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $filters = [];

        $categories = $this->input('categories');
        if (is_string($categories) && $categories !== '') {
            $filters['categories'] = array_values(array_filter(array_map(
                'trim',
                explode(',', $categories),
            )));
        }

        foreach (['minDays', 'maxDays', 'priceMin', 'priceMax'] as $key) {
            if ($this->filled($key)) {
                $filters[$key] = (int) $this->input($key);
            }
        }

        foreach (['dateFrom', 'dateTo'] as $key) {
            if ($this->filled($key)) {
                $filters[$key] = (string) $this->input($key);
            }
        }

        return $filters;
    }
}
