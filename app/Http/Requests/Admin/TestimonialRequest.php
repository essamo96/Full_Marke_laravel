<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'position_ar' => 'nullable|string|max:255',
            'position_en' => 'nullable|string|max:255',
            'message_ar' => 'required|string',
            'message_en' => 'required|string',
            'display_order' => 'nullable|integer',
            'status' => 'nullable',
        ];

        $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048';

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => __('validation.required'),
            'name_en.required' => __('validation.required'),
            'message_ar.required' => __('validation.required'),
            'message_en.required' => __('validation.required'),
            'image.image' => __('validation.image'),
            'image.mimes' => __('validation.mimes'),
            'image.max' => __('validation.max'),
        ];
    }
}
