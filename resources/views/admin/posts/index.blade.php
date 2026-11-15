@extends('admin.layout')

@section('title', 'المدونة')
@section('actions')<a href="{{ route('admin.posts.create') }}" class="btn btn-primary">＋ مقال جديد</a>@endsection

@section('content')
<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="q" value="{{ request('q') }}" placeholder="بحث بالعنوان…" class="input max-w-xs">
    <select name="status" class="input max-w-[160px]" onchange="this.form.submit()">
        <option value="">كل الحالات</option>
        <option value="published" @selected(request('status') === 'published')>منشور</option>
        <option value="draft" @selected(request('status') === 'draft')>مسودة</option>
    </select>
    <button class="btn btn-ghost">بحث</button>
</form>
@if ($posts->isEmpty())
    <x-admin.empty text="لا توجد مقالات." :action="route('admin.posts.create')" action-label="كتابة مقال" />
@else
<div class="card overflow-x-auto">
    <table class="table">
        <thead><tr><th>المقال</th><th>التصنيف</th><th>تاريخ النشر</th><th>الحالة</th><th class="w-32"></th></tr></thead>
        <tbody>
            @foreach ($posts as $p)
                <tr>
                    <td><div class="flex items-center gap-3">
                        <img src="{{ $p->coverUrl() }}" alt="" class="h-12 w-16 rounded-lg object-cover ring-1 ring-slate-200">
                        <a href="{{ route('admin.posts.edit', $p) }}" class="font-bold text-navy hover:text-brand">{{ $p->title }}</a>
                    </div></td>
                    <td class="text-slate-500">{{ $p->category ?: '—' }}</td>
                    <td class="text-slate-500">{{ $p->published_at?->format('Y-m-d') }}</td>
                    <td>@if ($p->status === 'published')<span class="badge bg-emerald-50 text-emerald-700">منشور</span>@else<span class="badge bg-amber-50 text-amber-700">مسودة</span>@endif</td>
                    <td><div class="flex justify-end gap-1">
                        <a href="{{ route('admin.posts.edit', $p) }}" class="btn btn-ghost btn-sm">تعديل</a>
                        <form method="POST" action="{{ route('admin.posts.destroy', $p) }}" data-confirm="حذف المقال «{{ $p->title }}»؟">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">حذف</button></form>
                    </div></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $posts->links() }}
@endif
@endsection
