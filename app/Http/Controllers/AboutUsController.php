<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsPublicSiteSettings;
use App\Models\Page;
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
            ->firstOrFail();

        $settings = $this->publicSiteSettings();
        $title = $page->meta_title ?: $page->title.($settings->get('site_name') ? ' | '.$settings->get('site_name') : '');

        return view('pages.about-us', [
            'page' => $page,
            'settings' => $settings,
            'seoTitle' => $title,
            'metaDescription' => $page->meta_description ?: $page->excerpt,
            'canonicalUrl' => $page->canonical_url ?: url('/about-us/'),
        ]);
    }
}
