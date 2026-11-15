@extends('layouts.site')

@section('title', 'المنتجات والخدمات')

@php $p = fn ($k) => content('products', $k); @endphp

@section('content')
<x-site.page-hero :title="$p('pr_hero_title')" :sub="$p('pr_hero_sub')" :crumb="$collection->name ?? 'المنتجات والخدمات'" />

<section class="section">
    <div class="container">
        <x-site.sec-head :eyebrow="$p('pr_eyebrow')" :title="$collection ? e($collection->name) : $p('pr_title')" :sub="$collection->description ?? $p('pr_sub')" />
        <div class="grid-3">
            <x-site.products-grid :products="$products" :with-anchor="true" />
        </div>
    </div>
</section>

@unless ($collection)
<section class="section section--cream">
    <div class="container">
        <x-site.sec-head :eyebrow="$p('sv_eyebrow')" :title="$p('sv_title')" :sub="$p('sv_sub')" />
        @foreach ($p('services') as $i => $sv)
            <div class="srow{{ $i % 2 === 1 ? ' reverse' : '' }} reveal">
                @php $svImg = media_url($sv['img'] ?? '', 'life-purple.jpeg'); @endphp
                <div class="srow-media photo photo-fit">
                    <img class="photo-fit-bg" src="{{ $svImg }}"{{ img_srcset($svImg, '320px') }} loading="lazy" alt="" aria-hidden="true">
                    <img class="photo-fit-main" src="{{ $svImg }}"{{ img_srcset($svImg, '(max-width: 900px) 92vw, 560px') }} loading="lazy" alt="{{ $sv['title'] ?? '' }}">
                </div>
                <div class="srow-text">
                    <div class="srow-icon">{{ $sv['icon'] ?? '' }}</div>
                    <h3>{{ $sv['title'] ?? '' }}</h3>
                    <p>{{ $sv['text'] ?? '' }}</p>
                    <x-site.srow-list :text="$sv['list'] ?? ''" />
                </div>
            </div>
        @endforeach
    </div>
</section>
@endunless

<section class="section">
    <div class="container">
        <x-site.cta-band :title="$p('pr_cta_title')" :text="$p('pr_cta_text')" :label="$p('pr_cta_btn_label')" :url="$p('pr_cta_btn_url')" />
    </div>
</section>
@endsection
