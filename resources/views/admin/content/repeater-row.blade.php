@php
    $summary = collect($field['fields'])->first(fn ($f) => in_array($f['type'], ['text', 'textarea'], true));
    $summaryText = $summary ? \Illuminate\Support\Str::limit(strip_tags((string) ($row[$summary['name']] ?? '')), 60) : '';
@endphp
<div data-row class="rounded-xl bg-white ring-1 ring-slate-200">
    <div class="flex items-center gap-2 border-b border-slate-100 px-3 py-2">
        <span class="drag-handle text-lg" title="اسحب للترتيب">⠿</span>
        <span class="badge bg-brand-soft text-brand" data-row-num></span>
        <button type="button" data-row-toggle class="min-w-0 flex-1 truncate text-start text-sm font-semibold text-slate-600">{{ $summaryText ?: 'عنصر جديد' }} <span class="text-slate-300">▾</span></button>
        <button type="button" data-row-up class="btn btn-ghost btn-sm" title="لأعلى">↑</button>
        <button type="button" data-row-down class="btn btn-ghost btn-sm" title="لأسفل">↓</button>
        <button type="button" data-row-remove class="btn btn-danger btn-sm" title="حذف">✕</button>
    </div>
    <div data-row-body class="grid gap-4 p-3 sm:grid-cols-2 {{ ($collapsed ?? false) ? 'hidden' : '' }}">
        @foreach ($field['fields'] as $sub)
            @include('admin.content.field', [
                'field' => $sub,
                'value' => $row[$sub['name']] ?? '',
                'name' => $name.'[#]['.$sub['name'].']',
                'file' => $file.'[#]['.$sub['name'].']',
                'templated' => true,
            ])
        @endforeach
    </div>
</div>
