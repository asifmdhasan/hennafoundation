@props(['items' => []])

<ul class="primary-navigation__list">
    @foreach ($items as $item)
        @php($itemPath = $item['url'] ?? '/')
        @php($itemUrl = url($itemPath))
        @if (str_starts_with($itemPath, '/') && !str_starts_with($itemPath, '//') && $itemPath !== '/')
            @php($itemUrl = rtrim($itemUrl, '/').'/')
        @endif
        @php($isCurrent = rtrim($itemUrl, '/') === rtrim(url()->current(), '/'))
        <li @class(['primary-navigation__donate' => (bool) ($item['is_cta'] ?? false)])>
            <a @class(['button button--primary' => (bool) ($item['is_cta'] ?? false), 'is-active' => $isCurrent]) @if ($isCurrent) aria-current="page" @endif href="{{ $itemUrl }}">{{ $item['label'] ?? '' }}</a>
        </li>
    @endforeach
</ul>
