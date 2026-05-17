<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartureRequest extends FormRequest
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
        $departureId = $this->route('departure')?->id ?? $this->route('departure');
        $isCreate = $this->isMethod('POST');

        $startDateRules = [
            $this->isMethod('PATCH') ? 'sometimes' : 'required',
            'date',
            Rule::unique('tour_departures', 'start_date')
                ->where('tour_id', $tourId)
                ->ignore($departureId),
        ];

        if ($isCreate) {
            $startDateRules[] = 'after_or_equal:today';
        }

        return [
            'start_date' => $startDateRules,
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'price' => [$this->isMethod('PATCH') ? 'sometimes' : 'required', 'integer', 'min:1'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'seats' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
