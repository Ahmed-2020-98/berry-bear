@props(['name', 'label' => null, 'value' => null, 'rows' => 3, 'help' => null, 'required' => false])
@php $key = str_replace(['[', ']'], ['.', ''], $name); $id = 'f_'.str_replace('.', '_', $key); @endphp
<div {{ $attributes->only('class') }}>
    @if ($label)<label class="label" for="{{ $id }}">{{ $label }}@if ($required) <span class="text-brand">*</span>@endif</label>@endif
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->except('class')->merge(['class' => 'input'.($errors->has($key) ? ' input-error' : '')]) }} @required($required)>{{ old($key, $value) }}</textarea>
    @if ($help)<p class="help">{{ $help }}</p>@endif
    @error($key)<p class="error">{{ $message }}</p>@enderror
</div>
