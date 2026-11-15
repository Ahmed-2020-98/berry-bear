@extends('layouts.site')

@section('title', 'من نحن')

@php $a = fn ($k) => content('about', $k); @endphp

@section('content')
<x-site.page-hero :title="$a('ab_hero_title')" :sub="$a('ab_hero_sub')" crumb="من نحن" />

<section class="section">
    <div class="container about-wrap">
        <div class="about-visual reveal">
            <div class="frame photo"><img src="{{ media_url($a('ab_story_image'), 'life-blue.jpeg') }}" alt="منتجات بيري بير في أجواء البستان"></div>
            <div class="sticker"><b>{{ $a('ab_sticker_num') }}</b> {{ $a('ab_sticker_text') }}</div>
        </div>
        <div class="about-text reveal d1">
            <span class="eyebrow">{{ $a('ab_story_eyebrow') }}</span>
            <h2>{{ safe_html($a('ab_story_title')) }}</h2>
            <p>{{ $a('ab_story_p1') }}</p>
            <p>{{ $a('ab_story_p2') }}</p>
            <ul class="about-points">
                @foreach (flatten_text($a('ab_points'), 'point') as $pt)<li><span class="ck">✓</span> {{ $pt }}</li>@endforeach
            </ul>
            <a href="{{ site_url($a('ab_btn_url'), '/contact') }}" class="btn btn-navy">{{ $a('ab_btn_label') }}</a>
        </div>
    </div>
</section>

<section class="section section--navy section--tight">
    <div class="container">
        <div class="stats">
            @foreach ($a('ab_stats') as $i => $st)
                <div class="stat reveal {{ ['', 'd1', 'd2', 'd3'][$i % 4] }}">
                    <b data-count="{{ $st['value'] ?? 0 }}" data-suffix="{{ $st['suffix'] ?? '' }}">0</b>
                    <span>{{ $st['label'] ?? '' }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section section--cream">
    <div class="container">
        <x-site.sec-head :eyebrow="$a('ab_vmv_eyebrow')" :title="$a('ab_vmv_title')" :sub="$a('ab_vmv_sub')" />
        <x-site.vision-cards :cards="content('home', 'vision_cards')" />
    </div>
</section>

<section class="section">
    <div class="container">
        <x-site.sec-head :eyebrow="$a('ab_promise_eyebrow')" :title="$a('ab_promise_title')" :sub="$a('ab_promise_sub')" />
        <div class="grid-3">
            @foreach ($a('ab_promise') as $i => $f)
                <div class="fcard reveal {{ delay_class($i) }}">
                    <div class="ficon">{{ $f['icon'] ?? '' }}</div>
                    <h3>{{ $f['title'] ?? '' }}</h3>
                    <p>{{ $f['text'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
        @if ($stmt = $a('ab_promise_statement'))
            <p class="center reveal" style="max-width:760px;margin:40px auto 0;font-family:var(--font-display);font-weight:700;font-size:1.25rem;color:var(--pink);">🍓 {{ $stmt }}</p>
        @endif
    </div>
</section>

<section class="section section--cream">
    <div class="container">
        @if ($mw = $a('manager_word_text'))
            <div class="manager-word reveal">
                <span class="eyebrow">{{ $a('manager_word_eyebrow') }}</span>
                <p class="mw-text">{{ $mw }}</p>
                <div class="mw-who">
                    <b>{{ $a('manager_name') }}</b>
                    <span>{{ $a('manager_role') }}</span>
                </div>
            </div>
        @endif
    </div>
</section>

<section class="section section--cream">
    <div class="container">
        <x-site.cta-band :title="$a('ab_cta_title')" :text="$a('ab_cta_text')" :label="$a('ab_cta_btn_label')" :url="$a('ab_cta_btn_url')" />
    </div>
</section>
@endsection
