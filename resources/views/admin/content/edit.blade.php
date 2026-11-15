@extends('admin.layout')

@section('title', $group['title'])
@section('subtitle', $key === 'settings' ? 'الإعدادات العامة للموقع: الشعار، شاشة التحميل، الفوتر، والتواصل.' : 'عدّل نصوص وصور أقسام هذه الصفحة. الحقول الفارغة تعرض النص الافتراضي.')
@section('actions')
    @if ($previewUrl)<a href="{{ $previewUrl }}" target="_blank" class="btn btn-ghost">👁️ معاينة الصفحة</a>@endif
@endsection

@section('content')
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" data-dirty-check>
    @csrf @method('PUT')

    @if (count($group['tabs']) > 1)
        <div data-tabs class="mb-4 flex gap-1 overflow-x-auto rounded-xl bg-slate-100 p-1">
            @foreach (array_keys($group['tabs']) as $i => $tab)
                <button type="button" class="tab" data-tab="t{{ $i }}">{{ $tab }}</button>
            @endforeach
        </div>
    @endif

    @foreach (array_values($group['tabs']) as $i => $fields)
        <div data-panel="t{{ $i }}" class="card card-body {{ $i > 0 ? 'hidden' : '' }}">
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ($fields as $field)
                    @include('admin.content.field', [
                        'field' => $field,
                        'value' => $values[$field['name']] ?? '',
                        'name' => 'data['.$field['name'].']',
                        'file' => 'files['.$field['name'].']',
                    ])
                @endforeach
            </div>
        </div>
    @endforeach

    <x-admin.form-actions label="حفظ التغييرات" />
</form>
@endsection
