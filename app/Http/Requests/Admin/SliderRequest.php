<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SliderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_ar' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'desc_ar' => 'nullable|string',
            'desc_en' => 'nullable|string',
            
            'btn1_text_ar' => 'nullable|string|max:255',
            'btn1_text_en' => 'nullable|string|max:255',
            'btn1_link' => 'nullable|string|max:255',
            
            'btn2_text_ar' => 'nullable|string|max:255',
            'btn2_text_en' => 'nullable|string|max:255',
            'btn2_link' => 'nullable|string|max:255',
            
            'image' => 'nullable|string|max:255',
            'video1' => 'nullable|string|max:255',
            'video2' => 'nullable|string|max:255',
            
            'sort' => 'nullable|integer',
        ];
    }
}
