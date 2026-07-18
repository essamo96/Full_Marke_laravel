<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'program_id' => 'required|exists:programs,id',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string|max:2000',
            'description_en' => 'nullable|string|max:2000',
            'image' => 'nullable|image|max:2048',
            'fee' => 'required|numeric|min:0',
            'min_payment' => 'required|numeric|min:0|lte:fee',
            'reg_start_date' => 'nullable|date',
            'reg_end_date' => 'nullable|date|after_or_equal:reg_start_date',
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*' => 'exists:teachers,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'min_payment.lte' => __('app.min_payment_error'),
        ];
    }
}
