<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Media;
use App\Models\WebsiteSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

trait LoadsPublicSiteSettings
{
    protected function publicSiteSettings(): Collection
    {
        $settings = WebsiteSetting::query()->pluck('value', 'key');
        $settings['home_nav_items'] = json_decode($settings->get('home_nav_items', '[]'), true) ?: [];
        $settings['footer_links'] = json_decode($settings->get('footer_links', '[]'), true) ?: [];

        $logo = Media::query()->find($settings->get('site_logo_media_id'));
        $settings['site_logo_url'] = $logo ? Storage::disk($logo->disk)->url($logo->path) : null;

        return $settings;
    }
}
