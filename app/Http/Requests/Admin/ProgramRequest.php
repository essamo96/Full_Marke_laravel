<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'slug' => ['required', 'alpha_dash', Rule::unique('programs', 'slug')->ignore($id)],
            'type' => 'required|in:regular,children,rehabilitation',
            'description_ar' => 'nullable|string|max:2000',
            'description_en' => 'nullable|string|max:2000',
            'image' => 'nullable|image|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
