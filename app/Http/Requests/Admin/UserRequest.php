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
        $encryptedId = $this->route('id');
        $id = null;
        if ($encryptedId) {
            try {
                $id = \Illuminate\Support\Facades\Crypt::decrypt($encryptedId);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                // Ignore or handle
            }
        }

        return [
            'name' => 'required|string|min:3|max:191',
            'email' => ['required', 'email', Rule::unique('admins', 'email')->ignore($id)],
            'password' => $id ? 'nullable|required_with:password_confirmation|string|min:6|confirmed' : 'required|string|min:6|confirmed',
            'role' => 'required|integer|exists:roles,id',
            'photo' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
        ];
    }
}
