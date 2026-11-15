@props(['title', 'desc' => '', 'img', 'label' => '', 'color' => '#E51B6E', 'anchor' => '', 'delay' => '', 'url' => '', 'backImg' => ''])
@php $color = hex_color($color); @endphp
<article class="pcard pcard--flip reveal {{ $delay }}"@if($anchor) id="{{ $anchor }}"@endif tabindex="0" style="--flavor:{{ $color }}">
    <div class="pcard-inner">
        <div class="pcard-face pcard-front">
            <div class="pcard-media photo">
                <img src="{{ $img }}"{{ img_srcset($img, '(max-width: 640px) 90vw, 340px') }} alt="{{ $title }}" loading="lazy">
            </div>
            <div class="pcard-body">
                <h3>{{ $title }}</h3>
                @if ($desc)<p>{{ $desc }}</p>@endif
                @if ($label)<span class="flavor-pill" style="background:{{ $color }}">{{ $label }}</span>@endif
            </div>
            <span class="pcard-hint" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/></svg>
                اضغط للتفاصيل
            </span>
        </div>
        <div class="pcard-face pcard-back">
            <div class="pcard-back-media">
                <img src="{{ $backImg ?: $img }}"{{ img_srcset($backImg ?: $img, '300px') }} alt="{{ $title.($backImg ? ' — كرتونة' : '') }}" loading="lazy">
            </div>
            @if ($backImg)
                <span class="pcard-back-tag">متوفّر بالجملة · كرتونة 24 قطعة</span>
            @elseif ($label)
                <span class="pcard-back-tag">{{ $label }}</span>
            @endif
            <h3>{{ $title }}</h3>
            @if ($desc)<p>{{ $desc }}</p>@endif
            <div class="pcard-actions">
                @if ($url)<a href="{{ $url }}" class="btn btn-yellow">تفاصيل المنتج</a>@endif
                <a href="{{ url('/contact') }}" class="btn pcard-btn-ghost">اطلب الآن</a>
            </div>
        </div>
    </div>
</article>
