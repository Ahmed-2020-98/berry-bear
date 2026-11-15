@if ($paginator->hasPages())
    <nav class="mt-5 flex flex-wrap items-center justify-center gap-1.5 text-sm">
        @if ($paginator->onFirstPage())
            <span class="btn btn-ghost btn-sm opacity-40">→ السابق</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-ghost btn-sm">→ السابق</a>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))<span class="px-2 text-slate-400">{{ $element }}</span>@endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="btn btn-primary btn-sm">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="btn btn-ghost btn-sm">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-ghost btn-sm">التالي ←</a>
        @else
            <span class="btn btn-ghost btn-sm opacity-40">التالي ←</span>
        @endif
    </nav>
@endif
