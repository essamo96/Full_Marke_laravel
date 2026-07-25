<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PosPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'address_ar' => 'required|string|max:500',
            'address_en' => 'required|string|max:500',
            'working_hours_ar' => 'nullable|string|max:255',
            'working_hours_en' => 'nullable|string|max:255',
            'booklet_price' => 'nullable|numeric|min:0',
            'phone' => 'required|string|max:50',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
