@props(['settings'])

<footer class="site-footer">
    <div class="container site-footer__columns">
        <section class="site-footer__about" aria-label="{{ $settings->get('footer_heading', 'Henna Foundation') }}">
            <a class="site-footer__logo" href="{{ route('home') }}" aria-label="{{ $settings->get('footer_heading', 'Henna Foundation') }} home">
                @if ($settings->get('footer_logo_url'))
                    <img src="{{ $settings->get('footer_logo_url') }}" alt="{{ $settings->get('footer_heading', 'Henna Foundation') }}" width="310" height="100" loading="lazy">
                @else
                    <span class="site-brand__mark" aria-hidden="true">h</span><span class="site-brand__words">{{ $settings->get('footer_heading', 'Henna Foundation') }}</span>
                @endif
            </a>
            @if ($settings->get('footer_text'))
                <div class="site-footer__description">{!! (new \Symfony\Component\HtmlSanitizer\HtmlSanitizer((new \Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig())->allowSafeElements()->allowRelativeLinks()))->sanitize($settings->get('footer_text')) !!}</div>
            @elseif ($settings->get('purpose_statement'))
                <p>{{ $settings->get('purpose_statement') }}</p>
            @endif
            @if ($settings->get('charity_number') && !str_contains(strip_tags($settings->get('footer_text', '')), (string) $settings->get('charity_number')))
                <p class="site-footer__charity"><strong>{{ $settings->get('footer_charity_label', 'Registered Charity Number') }}: {{ $settings->get('charity_number') }}</strong></p>
            @endif
        </section>

        <nav aria-label="Footer navigation" class="site-footer__links">
            <h2>{{ $settings->get('footer_links_heading', 'Quick Links') }}</h2>
            <ul>
                @foreach ($settings->get('footer_links', []) as $link)
                    @php($linkPath = $link['url'] ?? '/')
                    @php($linkUrl = preg_match('/^(https?:|mailto:|tel:|#)/i', $linkPath) ? $linkPath : url($linkPath))
                    @if (str_starts_with($linkPath, '/') && !str_starts_with($linkPath, '//') && $linkPath !== '/')
                        @php($linkUrl = rtrim($linkUrl, '/').'/')
                    @endif
                    <li><a href="{{ $linkUrl }}">{{ $link['label'] ?? '' }}</a></li>
                @endforeach
            </ul>
        </nav>

        <section class="site-footer__contact" aria-labelledby="footer-contact-title">
            <h2 id="footer-contact-title">{{ $settings->get('footer_contact_heading', 'Contact Us') }}</h2>
            @foreach ($settings->get('footer_contact_items', []) as $item)
                @php($type = strtolower($item['type'] ?? ''))
                <div class="footer-contact-item">
                    @if (!empty($item['icon_url']))
                        <span class="footer-contact-item__icon"><img src="{{ $item['icon_url'] }}" alt="" loading="lazy"></span>
                    @else
                        <span class="footer-contact-item__icon" aria-hidden="true">{{ match ($type) { 'website' => '◎', 'email' => '✉', 'telephone' => '☎', 'address' => '⌖', default => '•' } }}</span>
                    @endif
                    <span>
                        <strong>{{ $item['label'] ?? '' }}</strong>
                        @if (filled($item['url'] ?? ''))
                            <a href="{{ $item['url'] }}">{{ $item['value'] ?? '' }}</a>
                        @else
                            <address>{{ $item['value'] ?? '' }}</address>
                        @endif
                    </span>
                </div>
            @endforeach
        </section>
    </div>

    <div class="site-footer__bottom">
        <div class="container site-footer__bottom-inner">
            <ul class="site-footer__social" aria-label="Social media">
                @foreach ($settings->get('footer_social_items', []) as $item)
                    @if (filled($item['url'] ?? ''))
                        <li><a class="social-icon" href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $item['platform'] ?? 'Social media' }}">
                            @if (!empty($item['icon_url']))<img src="{{ $item['icon_url'] }}" alt="" loading="lazy">@else<span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($item['platform'] ?? 'S', 0, 1)) }}</span>@endif
                        </a></li>
                    @endif
                @endforeach
            </ul>
            @php($copyright = $settings->get('footer_copyright_text', ''))
            @php($copyright = preg_replace('/\bCopyright\s+\d{4}/i', 'Copyright '.now()->year, $copyright))
            <p>{{ str_contains(strtolower($copyright), 'copyright') ? $copyright : 'Copyright '.now()->year.' © '.$settings->get('site_name', 'Henna Foundation').' '.$copyright }}@if ($settings->get('footer_credit') && strtolower(trim($settings->get('footer_credit'))) !== strtolower(trim($settings->get('footer_developer_text', 'Developed by').' '.$settings->get('footer_developer_name', '')))) <span> · {{ $settings->get('footer_credit') }}</span>@endif</p>
            @if ($settings->get('footer_developer_name'))
                <p>{{ $settings->get('footer_developer_text', 'Developed by') }} <a href="{{ $settings->get('footer_developer_url', 'https://printagraphy.com/') }}" target="_blank" rel="noopener noreferrer">{{ $settings->get('footer_developer_name') }}</a></p>
            @elseif ($settings->get('footer_credit'))
                <p>{{ $settings->get('footer_credit') }}</p>
            @endif
        </div>
    </div>
</footer>
