<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsPublicSiteSettings;
use App\Models\Media;
use App\Models\Page;
use App\Models\WebsiteSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AboutUsController extends Controller
{
    use LoadsPublicSiteSettings;

    public function __invoke(): View
    {
        $page = Page::query()
            ->where('slug', 'about-us')
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->with([
                'ogImage',
                'mediaAttachments' => fn ($query) => $query->wherePivot('role', 'about-inspiration')->orderByPivot('sort_order'),
            ])
            ->firstOrFail();

        $settings = WebsiteSetting::query()
            ->where('group', 'about-us')
            ->pluck('value', 'key');
        $siteSettings = $this->publicSiteSettings();
        $seoTitle = $page->meta_title
            ?: $page->title.($siteSettings->get('site_name') ? ' | '.$siteSettings->get('site_name') : '');
        $metaDescription = $page->meta_description ?: $page->excerpt;
        $heroImage = $this->image($page->ogImage);
        $inspirationImages = $page->mediaAttachments
            ->map(fn (Media $media): ?array => $this->image($media))
            ->filter()
            ->values();

        $trustees = $this->visibleItems($this->decode($settings->get('about_trustees')))
            ->map(function (array $trustee): array {
                $trustee['image'] = $this->imageById($trustee['image_media_id'] ?? null);
                $trustee['social_links'] = collect([
                    ['label' => 'Facebook', 'symbol' => 'f', 'url' => $this->externalUrl($trustee['facebook_url'] ?? null)],
                    ['label' => 'Twitter/X', 'symbol' => '𝕏', 'url' => $this->externalUrl($trustee['twitter_url'] ?? null)],
                    ['label' => 'LinkedIn', 'symbol' => 'in', 'url' => $this->externalUrl($trustee['linkedin_url'] ?? null)],
                    ['label' => 'Instagram', 'symbol' => '◎', 'url' => $this->externalUrl($trustee['instagram_url'] ?? null)],
                    ['label' => 'Other profile', 'symbol' => '↗', 'url' => $this->externalUrl($trustee['other_profile_url'] ?? null)],
                ])->filter(fn (array $link): bool => filled($link['url']))->values()->all();

                return $trustee;
            });

        $goals = $this->visibleItems($this->decode($settings->get('about_goals')))
            ->map(function (array $goal): array {
                $goal['icon_image'] = $this->imageById($goal['icon_media_id'] ?? null);

                return $goal;
            });
        $planItems = $this->visibleItems($this->decode($settings->get('about_plan_items')));
        $patronImage = $this->imageById($settings->get('about_patron_image_media_id'));
        $donateUrl = $this->publicUrl($settings->get('about_inspiration_button_url'));

        return view('pages.about-us', [
            'page' => $page,
            'settings' => $siteSettings,
            'content' => [
                'hero' => [
                    'visible' => $this->boolean($settings, 'about_show_hero', true),
                    'overlay' => $this->boolean($settings, 'about_hero_overlay', true),
                    'breadcrumb_title' => $settings->get('about_hero_breadcrumb_title'),
                    'text' => $settings->get('about_hero_text'),
                    'image' => $heroImage,
                ],
                'inspiration' => [
                    'visible' => $this->boolean($settings, 'about_show_inspiration', true),
                    'eyebrow' => $settings->get('about_inspiration_eyebrow'),
                    'heading' => $settings->get('about_inspiration_heading'),
                    'paragraphs' => $this->strings($this->decode($settings->get('about_inspiration_paragraphs'))),
                    'images' => $inspirationImages,
                    'button_visible' => $this->boolean($settings, 'about_inspiration_button_visible', true),
                    'button_label' => $settings->get('about_inspiration_button_label'),
                    'button_url' => $donateUrl,
                ],
                'patron' => [
                    'visible' => $this->boolean($settings, 'about_show_patron', true),
                    'eyebrow' => $settings->get('about_patron_eyebrow'),
                    'name' => $settings->get('about_patron_name'),
                    'designation' => $settings->get('about_patron_designation'),
                    'biography' => $this->strings($this->decode($settings->get('about_patron_biography'))),
                    'image' => $patronImage,
                ],
                'trustees' => [
                    'visible' => $this->boolean($settings, 'about_show_trustees', true),
                    'eyebrow' => $settings->get('about_trustees_eyebrow'),
                    'heading' => $settings->get('about_trustees_heading'),
                    'items' => $trustees,
                ],
                'goals' => [
                    'visible' => $this->boolean($settings, 'about_show_goals', true),
                    'eyebrow' => $settings->get('about_goals_eyebrow'),
                    'heading' => $settings->get('about_goals_heading'),
                    'intro' => $settings->get('about_goals_intro'),
                    'items' => $goals,
                ],
                'heart' => [
                    'visible' => $this->boolean($settings, 'about_show_heart', true),
                    'eyebrow' => $settings->get('about_heart_eyebrow'),
                    'heading' => $settings->get('about_heart_heading'),
                    'paragraphs' => $this->strings($this->decode($settings->get('about_heart_paragraphs'))),
                ],
                'plan' => [
                    'visible' => $this->boolean($settings, 'about_show_plan', true),
                    'eyebrow' => $settings->get('about_plan_eyebrow'),
                    'heading' => $settings->get('about_plan_heading'),
                    'intro' => $settings->get('about_plan_intro'),
                    'items' => $planItems,
                ],
                'charity' => [
                    'visible' => $this->boolean($settings, 'about_show_charity_purpose', true),
                    'heading' => $settings->get('about_charity_heading'),
                    'content' => $settings->get('about_charity_content'),
                    'number' => $settings->get('about_charity_number'),
                ],
            ],
            'seoTitle' => $seoTitle,
            'metaDescription' => $metaDescription,
            'ogTitle' => $page->og_title ?: $seoTitle,
            'ogDescription' => $page->og_description ?: $metaDescription,
            'canonicalUrl' => $page->canonical_url ?: url('/about-us/'),
            'ogImageUrl' => $heroImage['url'] ?? null,
        ]);
    }

    private function imageById(mixed $id): ?array
    {
        if (! is_numeric($id)) {
            return null;
        }

        return $this->image(Media::query()->find((int) $id));
    }

    private function image(?Media $media): ?array
    {
        if (! $media || ! Storage::disk($media->disk)->exists($media->path)) {
            return null;
        }

        return [
            'url' => Storage::disk($media->disk)->url($media->path),
            'alt' => $media->alt_text ?: $media->caption ?: 'Henna Foundation community image',
        ];
    }

    private function decode(?string $value): array
    {
        $decoded = json_decode($value ?: '[]', true);

        return is_array($decoded) ? $decoded : [];
    }

    private function strings(array $items): array
    {
        return collect($items)
            ->filter(fn ($item): bool => is_string($item) && filled($item))
            ->values()
            ->all();
    }

    private function visibleItems(array $items): Collection
    {
        return collect(array_values($items))
            ->filter(fn ($item): bool => is_array($item) && $this->itemVisible($item))
            ->sortBy(fn (array $item, int $index): array => [
                is_numeric($item['sort_order'] ?? null) ? (int) $item['sort_order'] : $index,
                $index,
            ])
            ->values();
    }

    private function itemVisible(array $item): bool
    {
        return filter_var($item['is_visible'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    private function boolean(Collection $settings, string $key, bool $default): bool
    {
        $value = $settings->get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function externalUrl(?string $value): ?string
    {
        if (! is_string($value) || ! filter_var($value, FILTER_VALIDATE_URL)) {
            return null;
        }

        return in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true)
            ? $value
            : null;
    }

    private function publicUrl(?string $value): ?string
    {
        if (! is_string($value) || blank($value)) {
            return null;
        }

        if (Str::startsWith($value, '/') && ! Str::startsWith($value, '//')) {
            return $value;
        }

        return $this->externalUrl($value);
    }
}
