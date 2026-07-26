<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NewsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'image' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ];

        $locales = ['ar', 'en'];
        foreach ($locales as $locale) {
            $rules['title_' . $locale] = 'required|string|max:255';
            $rules['description_' . $locale] = 'required|string';
        }

        return $rules;
    }
}
