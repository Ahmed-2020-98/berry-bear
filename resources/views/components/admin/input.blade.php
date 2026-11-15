@props(['name', 'label' => null, 'value' => null, 'type' => 'text', 'help' => null, 'required' => false, 'dir' => null])
@php $key = str_replace(['[', ']'], ['.', ''], $name); $id = 'f_'.str_replace('.', '_', $key); @endphp
<div {{ $attributes->only('class') }}>
    @if ($label)<label class="label" for="{{ $id }}">{{ $label }}@if ($required) <span class="text-brand">*</span>@endif</label>@endif
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($key, $value) }}" @if($dir) dir="{{ $dir }}" @endif
        {{ $attributes->except('class')->merge(['class' => 'input'.($errors->has($key) ? ' input-error' : '')]) }} @required($required)>
    @if ($help)<p class="help">{{ $help }}</p>@endif
    @error($key)<p class="error">{{ $message }}</p>@enderror
</div>
