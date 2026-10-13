<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
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
        $query = request()->string('q')->trim()->toString();
        $categorySlug = request()->string('category')->trim()->toString();

        $postsQuery = BlogPost::query()
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->with(['author', 'categories', 'ogImage', 'mediaAttachments' => fn ($query) => $query->orderByPivot('sort_order')]);

        if ($query !== '') {
            $postsQuery->where(fn ($builder) => $builder
                ->where('title', 'like', "%{$query}%")
                ->orWhere('excerpt', 'like', "%{$query}%"));
        }

        if ($categorySlug !== '') {
            $postsQuery->whereHas('categories', fn ($builder) => $builder->where('slug', $categorySlug));
        }

        $posts = $postsQuery->latest('published_at')->latest('id')->paginate(12)->withQueryString();
        $categories = Category::query()->whereHas('blogPosts', fn ($builder) => $builder
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now())))
            ->orderBy('name')->get();

        return view('pages.blog', [
            'posts' => $posts,
            'categories' => $categories,
            'activeCategory' => $categorySlug,
            'search' => $query,
            'settings' => $this->publicSettings(),
        ]);
    }

    public function show(BlogPost $blogPost): View
    {
        abort_unless(
            $blogPost->status === 'published' && (! $blogPost->published_at || $blogPost->published_at->lte(now())),
            404,
        );

        $blogPost->load(['author', 'categories', 'ogImage', 'mediaAttachments' => fn ($query) => $query->orderByPivot('sort_order')]);
        $categories = Category::query()->whereHas('blogPosts', fn ($builder) => $builder
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now())))
            ->orderBy('name')->get();

        return view('pages.blog-post', [
            'post' => $blogPost,
            'categories' => $categories,
            'settings' => $this->publicSettings(),
        ]);
    }

    private function publicSettings()
    {
        $settings = WebsiteSetting::query()->pluck('value', 'key');
        $settings['home_nav_items'] = json_decode($settings->get('home_nav_items', '[]'), true) ?: [];
        $settings['footer_links'] = json_decode($settings->get('footer_links', '[]'), true) ?: [];
        $logoMedia = Media::query()->find($settings->get('site_logo_media_id'));
        $settings['site_logo_url'] = $logoMedia ? Storage::disk($logoMedia->disk)->url($logoMedia->path) : null;

        return $this->withFooterSettings($settings);
    }
}
