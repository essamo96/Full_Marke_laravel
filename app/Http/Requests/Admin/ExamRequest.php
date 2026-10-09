<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * Older clients (and the legacy form) post a single `group_id`; fold it
     * into `group_ids` so the rest of the request only deals with the list.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('group_ids') && $this->filled('group_id')) {
            $this->merge(['group_ids' => [$this->input('group_id')]]);
        }
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
            'group_id' => 'nullable|exists:groups,id',
            // One exam can be published to several groups of the same subject at once.
            'group_ids' => 'required|array|min:1',
            'group_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('groups', 'id')->where('subject_id', $this->input('subject_id')),
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'duration_minutes' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,published,completed',
            'audience' => 'required|in:students,guests,both',
            'allow_student_review' => 'nullable|boolean',
            'excluded_student_ids' => 'nullable|array',
            'excluded_student_ids.*' => 'exists:students,id',
            // Sent by the form so that "nobody excluded" (no checkbox ticked, so no field) can be told apart from "field not sent".
            'exclusions_present' => 'nullable|boolean',
            
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

    /**
     * A choice question saved without a correct option marks every student wrong (they all get 0),
     * so require exactly one correct option among the options that are actually kept (non-empty text).
     */
    public function after(): array
    {
        return [
            function ($validator) {
                foreach ((array) $this->input('questions', []) as $index => $question) {
                    if (! in_array($question['type'] ?? null, ['multiple_choice', 'true_false'], true)) {
                        continue;
                    }

                    $correct = collect((array) ($question['options'] ?? []))
                        ->filter(fn ($o) => is_array($o) && filled($o['option_text'] ?? null))
                        ->filter(fn ($o) => filter_var($o['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN))
                        ->count();

                    if ($correct !== 1) {
                        $validator->errors()->add(
                            "questions.{$index}.options",
                            'السؤال رقم '.((int) $index + 1).': يجب تحديد إجابة صحيحة واحدة.'
                        );
                    }
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'subject_id' => 'المادة الدراسية',
            'group_id' => 'المجموعة',
            'group_ids' => 'المجموعات',
            'group_ids.*' => 'المجموعة',
            'title' => 'عنوان الامتحان',
            'description' => 'الوصف',
            'start_time' => 'وقت البدء',
            'end_time' => 'وقت الانتهاء',
            'duration_minutes' => 'مدة الامتحان',
            'status' => 'حالة الامتحان',
            'audience' => 'الفئة المستهدفة',
            'allow_student_review' => 'سماح مراجعة الإجابات للطلاب',
            'excluded_student_ids' => 'الطلاب المستثنون',
            'questions.*.type' => 'نوع السؤال',
            'questions.*.content' => 'نص السؤال',
            'questions.*.points' => 'نقاط السؤال',
            'questions.*.options' => 'خيارات السؤال',
            'questions.*.options.*.option_text' => 'نص الخيار',
            'questions.*.options.*.is_correct' => 'حالة الإجابة الصحيحة',
        ];
    }

    public function messages(): array
    {
        return [
            'group_ids.required' => 'اختر مجموعة واحدة على الأقل.',
            'group_ids.min' => 'اختر مجموعة واحدة على الأقل.',
            'group_ids.*.exists' => 'إحدى المجموعات المختارة لا تتبع المادة الدراسية المحددة.',
            'questions.*.content.required' => 'حقل نص السؤال رقم :position مطلوب.',
            'questions.*.points.required' => 'حقل نقاط السؤال رقم :position مطلوب.',
            'questions.*.options.required_if' => 'الخيارات مطلوبة للسؤال رقم :position.',
            'questions.*.options.*.option_text.required_with' => 'نص الخيار مطلوب في السؤال رقم :position.',
        ];
    }
}
