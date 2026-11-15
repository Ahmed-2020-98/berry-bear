@props(['title', 'sub' => '', 'crumb' => ''])
<section class="page-hero">
    <svg class="floaty-bear pb1"><use href="#gummy"></use></svg>
    <svg class="floaty-bear pb2"><use href="#gummy"></use></svg>
    <div class="container">
        @if ($crumb)
            <div class="breadcrumb"><a href="{{ url('/') }}">الرئيسية</a> <span>/</span> {{ $crumb }}</div>
        @endif
        <h1>{{ safe_html($title) }}</h1>
        @if ($sub)<p>{{ $sub }}</p>@endif
    </div>
</section>
