@props(['name', 'label', 'checked' => false, 'help' => null])
@php $key = str_replace(['[', ']'], ['.', ''], $name); $on = (bool) old($key, $checked); @endphp
<div {{ $attributes->only('class') }}>
    <input type="hidden" name="{{ $name }}" value="0">
    <label class="inline-flex cursor-pointer items-center gap-3">
        <input type="checkbox" name="{{ $name }}" value="1" class="peer sr-only" @checked($on)>
        <span class="relative h-6 w-11 rounded-full bg-slate-300 transition after:absolute after:top-0.5 after:right-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:bg-brand peer-checked:after:-translate-x-5"></span>
        <span class="text-sm font-bold text-slate-700">{{ $label }}</span>
    </label>
    @if ($help)<p class="help">{{ $help }}</p>@endif
</div>
