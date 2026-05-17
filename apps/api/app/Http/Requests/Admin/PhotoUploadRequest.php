<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PhotoUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_cover')) {
            $this->merge([
                'is_cover' => filter_var($this->input('is_cover'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxKb = (int) env('PHOTO_MAX_SIZE_MB', 10) * 1024;
        $mimes = array_map('trim', explode(',', (string) env('PHOTO_ALLOWED_MIMES', 'image/jpeg,image/png,image/webp')));

        return [
            'photo' => ['required', 'file', 'max:'.$maxKb, 'mimetypes:'.implode(',', $mimes)],
            'alt' => ['nullable', 'string', 'max:255'],
            'is_cover' => ['nullable', 'boolean'],
        ];
    }
}
