@props(['t', 'delay' => ''])
@php $stars = max(1, min(5, (int) ($t->rating ?: 5))); @endphp
<div class="rcard reveal {{ $delay }}">
    <div class="quote-mark">”</div>
    <div class="stars">{{ str_repeat('★', $stars) }}</div>
    <p>{{ $t->text }}</p>
    <div class="who">
        <div class="av" style="background:{{ hex_color($t->avatar_color) }}">{{ mb_substr($t->name, 0, 1) }}</div>
        <div><b>{{ $t->name }}</b><span>{{ $t->role ?: 'عميل' }}</span></div>
    </div>
</div>
