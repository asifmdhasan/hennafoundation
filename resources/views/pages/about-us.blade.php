@extends('layouts.app')

@section('title', $seoTitle)
@section('meta_description', $metaDescription)
@section('canonical_url', $canonicalUrl)
@section('og_title', $page->og_title ?: $seoTitle)

@section('content')
    <section class="section-pad content-page" aria-labelledby="page-title">
        <div class="container content-page__container">
            <header class="section-heading section-heading--centered">
                <h1 id="page-title">{{ $page->title }}</h1>
                @if (filled($page->excerpt))
                    <p class="section-heading__description">{{ $page->excerpt }}</p>
                @endif
            </header>

            @if (filled(trim(strip_tags((string) $page->content))))
                <div class="content-page__body">{!! $page->content !!}</div>
            @endif
        </div>
    </section>
@endsection
