@extends('admin.layout')

@section('title', 'صفحات إضافية')
@section('subtitle', 'صفحات نصية مثل سياسة الخصوصية والشروط. الصفحات الرئيسية تُعدّل من «محتوى الصفحات».')
@section('actions')<a href="{{ route('admin.pages.create') }}" class="btn btn-primary">＋ صفحة جديدة</a>@endsection

@section('content')
@if ($pages->isEmpty())
    <x-admin.empty text="لا توجد صفحات إضافية." :action="route('admin.pages.create')" action-label="إضافة صفحة" />
@else
<div class="card overflow-x-auto">
    <table class="table">
        <thead><tr><th>العنوان</th><th>الرابط</th><th>الحالة</th><th class="w-32"></th></tr></thead>
        <tbody>
            @foreach ($pages as $p)
                <tr>
                    <td><a href="{{ route('admin.pages.edit', $p) }}" class="font-bold text-navy hover:text-brand">{{ $p->title }}</a></td>
                    <td><a href="{{ url($p->slug) }}" target="_blank" class="text-xs text-slate-400 hover:text-brand" dir="ltr">/{{ $p->slug }}</a></td>
                    <td>@if ($p->is_published)<span class="badge bg-emerald-50 text-emerald-700">منشورة</span>@else<span class="badge bg-slate-100 text-slate-500">مخفية</span>@endif</td>
                    <td><div class="flex justify-end gap-1">
                        <a href="{{ route('admin.pages.edit', $p) }}" class="btn btn-ghost btn-sm">تعديل</a>
                        <form method="POST" action="{{ route('admin.pages.destroy', $p) }}" data-confirm="حذف الصفحة «{{ $p->title }}»؟">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">حذف</button></form>
                    </div></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
