@extends('layouts.app')

@section('title', 'Blog'.($settings->get('site_name') ? ' | '.$settings->get('site_name') : ''))
@section('meta_description', $settings->get('blog_page_description', ''))
@section('canonical_url', url('/blog/'))

@section('content')
    <section class="section-pad">
        <div class="container">
            <x-section-heading :eyebrow="$settings->get('home_blog_eyebrow', 'OUR BLOG')" title="Blog" :description="$settings->get('blog_page_description', '')" centered />
            @if ($posts->isEmpty())
                <p class="empty-state">{{ $settings->get('blog_page_empty', '') }}</p>
            @else
                <div class="project-grid">
                    @foreach ($posts as $post)
                        @php($image = $post->ogImage ?: $post->mediaAttachments->first())
                        <article class="project-card">
                            @if ($image)
                                <img class="project-card__image" src="{{ \Illuminate\Support\Facades\Storage::disk($image->disk)->url($image->path) }}" alt="{{ $image->alt_text ?: $post->title }}" loading="lazy">
                            @endif
                            <div class="project-card__content">
                                <h2>{{ $post->title }}</h2>
                                @if ($post->excerpt)<p>{{ $post->excerpt }}</p>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="pagination">{{ $posts->links() }}</div>
            @endif
        </div>
    </section>
@endsection
