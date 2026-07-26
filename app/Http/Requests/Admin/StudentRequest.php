<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        if ($id) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Exception $e) {
                // Ignore decryption exception, validation will handle or fail later
            }
        }

        return [
            'full_name_ar' => 'required|string|max:255',
            'full_name_en' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('students', 'email')->ignore($id)],
            'phone' => 'required|string|max:30',
            'password' => $id ? 'nullable|string|min:8' : 'required|string|min:8',
            'national_id' => ['nullable', 'string', 'max:255', Rule::unique('students', 'national_id')->ignore($id)],
            'is_child' => 'nullable|boolean',
            'guardian_id' => 'nullable|exists:guardians,id',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string',
            'region_id' => 'nullable|exists:regions,id',
            'branch_id' => 'nullable|exists:branches,id',
            'major_profession' => 'nullable|string|max:255',
            'health_information' => 'nullable|string',
            'status' => 'nullable|boolean',
            'image' => 'nullable|string|max:255',
        ];
    }
}
