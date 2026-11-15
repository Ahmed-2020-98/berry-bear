@props(['post', 'delay' => ''])
<article class="bcard reveal {{ $delay }}">
    <div class="bcard-media photo photo-fit">
        <img class="photo-fit-bg" src="{{ $post->coverUrl() }}"{{ img_srcset($post->coverUrl(), '160px') }} loading="lazy" alt="" aria-hidden="true">
        @if ($post->category)<span class="cat">{{ $post->category }}</span>@endif
        <img class="photo-fit-main" src="{{ $post->coverUrl() }}"{{ img_srcset($post->coverUrl(), '(max-width: 640px) 92vw, 380px') }} loading="lazy" alt="{{ $post->title }}">
    </div>
    <div class="bcard-body">
        <div class="meta">{{ $post->metaLine() }}</div>
        <h3><a href="{{ $post->url() }}">{{ $post->title }}</a></h3>
        @if ($post->excerptText())<p>{{ $post->excerptText() }}</p>@endif
        <a href="{{ $post->url() }}" class="more">اقرأ المزيد ←</a>
    </div>
</article>
