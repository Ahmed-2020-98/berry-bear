{{--
    Media field.
      kind=image → picks from the central media library (modal); stores the file path in $name.
      kind=file  → classic upload input ($file) for video / PDF.
--}}
@props(['name', 'file', 'label' => null, 'value' => null, 'kind' => 'image', 'accept' => null, 'help' => null, 'templated' => false])
@php
    $attr = $templated ? 'data-name' : 'name';
    $url = media_url($value);
    $fileKey = str_replace(['[', ']'], ['.', ''], $file);
    $keyErr = str_replace(['[', ']'], ['.', ''], $name);
    $hasErr = $errors->has($fileKey) || $errors->has($keyErr);
    $accept = $accept ? collect(explode(',', $accept))->map(fn ($e) => '.'.trim($e))->implode(',') : '';
@endphp
<div data-media data-label="{{ $label }}" {{ $attributes->only('class') }}>
    @if ($label)<span class="label">{{ $label }}</span>@endif
    <div class="flex items-center gap-3 rounded-xl p-2 ring-1 {{ $hasErr ? 'ring-red-400' : 'ring-slate-200' }} bg-slate-50/60">
        <input type="hidden" {{ $attr }}="{{ $name }}" value="{{ $value }}" data-keep>
        @if ($kind === 'image')
            <button type="button" data-media-pick class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-lg bg-white ring-1 ring-slate-200 transition hover:ring-2 hover:ring-brand/60" title="اختيار من مكتبة الوسائط">
                <img data-preview src="{{ $url }}" alt="" class="h-full w-full object-cover {{ $url ? '' : 'hidden' }}">
                <span data-empty class="text-2xl text-slate-300 {{ $url ? 'hidden' : '' }}">🖼️</span>
            </button>
            <div class="min-w-0 flex-1 space-y-1.5">
                <button type="button" data-media-pick class="btn btn-ghost btn-sm">🖼️ {{ $value ? 'تغيير الصورة' : 'اختيار من المكتبة' }}</button>
                <button type="button" data-remove class="btn btn-sm text-red-600 hover:bg-red-50 {{ $value ? '' : 'hidden' }}">إزالة</button>
                <div data-filename class="truncate text-xs text-slate-500" dir="ltr">{{ $value ? basename((string) $value) : '' }}</div>
            </div>
        @else
            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-lg bg-white text-xl ring-1 ring-slate-200">🎞️</div>
            <div class="min-w-0 flex-1 space-y-1.5">
                <label class="btn btn-ghost btn-sm">
                    اختيار ملف
                    <input type="file" {{ $attr }}="{{ $file }}" accept="{{ $accept }}" class="sr-only">
                </label>
                <button type="button" data-remove class="btn btn-sm text-red-600 hover:bg-red-50">إزالة</button>
                <div data-filename class="truncate text-xs text-slate-500" dir="ltr">@if($value){{ basename((string) $value) }}@endif</div>
            </div>
        @endif
    </div>
    @if ($help)<p class="help">{{ $help }}</p>@endif
    @if ($errors->has($fileKey))<p class="error">{{ $errors->first($fileKey) }}</p>@elseif ($errors->has($keyErr))<p class="error">{{ $errors->first($keyErr) }}</p>@endif
</div>
