<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Usually check for admin permission here, e.g. auth('admin')->check()
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject_id' => 'required|exists:subjects,id',
            'group_id' => 'required|exists:groups,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'duration_minutes' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,published,completed',
            'excluded_student_ids' => 'nullable|array',
            'excluded_student_ids.*' => 'exists:students,id',
            
            // Validate the questions array
            'questions' => 'nullable|array',
            'questions.*.type' => 'required|in:true_false,multiple_choice,essay',
            'questions.*.content' => 'required|string',
            'questions.*.points' => 'required|integer|min:1',
            'questions.*.sort_order' => 'nullable|integer',

            // If it's a multiple choice or true/false, it must have options
            'questions.*.options' => 'required_if:questions.*.type,multiple_choice,true_false|array',
            'questions.*.options.*.option_text' => 'required_with:questions.*.options|string',
            'questions.*.options.*.is_correct' => 'required_with:questions.*.options|boolean',
        ];
    }
}
