<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\WebsiteSetting;
use App\Http\Controllers\Concerns\LoadsPublicSiteSettings;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BlogController extends Controller
{
    use LoadsPublicSiteSettings;

    public function __invoke(): View
    {
        $posts = BlogPost::query()
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->with(['ogImage', 'mediaAttachments' => fn ($query) => $query->orderByPivot('sort_order')])
            ->latest('published_at')
            ->latest('id')
            ->paginate(12);

        $settings = WebsiteSetting::query()->pluck('value', 'key');
        $settings['home_nav_items'] = json_decode($settings->get('home_nav_items', '[]'), true) ?: [];
        $settings['footer_links'] = json_decode($settings->get('footer_links', '[]'), true) ?: [];
        $logoMedia = Media::query()->find($settings->get('site_logo_media_id'));
        $settings['site_logo_url'] = $logoMedia ? Storage::disk($logoMedia->disk)->url($logoMedia->path) : null;
        $settings = $this->withFooterSettings($settings);

        return view('pages.blog', ['posts' => $posts, 'settings' => $settings]);
    }
}
