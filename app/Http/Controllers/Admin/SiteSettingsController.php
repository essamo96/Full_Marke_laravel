<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SiteSettingRequest;
use App\Models\SiteSetting;

class SiteSettingsController extends AdminController
{
    protected $path;

    /** Upload field => storage sub-folder under the `public` disk. */
    protected array $mediaFields = [
        'hero_video_1' => 'site-settings/videos',
        'hero_video_2' => 'site-settings/videos',
        'hero_video_1_mobile' => 'site-settings/videos',
        'hero_video_2_mobile' => 'site-settings/videos',
        'about_video' => 'site-settings/videos',
        'about_video_mobile' => 'site-settings/videos',
        'hero_still_image' => 'site-settings/images'];

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'site_settings';
        $this->path = 'site_settings';
    }

    /**
     * Single combined add/edit screen — the site only ever has one
     * settings record, so there is no list view and no separate add route.
     */
    public function getIndex()
    {
        self::$data['info'] = SiteSetting::with('translations')->first() ?? SiteSetting::current();

        return view('admin.site_settings.view', self::$data);
    }

    public function postUpdate(SiteSettingRequest $request)
    {
        $validated = $request->validated();
        $record = SiteSetting::find(1) ?? new SiteSetting(['id' => 1]);

        foreach ($this->mediaFields as $field => $folder) {
            if ($request->hasFile($field)) {
                $validated[$field] = 'storage/'.$request->file($field)->store($folder, 'public');
            } else {
                unset($validated[$field]);
            }
        }

        // Social link logos: each row may carry an uploaded icon file.
        $socialLinks = [];
        foreach ($request->input('social_links', []) as $i => $link) {
            $icon = $record->social_links[$i]['icon'] ?? null;
            if ($request->hasFile("social_links.$i.icon")) {
                $icon = 'storage/'.$request->file("social_links.$i.icon")->store('site-settings/social', 'public');
            }
            $socialLinks[] = [
                'platform' => $link['platform'] ?? '',
                'url' => $link['url'] ?? '',
                'icon' => $icon];
        }
        $validated['social_links'] = $socialLinks;
        $validated['maintenance_mode'] = $request->boolean('maintenance_mode');
        $validated['show_translation_button'] = $request->boolean('show_translation_button');

        // Extract options
        $options = [
            'show_contact_form' => $request->boolean('show_contact_form'),
            'enable_newsletter' => $request->boolean('enable_newsletter'),
            'enable_live_chat' => $request->boolean('enable_live_chat'),
            'show_registration_button' => $request->boolean('show_registration_button'),
        ];
        $validated['options'] = json_encode($options, JSON_UNESCAPED_UNICODE);

        $record->fill($validated)->save();

        // Handle Translations
        $locales = ['ar', 'en'];
        foreach ($locales as $locale) {
            $translationData = $validated[$locale] ?? [];
            if (!empty($translationData)) {
                $record->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $translationData
                );
            }
        }

        return redirect()->route($this->path.'.view')->with('success', __('app.update_success'));
    }
}
