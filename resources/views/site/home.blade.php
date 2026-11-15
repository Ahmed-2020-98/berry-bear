@extends('layouts.site')

@section('body_class', 'home')

@php $h = fn ($k) => content('home', $k); @endphp

@section('content')
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy reveal">
            <span class="hero-badge">{{ $h('hero_badge') }}</span>
            <h1>{{ safe_html($h('hero_title')) }}</h1>
            <p class="hero-lead">{{ $h('hero_lead') }}</p>
            <div class="hero-actions">
                <a href="{{ site_url($h('hero_cta1_url')) }}" class="btn btn-primary btn-lg">{{ $h('hero_cta1_label') }}</a>
                <a href="{{ site_url($h('hero_cta2_url')) }}" class="btn btn-outline btn-lg">{{ $h('hero_cta2_label') }}</a>
            </div>
            <div class="hero-tags">
                @foreach (flatten_text($h('hero_tags'), 'tag') as $tag)<span>{{ $tag }}</span>@endforeach
            </div>
        </div>
        <div class="hero-visual reveal d2">
            <div class="hero-blob"></div>
            <svg class="floaty-bear b1"><use href="#gummy"></use></svg>
            <svg class="floaty-bear b2"><use href="#gummy"></use></svg>
            <svg class="floaty-bear b3"><use href="#gummy"></use></svg>
            <svg class="floaty-bear b4"><use href="#gummy"></use></svg>
            @php $heroImg = media_url($h('hero_image'), 'life-pink.jpeg'); @endphp<img src="{{ $heroImg }}"{{ img_srcset($heroImg, '(max-width: 900px) 78vw, 380px') }} fetchpriority="high" alt="دباديب الجيلي من بيري بير في أجواء طبيعية" class="hero-pack">
        </div>
    </div>
    <div class="wave-divider" aria-hidden="true">
        <svg viewBox="0 0 1440 90" preserveAspectRatio="none"><path fill="#FFF7FB" d="M0,64 C240,10 480,10 720,42 C960,74 1200,90 1440,48 L1440,90 L0,90 Z"></path></svg>
    </div>
</section>

<x-site.trust-strip :badges="$h('trust_badges')" />

<section class="section" id="about">
    <div class="container about-wrap">
        <div class="about-visual reveal">
            <div class="frame photo">@php $aboutImg = media_url($h('about_image'), 'life-brown.jpeg'); @endphp<img src="{{ $aboutImg }}"{{ img_srcset($aboutImg, '(max-width: 900px) 92vw, 520px') }} loading="lazy" alt="منتجات بيري بير في أجواء البستان"></div>
            <div class="sticker"><b>{{ $h('about_sticker_num') }}</b> {{ $h('about_sticker_text') }}</div>
        </div>
        <div class="about-text reveal d1">
            <span class="eyebrow">{{ $h('about_eyebrow') }}</span>
            <h2>{{ safe_html($h('about_title')) }}</h2>
            <p>{{ $h('about_text') }}</p>
            <ul class="about-points">
                @foreach (flatten_text($h('about_points'), 'point') as $pt)<li><span class="ck">✓</span> {{ $pt }}</li>@endforeach
            </ul>
            <a href="{{ site_url($h('about_btn_url'), '/about') }}" class="btn btn-navy">{{ $h('about_btn_label') }}</a>
        </div>
    </div>
</section>

<section class="section section--cream">
    <div class="container">
        <x-site.sec-head :eyebrow="$h('vision_eyebrow')" :title="$h('vision_title')" :sub="$h('vision_sub')" />
        <x-site.vision-cards :cards="$h('vision_cards')" />
    </div>
</section>

<section class="section">
    <div class="container">
        <x-site.sec-head :eyebrow="$h('process_eyebrow')" :title="$h('process_title')" :sub="$h('process_sub')" />
        @foreach ($h('process_steps') as $i => $s)
            <div class="srow{{ $i % 2 === 1 ? ' reverse' : '' }} reveal">
                <div class="srow-media {{ $s['media'] ?? (['m1', 'm2', 'm3'][$i] ?? 'm1') }}">
                    <svg class="bear-big" style="color:{{ hex_color($s['color'] ?? '') }}"><use href="#gummy"></use></svg>
                    @if (! empty($s['e1']))<span class="m-emoji" style="top:16%;inset-inline-start:14%">{{ $s['e1'] }}</span>@endif
                    @if (! empty($s['e2']))<span class="m-emoji" style="bottom:18%;inset-inline-end:16%">{{ $s['e2'] }}</span>@endif
                </div>
                <div class="srow-text">
                    <div class="srow-icon">{{ $s['icon'] ?? '' }}</div>
                    <h3>{{ $s['title'] ?? '' }}</h3>
                    <p>{{ $s['text'] ?? '' }}</p>
                    <x-site.srow-list :text="$s['list'] ?? ''" />
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="section section--pink" id="products">
    <div class="container">
        <x-site.sec-head :eyebrow="$h('products_eyebrow')" :title="$h('products_title')" :sub="$h('products_sub')" />
        <div class="grid-3">
            <x-site.products-grid :products="$products" />
        </div>
        <div class="center" style="margin-top:44px">
            <a href="{{ url('/products') }}" class="btn btn-navy btn-lg">شاهد كل المنتجات</a>
        </div>
        @php $shop = array_filter((array) $h('shop_buttons'), fn ($b) => trim((string) ($b['label'] ?? '')) !== ''); @endphp
        @if ($shop)
            <div class="shop-links reveal">
                @foreach ($shop as $b)<a href="{{ site_url($b['url'] ?? '', '/products') }}" class="btn btn-outline">{{ $b['label'] }}</a>@endforeach
            </div>
        @endif
    </div>
</section>

<x-site.slogan-band :slogans="$h('slogans')" />

<section class="section section--tight">
    <div class="container">
        <div class="sec-head reveal" style="margin-bottom:36px">
            <span class="eyebrow">{{ $h('marquee_eyebrow') }}</span>
            <h2>{{ safe_html($h('marquee_title')) }}</h2>
        </div>
    </div>
    <div class="marquee reveal">
        <div class="marquee-track">
            @for ($pass = 0; $pass < 2; $pass++)
                @foreach ($h('marquee_chips') as $c)<span class="chip"><span class="dot" style="background:{{ hex_color($c['color'] ?? '') }}"></span> {{ $c['label'] ?? '' }}</span>@endforeach
            @endfor
        </div>
    </div>
</section>

<section class="section section--cream">
    <div class="container">
        <x-site.sec-head :eyebrow="$h('gallery_eyebrow')" :title="$h('gallery_title')" :sub="$h('gallery_sub')" />
        <div class="gallery-grid">
            @foreach ($h('gallery_images') as $i => $g)
                @php $src = media_url($g['img'] ?? '', 'life-pink.jpeg'); @endphp
                <div class="gitem{{ $i === 0 ? ' big' : '' }} photo" data-lightbox data-img="{{ $src }}" data-title="{{ $g['title'] ?? '' }}" data-cap="{{ $g['cap'] ?? '' }}"><img src="{{ $src }}"{{ img_srcset($src, $i === 0 ? '(max-width: 900px) 92vw, 600px' : '(max-width: 900px) 46vw, 300px') }} loading="lazy" alt="{{ $g['title'] ?? '' }}"></div>
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <x-site.cta-band :title="$h('cta_title')" :text="$h('cta_text')" :label="$h('cta_btn_label')" :url="$h('cta_btn_url')" />
    </div>
</section>

<section class="section section--navy">
    <div class="container">
        <x-site.sec-head :eyebrow="$h('why_eyebrow')" :title="$h('why_title')" :sub="$h('why_sub')" />
        <div class="grid-4">
            @foreach ($h('feature_cards') as $i => $f)
                <div class="fcard reveal {{ ['', 'd1', 'd2', 'd3'][$i] ?? '' }}">
                    <div class="ficon">{{ $f['icon'] ?? '' }}</div>
                    <h3>{{ $f['title'] ?? '' }}</h3>
                    <p>{{ $f['text'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<x-site.trust-reasons :eyebrow="$h('trust_reasons_eyebrow')" :title="$h('trust_reasons_title')" :sub="$h('trust_reasons_sub')" :reasons="$h('trust_reasons')" />

<section class="section">
    <div class="container">
        <x-site.sec-head :eyebrow="$h('reviews_eyebrow')" :title="$h('reviews_title')" />
        <div class="grid-3">
            @foreach ($testimonials as $i => $t)<x-site.review-card :t="$t" :delay="delay_class($i)" />@endforeach
        </div>
    </div>
</section>

<section class="section section--cream">
    <div class="container">
        <x-site.sec-head :eyebrow="$h('blog_eyebrow')" :title="$h('blog_title')" :sub="$h('blog_sub')" />
        <div class="grid-3">
            @foreach ($posts as $i => $post)<x-site.blog-card :post="$post" :delay="delay_class($i)" />@endforeach
        </div>
        <div class="center" style="margin-top:44px">
            <a href="{{ route('blog') }}" class="btn btn-navy btn-lg">عرض كل المقالات</a>
        </div>
    </div>
</section>
@endsection
