@extends('layouts.site')

@section('title', $product->title)
@section('description', $product->excerpt)

@section('content')
<x-site.page-hero :title="e($product->title)" :sub="$product->flavor_label" crumb="المنتجات" />

<section class="section">
    <div class="container about-wrap">
        <div class="about-visual reveal">
            <div class="frame photo">
                <img src="{{ $product->imageUrl() }}"{{ img_srcset($product->imageUrl(), '(max-width: 900px) 92vw, 560px') }} alt="{{ $product->title }}">
            </div>
            @if ($product->cartonUrl())
                <div class="carton-frame">
                    <img src="{{ $product->cartonUrl() }}"{{ img_srcset($product->cartonUrl(), '(max-width: 900px) 60vw, 320px') }} loading="lazy" alt="{{ $product->title }} — كرتونة">
                    <span>متوفّر بالجملة</span>
                </div>
            @endif
        </div>
        <div class="about-text reveal d1">
            @if ($product->flavor_label)
                <span class="flavor-pill" style="background:{{ hex_color($product->flavor_color) }}; margin-bottom:16px;">{{ $product->flavor_label }}</span>
            @endif
            <h2>{{ $product->title }}</h2>
            <div>{{ safe_html($product->body) }}</div>
            <p style="margin-top:20px">
                <a href="{{ url('/products') }}" class="btn btn-outline">كل المنتجات</a>
                <a href="{{ url('/contact') }}" class="btn btn-primary">اطلب الآن</a>
            </p>
        </div>
    </div>
</section>
@endsection
