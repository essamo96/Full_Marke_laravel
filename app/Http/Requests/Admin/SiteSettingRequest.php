<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SiteSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'hero_video_1' => 'nullable|file|mimetypes:video/mp4|max:51200',
            'hero_video_2' => 'nullable|file|mimetypes:video/mp4|max:51200',
            'hero_video_1_mobile' => 'nullable|file|mimetypes:video/mp4|max:51200',
            'hero_video_2_mobile' => 'nullable|file|mimetypes:video/mp4|max:51200',
            'about_video' => 'nullable|file|mimetypes:video/mp4|max:51200',
            'about_video_mobile' => 'nullable|file|mimetypes:video/mp4|max:51200',
            'hero_still_image' => 'nullable|image|max:10240',

            'social_links' => 'nullable|array',
            'social_links.*.platform' => 'required_with:social_links|string|max:50',
            'social_links.*.url' => 'required_with:social_links|url',
            'social_links.*.icon' => 'nullable|image|max:2048',

            'maintenance_mode' => 'nullable|boolean',
            'show_translation_button' => 'nullable|boolean',
            'completed_courses_count' => 'nullable|integer|min:0',
            'registered_students_count' => 'nullable|integer|min:0',
            'training_hours_count' => 'nullable|integer|min:0',

            // Fields from general_settings
            'site_email' => 'nullable|email|max:255',
            'site_phone' => 'nullable|string|max:255',
            'show_contact_form' => 'nullable|boolean',
            'enable_newsletter' => 'nullable|boolean',
            'enable_live_chat' => 'nullable|boolean',
            'show_registration_button' => 'nullable|boolean',
        ];

        $locales = ['ar', 'en'];
        foreach ($locales as $locale) {
            $rules["{$locale}.seo_title"] = 'nullable|string|max:255';
            $rules["{$locale}.seo_description"] = 'nullable|string|max:1000';
            $rules["{$locale}.seo_keywords"] = 'nullable|string|max:1000';
            $rules["{$locale}.maintenance_title"] = 'nullable|string|max:255';
            $rules["{$locale}.maintenance_message"] = 'nullable|string|max:2000';
            $rules["{$locale}.site_address"] = 'nullable|string|max:500';
        }

        return $rules;
    }
}
