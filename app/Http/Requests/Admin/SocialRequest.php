<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SocialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We use Spatie middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // When updating a single record
        if ($this->route('id')) {
            return [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'link' => 'required|url|max:255',
                'icon' => ['bail', 'required_without:preset_logo', 'nullable', 'string', 'max:255', 'regex:/^(ki-|bi)/'],
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'preset_logo' => 'nullable|string',
            ];
        }

        // When adding via Repeater
        return [
            'socials' => 'required|array',
            'socials.*.name_ar' => 'required|string|max:255',
            'socials.*.name_en' => 'required|string|max:255',
            'socials.*.link' => 'required|url|max:255',
            'socials.*.icon' => ['bail', 'required_without:socials.*.preset_logo', 'nullable', 'string', 'max:255', 'regex:/^(ki-|bi)/'],
            'socials.*.preset_logo' => 'nullable|string',
            'socials.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
