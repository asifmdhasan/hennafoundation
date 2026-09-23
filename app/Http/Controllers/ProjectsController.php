<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LoadsPublicSiteSettings;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectsController extends Controller
{
    use LoadsPublicSiteSettings;

    public function __invoke(): View
    {
        $settings = $this->publicSiteSettings();
        $projects = Project::query()
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->with(['mediaAttachments' => fn ($query) => $query->orderByPivot('sort_order')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Project $project): array {
                $image = $project->mediaAttachments->first(fn ($media) => $media->pivot->role === 'featured')
                    ?? $project->mediaAttachments->first();

                return [
                    'project' => $project,
                    'image_url' => $image ? Storage::disk($image->disk)->url($image->path) : null,
                    'image_alt' => $image?->alt_text ?: $image?->caption ?: "Image for {$project->title}",
                    'description' => filled($project->summary)
                        ? $project->summary
                        : Str::limit(trim(strip_tags((string) $project->content)), 260),
                ];
            });

        return view('pages.projects', [
            'projects' => $projects,
            'settings' => $settings,
            'seoTitle' => ($settings->get('projects_page_title') ?: 'Projects').($settings->get('site_name') ? ' | '.$settings->get('site_name') : ''),
            'metaDescription' => $settings->get('projects_page_description') ?: $settings->get('home_projects_description'),
            'canonicalUrl' => url('/projects/'),
            'emptyMessage' => $settings->get('projects_page_empty', $settings->get('home_empty_projects', '')),
        ]);
    }
}
