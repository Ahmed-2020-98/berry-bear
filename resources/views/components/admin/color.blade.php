@props(['name', 'label' => null, 'value' => null, 'templated' => false])
@php $key = str_replace(['[', ']'], ['.', ''], $name); $val = (string) old($key, $value); $attr = $templated ? 'data-name' : 'name'; @endphp
<div data-color {{ $attributes->only('class') }}>
    @if ($label)<span class="label">{{ $label }}</span>@endif
    <div class="flex items-center gap-2">
        <input type="color" value="{{ preg_match('/^#[0-9a-f]{6}$/i', $val) ? $val : '#E51B6E' }}" class="h-10 w-12 cursor-pointer rounded-lg border-0 bg-transparent p-0">
        <input type="text" {{ $attr }}="{{ $name }}" value="{{ $val }}" placeholder="#E51B6E" dir="ltr" class="input font-mono {{ $errors->has($key) ? 'input-error' : '' }}">
    </div>
    @error($key)<p class="error">{{ $message }}</p>@enderror
</div>
