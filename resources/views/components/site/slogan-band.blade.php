@props(['slogans'])
@php $slogans = flatten_text($slogans); $main = array_shift($slogans); @endphp
@if ($main)
<section class="slogan-band"><div class="container reveal">
    <div class="slogan-main">{{ $main }}</div>
    @if ($slogans)<div class="slogan-list">@foreach ($slogans as $s)<span>{{ $s }}</span>@endforeach</div>@endif
</div></section>
@endif
