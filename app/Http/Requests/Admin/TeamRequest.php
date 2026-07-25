<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TeamRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id',
            'image' => 'nullable|string|max:255',
            'socials' => 'nullable|array',
            'member_type' => 'nullable|in:board,team',
            'display_order' => 'nullable|integer',
        ];
    }
}
