<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('teachers', 'email')->ignore($id)],
            'phone' => 'nullable|string|max:20',
            'password' => $id ? 'nullable|string|min:8' : 'required|string|min:8',
            'specialty' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|max:2048',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
            'status' => 'nullable|boolean',
        ];
    }
}
