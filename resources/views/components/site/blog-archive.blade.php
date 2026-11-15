@props(['posts', 'title', 'sub' => '', 'crumb' => 'المدونة'])
<x-site.page-hero :title="$title" :sub="$sub" :crumb="$crumb" />
<section class="section"><div class="container">
    @if ($posts->count())
        <div class="grid-3">
            @foreach ($posts as $i => $post)
                <x-site.blog-card :post="$post" :delay="delay_class($i)" />
            @endforeach
        </div>
        @if ($posts->hasPages())
            <div class="berry-pagination">
                @if ($posts->onFirstPage())@else<a class="prev page-numbers" href="{{ $posts->previousPageUrl() }}">→</a>@endif
                @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $n => $u)
                    @if ($n == $posts->currentPage())<span aria-current="page" class="page-numbers current">{{ $n }}</span>@else<a class="page-numbers" href="{{ $u }}">{{ $n }}</a>@endif
                @endforeach
                @if ($posts->hasMorePages())<a class="next page-numbers" href="{{ $posts->nextPageUrl() }}">←</a>@endif
            </div>
        @endif
    @else
        <p class="berry-empty">لا توجد مقالات بعد.</p>
    @endif
</div></section>
