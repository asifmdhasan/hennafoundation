@extends('layouts.app')

@section('title', 'Blog'.($settings->get('site_name') ? ' | '.$settings->get('site_name') : ''))
@section('meta_description', $settings->get('blog_page_description', ''))
@section('canonical_url', url('/blog/'))

@section('content')
    @php
        $heroPost = $posts->first();
        $heroMedia = $heroPost?->ogImage ?: $heroPost?->mediaAttachments->first();
        $heroImage = $heroPost?->featured_image_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($heroPost->featured_image_path)
            : ($heroMedia ? \Illuminate\Support\Facades\Storage::disk($heroMedia->disk)->url($heroMedia->path) : null);
    @endphp
    <section class="blog-hero" @if ($heroImage) style="--blog-hero-image: url('{{ $heroImage }}')" @endif aria-labelledby="blog-title">
        <div class="container blog-hero__inner">
            <nav class="blog-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><span>Blog</span></nav>
            <h1 id="blog-title">Blog</h1>
            @if (filled($settings->get('blog_page_description')))<p>{{ $settings->get('blog_page_description') }}</p>@endif
        </div>
    </section>

    <section class="blog-listing section-pad" aria-label="Blog posts">
        <div class="container">
            @if ($posts->isEmpty())
                <p class="empty-state">{{ $settings->get('blog_page_empty', 'No blog posts found.') }}</p>
            @else
                <div class="blog-grid">
                    @foreach ($posts as $post)
                        @php
                            $media = $post->ogImage ?: $post->mediaAttachments->first();
                            $imageUrl = $post->featured_image_path
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image_path)
                                : ($media ? \Illuminate\Support\Facades\Storage::disk($media->disk)->url($media->path) : null);
                        @endphp
                        <article class="blog-card">
                            <a class="blog-card__image-link" href="{{ route('blog.show', $post->slug) }}" aria-label="Read {{ $post->title }}">
                                @if ($imageUrl)
                                    <img class="blog-card__image" src="{{ $imageUrl }}" alt="{{ $media?->alt_text ?: $post->title }}" loading="lazy" decoding="async">
                                @else
                                    <span class="blog-card__image blog-card__image--placeholder" aria-hidden="true"></span>
                                @endif
                                @if ($post->categories->isNotEmpty())<span class="blog-card__badge">{{ $post->categories->first()->name }}</span>@endif
                            </a>
                            <div class="blog-card__body">
                                <span class="blog-card__avatar" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 22a8 8 0 0 1 16 0"/></svg></span>
                                <h2><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h2>
                                @if ($post->excerpt)<p>{{ $post->excerpt }}</p>@endif
                                <a class="blog-card__read-more" href="{{ route('blog.show', $post->slug) }}">Read more <span aria-hidden="true">»</span></a>
                            </div>
                            <footer class="blog-card__meta">
                                <time datetime="{{ ($post->published_at ?: $post->created_at)->toIso8601String() }}">{{ ($post->published_at ?: $post->created_at)->timezone('Asia/Dhaka')->format('F j, Y') }}</time>
                                <span aria-hidden="true">·</span><span>No comments</span>
                            </footer>
                        </article>
                    @endforeach
                </div>
                <nav class="blog-pagination" aria-label="Blog pages">{{ $posts->links() }}</nav>
            @endif
        </div>
    </section>
@endsection
