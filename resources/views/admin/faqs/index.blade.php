@extends('admin.layout')

@section('title', 'الأسئلة الشائعة')
@section('subtitle', 'اسحب ⠿ لترتيب الأسئلة كما تظهر في الموقع.')
@section('actions')<a href="{{ route('admin.faqs.create') }}" class="btn btn-primary">＋ سؤال جديد</a>@endsection

@section('content')
<span data-sort-status class="mb-2 block text-sm font-bold text-emerald-600 opacity-0 transition"></span>
@if ($faqs->isEmpty())
    <x-admin.empty text="لا توجد أسئلة." :action="route('admin.faqs.create')" action-label="إضافة سؤال" />
@else
<div class="card overflow-x-auto">
    <table class="table">
        <thead><tr><th class="w-8"></th><th>السؤال</th><th>الحالة</th><th class="w-32"></th></tr></thead>
        <tbody data-sort-url="{{ route('admin.sort', 'faqs') }}">
            @foreach ($faqs as $f)
                <tr data-id="{{ $f->id }}" class="bg-white">
                    <td><span class="drag-handle text-lg">⠿</span></td>
                    <td><a href="{{ route('admin.faqs.edit', $f) }}" class="font-bold text-navy hover:text-brand">{{ $f->question }}</a>
                        <div class="mt-0.5 line-clamp-1 text-xs text-slate-400">{{ strip_tags($f->answer) }}</div></td>
                    <td>@if ($f->is_published)<span class="badge bg-emerald-50 text-emerald-700">منشور</span>@else<span class="badge bg-slate-100 text-slate-500">مخفي</span>@endif</td>
                    <td><div class="flex justify-end gap-1">
                        <a href="{{ route('admin.faqs.edit', $f) }}" class="btn btn-ghost btn-sm">تعديل</a>
                        <form method="POST" action="{{ route('admin.faqs.destroy', $f) }}" data-confirm="حذف هذا السؤال؟">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">حذف</button></form>
                    </div></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
