@props(['badges'])
@php $badges = flatten_text($badges); @endphp
@if ($badges)
<section class="trust-strip"><div class="container">
    @foreach ($badges as $b)<span class="trust-badge"><span class="tb-ic">✓</span>{{ $b }}</span>@endforeach
</div></section>
@endif
