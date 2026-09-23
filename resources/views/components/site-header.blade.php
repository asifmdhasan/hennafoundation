@props(['settings' => collect()])
<div class="site-topbar">
    <div class="container site-topbar__inner">
        <div class="site-topbar__social" aria-label="Social media">
            @foreach ([['facebook', 'Facebook'], ['twitter', 'Twitter'], ['instagram', 'Instagram'], ['youtube', 'YouTube']] as [$network, $label])
                @php($socialUrl = $settings->get('social_'.$network))
                @if ($socialUrl)<a class="social-icon social-icon--{{ $network }}" href="{{ $socialUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $label }}">@else<span class="social-icon social-icon--{{ $network }}" role="img" aria-label="{{ $label }}">@endif
                    @if ($network === 'facebook')<span class="social-icon__letter">f</span>
                    @elseif ($network === 'twitter')<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M20.7 7.1v.6c0 6.2-4.7 13.3-13.3 13.3-2.6 0-5-.8-7-2.1h1c2.1 0 4.1-.7 5.7-1.9a4.7 4.7 0 0 1-4.4-3.2 4.5 4.5 0 0 0 2.1-.1 4.7 4.7 0 0 1-3.8-4.6v-.1c.6.4 1.4.6 2.2.6A4.7 4.7 0 0 1 1.8 3.3a13.3 13.3 0 0 0 9.7 4.9 4.7 4.7 0 0 1 8-4.3 9.2 9.2 0 0 0 3-1.1 4.7 4.7 0 0 1-2.1 2.6 9.3 9.3 0 0 0 2.7-.7 10 10 0 0 1-2.4 2.4Z"/></svg>
                    @elseif ($network === 'instagram')<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.7" cy="6.7" r="1.2" fill="currentColor"/></svg>
                    @else<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21.6 7.1a2.7 2.7 0 0 0-1.9-1.9C18 4.7 12 4.7 12 4.7s-6 0-7.7.5a2.7 2.7 0 0 0-1.9 1.9A28 28 0 0 0 1.9 12a28 28 0 0 0 .5 4.9 2.7 2.7 0 0 0 1.9 1.9c1.7.5 7.7.5 7.7.5s6 0 7.7-.5a2.7 2.7 0 0 0 1.9-1.9 28 28 0 0 0 .5-4.9 28 28 0 0 0-.5-4.9ZM10 15.3V8.7l5.7 3.3-5.7 3.3Z"/></svg>@endif
                @if ($socialUrl)</a>@else</span>@endif
            @endforeach
        </div>
        <div class="site-topbar__contact">
            @if ($settings->get('contact_email'))<a href="mailto:{{ $settings->get('contact_email') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6.5h18v13H3zM3 7l9 7 9-7M7 3.5h10"/></svg>{{ $settings->get('contact_email') }}</a>@endif
            @if ($settings->get('address'))<span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 10c0 5-7 12-7 12S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.2"/></svg>{{ $settings->get('address') }}</span>@endif
        </div>
    </div>
</div>
<header class="site-header">
    <div class="container site-header__inner">
        <a class="site-brand" href="{{ route('home') }}/" aria-label="{{ $settings->get('site_name', 'Home') }} home">
            @if ($settings->get('site_logo_url'))
                <img src="{{ $settings->get('site_logo_url') }}" width="155" height="74" alt="{{ $settings->get('site_name', '') }}">
            @else
                <span class="site-brand__mark" aria-hidden="true">h</span>
                <span class="site-brand__words"><strong>HENNA</strong><span>FOUNDATION</span></span>
            @endif
        </a>
        <x-mobile-navigation />
        <nav id="site-navigation" class="primary-navigation" aria-label="Main navigation">
            <x-primary-navigation :items="$settings->get('home_nav_items', [])" />
        </nav>
    </div>
</header>
