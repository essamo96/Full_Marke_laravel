<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class ProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = null;
        if ($this->route('id')) {
            try {
                $id = Crypt::decrypt($this->route('id'));
            } catch (\Exception $e) {
                $id = null;
            }
        }

        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'slug' => ['required', 'alpha_dash', Rule::unique('programs', 'slug')->ignore($id)],
            'type' => 'required|in:primary,middle,high,university,general',
            'short_description' => 'nullable|string|max:2000',
            'long_description' => 'nullable|string',
            'image' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
