<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsPublicSiteSettings;
use App\Models\Trustee;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TrusteeController extends Controller
{
    use LoadsPublicSiteSettings;

    public function show(Trustee $trustee): View
    {
        abort_unless(
            $trustee->status === 'published' && (! $trustee->published_at || $trustee->published_at->isPast()),
            404,
        );

        $trustee->load('image');
        $imageUrl = $trustee->image && Storage::disk($trustee->image->disk)->exists($trustee->image->path)
            ? Storage::disk($trustee->image->disk)->url($trustee->image->path)
            : null;
        $siteSettings = $this->publicSiteSettings();
        $title = $trustee->name.($siteSettings->get('site_name') ? ' | '.$siteSettings->get('site_name') : '');

        return view('pages.trustee', [
            'trustee' => $trustee,
            'imageUrl' => $imageUrl,
            'settings' => $siteSettings,
            'seoTitle' => $title,
            'canonicalUrl' => route('trustees.show', $trustee->slug),
            'contentHtml' => (new HtmlSanitizer((new HtmlSanitizerConfig())->allowSafeElements()->allowRelativeLinks()))
                ->sanitize($trustee->content ?? ''),
        ]);
    }
}
