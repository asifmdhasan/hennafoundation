<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Project;
use App\Models\BlogPost;
use App\Models\Media;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $projects = Project::query()
            ->where('status', 'published')
            ->where(fn ($query) => $query
                ->whereNull('published_at')
                ->orWhere('published_at', '<=', now()))
            ->with(['mediaAttachments' => fn ($query) => $query->orderByPivot('sort_order')])
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->orderByDesc('id')
            ->limit(3)
            ->get()
            ->sortBy('sort_order')
            ->values()
            ->map(function (Project $project): array {
                $media = $project->mediaAttachments->first();

                return [
                    'title' => $project->title,
                    'summary' => $project->summary,
                    'image_url' => $media
                        ? Storage::disk($media->disk)->url($media->path)
                        : null,
                    'image_alt' => $media?->alt_text ?: "Image for {$project->title}",
                ];
            });

        $pinkChaiPage = Page::query()
            ->where('slug', 'pink-chai-day-2025')
            ->where('status', 'published')
            ->where(fn ($query) => $query
                ->whereNull('published_at')
                ->orWhere('published_at', '<=', now()))
            ->with('ogImage')
            ->first(['title', 'excerpt', 'og_image_media_id']);

        $homePage = Page::query()
            ->where('slug', 'home')
            ->with(['ogImage', 'mediaAttachments' => fn ($query) => $query->orderByPivot('sort_order')])
            ->first();

        $settings = WebsiteSetting::query()->pluck('value', 'key');
        $settings['home_actions'] = json_decode($settings->get('home_actions', '[]'), true) ?: [];
        $settings['home_blog_paragraphs'] = json_decode($settings->get('home_blog_paragraphs', '[]'), true) ?: [];
        $settings['home_nav_items'] = json_decode($settings->get('home_nav_items', '[]'), true) ?: [];
        $settings['footer_links'] = json_decode($settings->get('footer_links', '[]'), true) ?: [];

        $heroImage = $homePage?->ogImage;
        $imageIds = collect($settings->get('home_actions', []))
            ->pluck('image_media_id')
            ->push($settings->get('home_main_cta_image_id'))
            ->filter()
            ->unique()
            ->values();
        $sectionMedia = $imageIds->isEmpty()
            ? collect()
            : Media::query()->whereIn('id', $imageIds)->get()->keyBy('id');
        $settings['home_actions'] = collect($settings->get('home_actions', []))
            ->map(function (array $card) use ($sectionMedia): array {
                $media = $sectionMedia->get($card['image_media_id'] ?? null);

                return [
                    ...$card,
                    'image_url' => $media ? Storage::disk($media->disk)->url($media->path) : null,
                    'image_alt' => $media?->alt_text ?: $media?->caption ?: ($card['title'] ?? ''),
                ];
            })
            ->all();
        $ctaMedia = $sectionMedia->get($settings->get('home_main_cta_image_id'));
        $logoMedia = Media::query()->find($settings->get('site_logo_media_id'));
        $settings['site_logo_url'] = $logoMedia ? Storage::disk($logoMedia->disk)->url($logoMedia->path) : null;
        $gallery = $homePage?->mediaAttachments
            ->filter(fn ($media) => $media->pivot->role !== 'hero')
            ->map(fn ($media) => [
                'url' => Storage::disk($media->disk)->url($media->path),
                'alt' => $media->alt_text ?: $media->caption ?: 'Henna Foundation gallery image',
                'caption' => $media->caption,
            ])
            ->values() ?? collect();

        $blogPosts = BlogPost::query()
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->with('ogImage')
            ->latest('published_at')
            ->latest('id')
            ->limit(3)
            ->get();

        return view('pages.home', [
            'projects' => $projects,
            'pinkChaiPage' => $pinkChaiPage,
            'settings' => $settings,
            'heroImage' => $heroImage ? Storage::disk($heroImage->disk)->url($heroImage->path) : null,
            'campaignImage' => $pinkChaiPage?->ogImage,
            'mainCtaImage' => $ctaMedia ? Storage::disk($ctaMedia->disk)->url($ctaMedia->path) : null,
            'gallery' => $gallery,
            'blogPosts' => $blogPosts,
        ]);
    }
}
