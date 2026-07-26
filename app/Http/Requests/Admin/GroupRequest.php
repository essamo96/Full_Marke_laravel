<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|string|max:255',
            'days' => 'required|array|min:1',
            'days.*' => 'in:sat,sun,mon,tue,wed,thu,fri',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_capacity' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'zoom_link' => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
        ];
    }
}
