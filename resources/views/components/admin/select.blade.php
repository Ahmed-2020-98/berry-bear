@props(['name', 'label' => null, 'value' => null, 'options' => [], 'help' => null, 'placeholder' => null])
@php $key = str_replace(['[', ']'], ['.', ''], $name); $id = 'f_'.str_replace('.', '_', $key); $current = (string) old($key, $value); @endphp
<div {{ $attributes->only('class') }}>
    @if ($label)<label class="label" for="{{ $id }}">{{ $label }}</label>@endif
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('class')->merge(['class' => 'input'.($errors->has($key) ? ' input-error' : '')]) }}>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $val => $text)
            <option value="{{ $val }}" @selected($current === (string) $val)>{{ $text }}</option>
        @endforeach
    </select>
    @if ($help)<p class="help">{{ $help }}</p>@endif
    @error($key)<p class="error">{{ $message }}</p>@enderror
</div>
