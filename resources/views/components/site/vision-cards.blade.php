@props(['cards'])
<div class="grid-3">
    @foreach ($cards as $i => $v)
        <div class="vcard reveal {{ delay_class($i) }}">
            <div class="vicon">{{ $v['icon'] ?? '' }}</div>
            <h3>{{ $v['title'] ?? '' }}</h3>
            <p>{{ $v['text'] ?? '' }}</p>
            @if (! empty($v['tag']))<span class="tag">{{ $v['tag'] }}</span>@endif
        </div>
    @endforeach
</div>
