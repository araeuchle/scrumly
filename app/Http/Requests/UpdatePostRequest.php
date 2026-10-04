<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['sometimes', 'required', 'string'],
            'cover_image_url' => ['nullable', 'url', 'max:2048'],
            'published' => ['nullable', 'boolean'],
        ];
    }
}
