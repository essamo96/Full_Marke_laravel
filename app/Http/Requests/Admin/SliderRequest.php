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
            
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'video1' => 'nullable|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime|max:20480', // 20MB
            'video2' => 'nullable|mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime|max:20480', // 20MB
            
            'sort' => 'nullable|integer',
        ];
    }
}
