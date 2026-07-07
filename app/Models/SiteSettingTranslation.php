<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSettingTranslation extends Model
{
    protected $table = 'site_settings_translations';

    protected $fillable = [
        'site_setting_id',
        'locale',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'maintenance_title',
        'maintenance_message',
        'site_address',
    ];

    public function siteSetting()
    {
        return $this->belongsTo(SiteSetting::class);
    }
}
