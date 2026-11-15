{{--
    Schema-driven field.
    $field  normalised field spec      $value current value
    $name   input name, e.g. data[hero_title] or data[steps][#][title]
    $file   file input name (image/file types)
    $templated  true inside repeater rows (names set by JS from data-name)
--}}
@php
    $templated = $templated ?? false;
    $attr = $templated ? 'data-name' : 'name';
    $type = $field['type'];
    $errKey = str_replace(['[', ']', '#'], ['.', '', '0'], $name);
    $hasErr = ! $templated && $errors->has($errKey);
    $cls = 'input'.($hasErr ? ' input-error' : '');
    $isWide = in_array($type, ['textarea', 'html', 'repeater'], true);
@endphp
<div class="{{ $isWide ? 'sm:col-span-2' : '' }}">
    @switch($type)
        @case('image')
        @case('file')
            <x-admin.media :name="$name" :file="$file" :label="$field['label']" :value="$value" :kind="$type" :accept="$field['accept'] ?? null" :help="$field['help']" :templated="$templated" />
            @break

        @case('color')
            <x-admin.color :name="$name" :label="$field['label']" :value="$value" :templated="$templated" />
            @break

        @case('toggle')
            <input type="hidden" {{ $attr }}="{{ $name }}" value="0">
            <label class="mt-6 inline-flex cursor-pointer items-center gap-3">
                <input type="checkbox" {{ $attr }}="{{ $name }}" value="1" class="peer sr-only" @checked((string) $value === '1' || $value === true)>
                <span class="relative h-6 w-11 rounded-full bg-slate-300 transition after:absolute after:top-0.5 after:right-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition peer-checked:bg-brand peer-checked:after:-translate-x-5"></span>
                <span class="text-sm font-bold text-slate-700">{{ $field['label'] }}</span>
            </label>
            @break

        @case('select')
            <label class="label">{{ $field['label'] }}</label>
            <select {{ $attr }}="{{ $name }}" class="{{ $cls }}">
                @foreach ($field['options'] as $val => $text)
                    <option value="{{ $val }}" @selected((string) $value === (string) $val)>{{ $text }}</option>
                @endforeach
            </select>
            @break

        @case('textarea')
        @case('html')
            <label class="label">{{ $field['label'] }} @if ($type === 'html')<span class="badge bg-grape/10 text-grape">يدعم HTML</span>@endif</label>
            <textarea {{ $attr }}="{{ $name }}" rows="{{ $type === 'html' ? 2 : 3 }}" class="{{ $cls }}">{{ $value }}</textarea>
            @if ($type === 'html')<p class="help">لتلوين كلمة: <code dir="ltr">&lt;span class="hl"&gt;كلمة&lt;/span&gt;</code> — الألوان المتاحة: hl, pink, purple, green</p>@endif
            @break

        @case('repeater')
            @php $rows = is_array($value) ? array_values($value) : []; @endphp
            <div data-repeater class="rounded-2xl bg-slate-50 p-3 ring-1 ring-slate-200 sm:p-4">
                <div class="mb-3 flex items-center justify-between">
                    <span class="label mb-0">{{ $field['label'] }}</span>
                    <span class="text-xs text-slate-400">اضغط على العنصر لفتحه · اسحب ⠿ للترتيب</span>
                </div>
                <div data-rows class="space-y-3">
                    @foreach ($rows as $i => $row)
                        @include('admin.content.repeater-row', ['field' => $field, 'row' => (array) $row, 'name' => $name, 'file' => $file, 'collapsed' => count($rows) > 3])
                    @endforeach
                </div>
                <template>
                    @include('admin.content.repeater-row', ['field' => $field, 'row' => [], 'name' => $name, 'file' => $file])
                </template>
                <button type="button" data-add class="btn btn-ghost btn-sm mt-3">＋ {{ $field['button'] }}</button>
            </div>
            @break

        @default
            <label class="label">{{ $field['label'] }}</label>
            <input type="{{ $type === 'number' ? 'number' : 'text' }}" {{ $attr }}="{{ $name }}" value="{{ $value }}" class="{{ $cls }}" @if($type === 'url') dir="ltr" placeholder="/contact أو https://…" @endif>
    @endswitch

    @if ($field['help'] && ! in_array($type, ['image', 'file'], true))<p class="help">{{ $field['help'] }}</p>@endif
    @if ($hasErr && ! in_array($type, ['image', 'file', 'color'], true))<p class="error">{{ $errors->first($errKey) }}</p>@endif
</div>
