<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SidebarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:191',
            'name_ar' => 'nullable|string|max:191',
            'name_en' => 'nullable|string|max:191',
            'icon' => 'nullable|string|max:191',
            'color' => 'nullable|string|max:50',
            'sort' => 'nullable|integer',
            'parent_id' => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }
}
