@props(['eyebrow', 'title', 'sub' => '', 'reasons'])
@php $reasons = flatten_text($reasons); @endphp
@if ($reasons)
<section class="section"><div class="container">
    <x-site.sec-head :eyebrow="$eyebrow" :title="$title" :sub="$sub" />
    <ul class="trust-reasons reveal">
        @foreach ($reasons as $r)<li><span class="ck">✓</span><span>{{ $r }}</span></li>@endforeach
    </ul>
</div></section>
@endif
