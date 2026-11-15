@props(['name', 'label' => null, 'value' => null, 'help' => null])
@php $key = str_replace(['[', ']'], ['.', ''], $name); @endphp
<div data-editor {{ $attributes->only('class') }}>
    @if ($label)<span class="label">{{ $label }}</span>@endif
    <input type="hidden" name="{{ $name }}" value="{{ old($key, $value) }}">
    <div data-editor-host>{!! clean((string) old($key, $value)) !!}</div>
    @if ($help)<p class="help">{{ $help }}</p>@endif
    @error($key)<p class="error">{{ $message }}</p>@enderror
</div>
