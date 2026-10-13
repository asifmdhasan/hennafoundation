@extends('layouts.app')

@section('title', $seoTitle)
@section('canonical_url', $canonicalUrl)
@section('content')
    <section class="trustee-detail section-pad" aria-labelledby="trustee-detail-title">
        <div class="container trustee-detail__layout">
            @if ($imageUrl)
                <div class="trustee-detail__media">
                    <img src="{{ $imageUrl }}" alt="{{ $trustee->image?->alt_text ?: $trustee->name }}" decoding="async">
                </div>
            @endif
            <div class="trustee-detail__copy">
                <p class="eyebrow">Board of Trustees</p>
                <h1 id="trustee-detail-title">{{ $trustee->name }}</h1>
                @if (filled($trustee->designation))
                    <p class="trustee-detail__designation">{{ $trustee->designation }}</p>
                @endif
                @if (filled($contentHtml))
                    <div class="trustee-detail__content">{!! $contentHtml !!}</div>
                @endif
            </div>
        </div>
    </section>
@endsection
