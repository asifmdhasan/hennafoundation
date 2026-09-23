@props(['settings'])

<footer class="site-footer">
    <div class="container site-footer__columns">
        <section class="site-footer__about" aria-label="{{ $settings->get('footer_heading', 'About Henna Foundation') }}">
            <a class="site-footer__logo" href="{{ route('home') }}/" aria-label="{{ $settings->get('footer_heading', 'Henna Foundation') }} home">
                @if ($settings->get('site_logo_url'))
                    <img src="{{ $settings->get('site_logo_url') }}" alt="{{ $settings->get('footer_heading', 'Henna Foundation') }}" width="210" height="92" loading="lazy">
                @else
                    <span class="site-brand__mark" aria-hidden="true">h</span><span class="site-brand__words">{{ $settings->get('footer_heading', 'Henna Foundation') }}</span>
                @endif
            </a>
            @if ($settings->get('purpose_statement'))<p>{{ $settings->get('purpose_statement') }}</p>@endif
            @if ($settings->get('footer_text'))<p>{{ $settings->get('footer_text') }}</p>@endif
            @if ($settings->get('charity_number'))<p class="site-footer__charity">{{ $settings->get('footer_charity_label', '') }}: {{ $settings->get('charity_number') }}</p>@endif
        </section>

        <nav aria-label="Footer navigation" class="site-footer__links">
            <h2>{{ $settings->get('footer_links_heading', '') }}</h2>
            <ul>
                @foreach ($settings->get('footer_links', []) as $link)
                    @php($linkPath = $link['url'] ?? '/')
                    @php($linkUrl = url($linkPath))
                    @if (str_starts_with($linkPath, '/') && !str_starts_with($linkPath, '//') && $linkPath !== '/')
                        @php($linkUrl = rtrim($linkUrl, '/').'/')
                    @endif
                    <li><a href="{{ $linkUrl }}">{{ $link['label'] ?? '' }}</a></li>
                @endforeach
            </ul>
        </nav>

        <section class="site-footer__contact" aria-labelledby="footer-contact-title">
            <h2 id="footer-contact-title">{{ $settings->get('footer_contact_heading', '') }}</h2>
            <div class="footer-contact-item"><span class="footer-contact-item__icon" aria-hidden="true">◎</span><span><strong>{{ $settings->get('footer_website_label', 'Website') }}</strong><a href="{{ $settings->get('site_website_url', url('/')) }}">{{ $settings->get('site_website_display', parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost()) }}</a></span></div>
            @if ($settings->get('contact_email'))<div class="footer-contact-item"><span class="footer-contact-item__icon" aria-hidden="true">✉</span><span><strong>{{ $settings->get('footer_email_label', 'Email') }}</strong><a href="mailto:{{ $settings->get('contact_email') }}">{{ $settings->get('contact_email') }}</a></span></div>@endif
            @if ($settings->get('address'))<div class="footer-contact-item"><span class="footer-contact-item__icon" aria-hidden="true">⌖</span><span><strong>{{ $settings->get('footer_address_label', 'Address') }}</strong><address>{{ $settings->get('address') }}</address></span></div>@endif
        </section>
    </div>

    <div class="site-footer__bottom">
        <div class="container site-footer__bottom-inner">
            <ul class="site-footer__social" aria-label="Social media">
                @if ($settings->get('social_youtube'))<li><a class="social-icon social-icon--youtube" href="{{ $settings->get('social_youtube') }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $settings->get('social_youtube_label', 'YouTube') }}">▶</a></li>@endif
            </ul>
            @if ($settings->get('site_name'))<p>Copyright © {{ now()->year }} {{ $settings->get('site_name') }} {{ $settings->get('footer_copyright_text', '') }}</p>@endif
            @if ($settings->get('footer_credit'))<p>{{ $settings->get('footer_credit') }}</p>@endif
        </div>
    </div>
</footer>
