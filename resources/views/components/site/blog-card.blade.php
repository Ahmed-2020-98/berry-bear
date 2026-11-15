@props(['post', 'delay' => ''])
<article class="bcard reveal {{ $delay }}">
    <div class="bcard-media photo">
        @if ($post->category)<span class="cat">{{ $post->category }}</span>@endif
        <img src="{{ $post->coverUrl() }}" alt="{{ $post->title }}">
    </div>
    <div class="bcard-body">
        <div class="meta">{{ $post->metaLine() }}</div>
        <h3><a href="{{ $post->url() }}">{{ $post->title }}</a></h3>
        @if ($post->excerptText())<p>{{ $post->excerptText() }}</p>@endif
        <a href="{{ $post->url() }}" class="more">اقرأ المزيد ←</a>
    </div>
</article>
