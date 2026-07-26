<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'slug' => 'required|string|max:255',
            'tags' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'video' => 'nullable|string|max:255',
        ];

        $locales = ['ar', 'en'];
        foreach ($locales as $prefix) {
            $rules["{$prefix}.title"] = 'required|string|max:255';
            $rules["{$prefix}.subtitle"] = 'nullable|string|max:255';
            $rules["{$prefix}.details"] = 'nullable|string';
        }

        return $rules;
    }
}
