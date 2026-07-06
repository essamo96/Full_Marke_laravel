<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PermissionsGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'name_ar' => 'required|string|max:191',
            'name_en' => 'required|string|max:191',
            'icon' => 'nullable|string|max:191',
            'color' => 'nullable|string|max:191',
            'sort' => 'nullable|integer',
            'parent_id' => 'nullable|integer',
            'status' => 'nullable'
        ];
    }
}
