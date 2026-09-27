@extends('layouts.app')

@section('title', $seoTitle)
@section('meta_description', $metaDescription)
@section('canonical_url', $canonicalUrl)

@section('content')
    <section class="section-pad projects-listing" aria-labelledby="projects-title">
        <div class="container">
            <x-section-heading
                id="projects-title"
                :eyebrow="$settings->get('home_projects_eyebrow', '')"
                title="Projects"
                :description="$settings->get('home_projects_description', '')"
                centered
            />

            @if ($projects->isEmpty())
                @if (filled($emptyMessage))
                    <p class="empty-state">{{ $emptyMessage }}</p>
                @endif
            @else
                <div class="project-grid">
                    @foreach ($projects as $item)
                        @php($project = $item['project'])
                        <article class="project-card">
                            @if ($item['image_url'])
                                <img
                                    class="project-card__image"
                                    src="{{ $item['image_url'] }}"
                                    width="768"
                                    alt="{{ $item['image_alt'] }}"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @else
                                <div class="project-card__image project-card__image--placeholder" aria-hidden="true"></div>
                            @endif
                            <div class="project-card__content">
                                <h2>{{ $project->title }}</h2>
                                @if (filled($item['description']))
                                    <p>{{ $item['description'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
