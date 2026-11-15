@extends('layouts.site')

@section('title', $post->title)
@section('description', $post->excerptText())

@section('content')
<x-site.page-hero :title="e($post->title)" :sub="$post->metaLine()" crumb="المدونة" />

<article class="post-single">
    <div class="container">
        @if ($post->cover)
            <div class="post-cover photo-fit"><img class="photo-fit-bg" src="{{ $post->coverUrl() }}"{{ img_srcset($post->coverUrl(), '320px') }} alt="" aria-hidden="true"><img class="photo-fit-main" src="{{ $post->coverUrl() }}"{{ img_srcset($post->coverUrl(), '(max-width: 800px) 94vw, 760px') }} alt="{{ $post->title }}"></div>
        @endif
        <div class="post-body">{{ safe_html($post->body) }}</div>
        <div class="post-nav">
            <span>@if ($prev)<a href="{{ $prev->url() }}" rel="prev">← المقال السابق</a>@endif</span>
            <span>@if ($next)<a href="{{ $next->url() }}" rel="next">المقال التالي →</a>@endif</span>
        </div>
    </div>
</article>

@if ($related->isNotEmpty())
<section class="section section--cream">
    <div class="container">
        <x-site.sec-head eyebrow="مقالات ذات صلة" title='اقرأ <span class="hl">أيضاً</span>' />
        <div class="grid-3">
            @foreach ($related as $i => $r)<x-site.blog-card :post="$r" :delay="delay_class($i)" />@endforeach
        </div>
    </div>
</section>
@endif
@endsection
