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

        return $this->withFooterSettings($settings);

    }

    protected function withFooterSettings(Collection $settings): Collection
    {
        $footerLogo = Media::query()->find($settings->get('footer_logo_media_id') ?: $settings->get('site_logo_media_id'));
        $favicon = Media::query()->find($settings->get('favicon_media_id'));
        $settings['footer_logo_url'] = $footerLogo ? Storage::disk($footerLogo->disk)->url($footerLogo->path) : null;
        $settings['favicon_url'] = $favicon ? Storage::disk($favicon->disk)->url($favicon->path) : null;

        $contacts = json_decode($settings->get('footer_contact_items', '[]'), true) ?: [];
        if ($contacts === []) {
            $contacts = array_values(array_filter([
                ['type' => 'Website', 'label' => $settings->get('footer_website_label', 'Website'), 'value' => $settings->get('site_website_display', ''), 'url' => $settings->get('site_website_url', '')],
                ['type' => 'Email', 'label' => $settings->get('footer_email_label', 'Email'), 'value' => $settings->get('contact_email', ''), 'url' => filled($settings->get('contact_email')) ? 'mailto:'.$settings->get('contact_email') : ''],
                ['type' => 'Address', 'label' => $settings->get('footer_address_label', 'Address'), 'value' => $settings->get('address', ''), 'url' => ''],
            ], fn ($item) => filled($item['value'])));
        }
        $socials = json_decode($settings->get('footer_social_items', '[]'), true) ?: [];
        if ($socials === []) {
            foreach (['facebook' => 'Facebook', 'twitter' => 'Twitter', 'instagram' => 'Instagram', 'youtube' => 'YouTube'] as $key => $label) {
                if (filled($url = $settings->get('social_'.$key))) {
                    $socials[] = ['platform' => $settings->get('social_'.$key.'_label', $label), 'url' => $url];
                }
            }
        }
        $mediaIds = collect($contacts)->pluck('icon_media_id')->merge(collect($socials)->pluck('icon_media_id'))->filter()->unique();
        $icons = $mediaIds->isEmpty() ? collect() : Media::query()->whereIn('id', $mediaIds)->get()->keyBy('id');
        $settings['footer_contact_items'] = collect($contacts)->map(function (array $item) use ($icons): array {
            $media = $icons->get($item['icon_media_id'] ?? null);
            return [...$item, 'icon_url' => $media ? Storage::disk($media->disk)->url($media->path) : null];
        })->all();
        $settings['footer_social_items'] = collect($socials)->map(function (array $item) use ($icons): array {
            $media = $icons->get($item['icon_media_id'] ?? null);
            return [...$item, 'icon_url' => $media ? Storage::disk($media->disk)->url($media->path) : null];
        })->all();

        return $settings;
    }
}
