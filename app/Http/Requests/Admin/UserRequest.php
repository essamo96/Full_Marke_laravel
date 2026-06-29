<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => 'required|string|min:3|max:191',
            'email' => ['required', 'email', Rule::unique('admins', 'email')->ignore($id)],
            'password' => $id ? 'nullable|string|min:8' : 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
            'photo' => 'nullable|image|max:2048',
            'status' => 'nullable|boolean',
        ];
    }
}
