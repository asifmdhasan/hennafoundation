@extends('layouts.app')

@php
    $featuredMedia = $post->ogImage ?: $post->mediaAttachments->first();
    $featuredImageUrl = $post->featured_image_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image_path)
        : ($featuredMedia ? \Illuminate\Support\Facades\Storage::disk($featuredMedia->disk)->url($featuredMedia->path) : null);
@endphp

@section('title', ($post->meta_title ?: $post->title).($settings->get('site_name') ? ' | '.$settings->get('site_name') : ''))
@section('meta_description', $post->meta_description ?: $post->excerpt)
@section('canonical_url', $post->canonical_url ?: route('blog.show', $post->slug))
@section('og_title', $post->og_title ?: $post->title)
@section('og_description', $post->og_description ?: ($post->meta_description ?: $post->excerpt))
@if ($featuredImageUrl)
    @section('og_image', $featuredImageUrl)
@endif

@section('content')
    <div class="blog-post-page section-pad">
        <div class="container blog-post-layout">
            <article class="blog-article">
                <nav class="blog-breadcrumb blog-breadcrumb--dark" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><a href="{{ route('blog.index') }}">Blog</a><span aria-hidden="true">/</span><span>{{ $post->title }}</span>
                </nav>
                @if ($featuredImageUrl)
                    <img class="blog-article__image" src="{{ $featuredImageUrl }}" alt="{{ $featuredMedia?->alt_text ?: $post->title }}">
                @endif
                <h1 class="blog-article__title">{{ $post->title }}</h1>
                <div class="blog-article__meta">
                    <span>{{ ($post->published_at ?: $post->created_at)->timezone('Asia/Dhaka')->format('F j, Y') }}</span>
                    <span aria-hidden="true">·</span>
                    @if ($post->categories->isNotEmpty())
                        @foreach ($post->categories as $category)
                            <a href="{{ route('blog.index', ['category' => $category->slug]) }}">{{ $category->name }}</a>
                        @endforeach
                    @else
                        <span>Blog</span>
                    @endif
                    @if ($post->author)<span aria-hidden="true">·</span><span>{{ $post->author->name }}</span>@endif
                </div>
                @if ($post->excerpt)<p class="blog-article__excerpt">{{ $post->excerpt }}</p>@endif
                <div class="blog-article__content">{!! $post->content !!}</div>
                @if ($post->categories->isNotEmpty())
                    <div class="blog-article__categories"><strong>Categories:</strong> @foreach ($post->categories as $category)<a href="{{ route('blog.index', ['category' => $category->slug]) }}">{{ $category->name }}</a>@endforeach</div>
                @endif
                <a class="blog-back-link" href="{{ route('blog.index') }}">← Back to all posts</a>
            </article>

            <aside class="blog-sidebar" aria-label="Blog tools">
                <form class="blog-search" action="{{ route('blog.index') }}" method="get" role="search">
                    <label class="visually-hidden" for="blog-search-input">Search blog posts</label>
                    <input id="blog-search-input" type="search" name="q" placeholder="Search..." value="{{ request('q') }}">
                    <button type="submit" aria-label="Search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.6"/><path d="m16 16 5 5"/></svg></button>
                </form>
                <section class="blog-sidebar__section" aria-labelledby="blog-categories-heading">
                    <h2 id="blog-categories-heading">Categories</h2>
                    @if ($categories->isNotEmpty())
                        <ul>
                            @foreach ($categories as $category)<li><a href="{{ route('blog.index', ['category' => $category->slug]) }}">{{ $category->name }}</a></li>@endforeach
                        </ul>
                    @else
                        <p>Categories will appear here.</p>
                    @endif
                </section>
            </aside>
        </div>
    </div>
@endsection
