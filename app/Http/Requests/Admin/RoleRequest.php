<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:191', Rule::unique('roles', 'name')->where('guard_name', 'admin')->ignore($id)],
            'status' => 'nullable',
            'is_user' => 'nullable',
        ];
    }
}
