@props(['eyebrow', 'title', 'sub' => ''])
<div {{ $attributes->merge(['class' => 'sec-head reveal']) }}>
    <span class="eyebrow">{{ $eyebrow }}</span>
    <h2>{{ safe_html($title) }}</h2>
    @if ($sub)<p>{{ $sub }}</p>@endif
</div>
