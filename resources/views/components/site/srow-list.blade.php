@props(['text'])
@php $lines = text_lines($text); @endphp
@if ($lines)
<ul class="srow-list">
    @foreach ($lines as $line)<li><span class="ck">✓</span><span>{{ safe_html($line) }}</span></li>@endforeach
</ul>
@endif
