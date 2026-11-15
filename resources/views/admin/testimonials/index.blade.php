@extends('admin.layout')

@section('title', 'آراء العملاء')
@section('subtitle', 'تعرض الصفحة الرئيسية أول ٣ آراء منشورة. اسحب ⠿ للترتيب.')
@section('actions')<a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary">＋ رأي جديد</a>@endsection

@section('content')
<span data-sort-status class="mb-2 block text-sm font-bold text-emerald-600 opacity-0 transition"></span>
@if ($testimonials->isEmpty())
    <x-admin.empty text="لا توجد آراء." :action="route('admin.testimonials.create')" action-label="إضافة رأي" />
@else
<div class="card overflow-x-auto">
    <table class="table">
        <thead><tr><th class="w-8"></th><th>العميل</th><th>التقييم</th><th>الحالة</th><th class="w-32"></th></tr></thead>
        <tbody data-sort-url="{{ route('admin.sort', 'testimonials') }}">
            @foreach ($testimonials as $t)
                <tr data-id="{{ $t->id }}" class="bg-white">
                    <td><span class="drag-handle text-lg">⠿</span></td>
                    <td><div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-full font-bold text-white" style="background:{{ hex_color($t->avatar_color) }}">{{ mb_substr($t->name, 0, 1) }}</span>
                        <div><a href="{{ route('admin.testimonials.edit', $t) }}" class="font-bold text-navy hover:text-brand">{{ $t->name }}</a>
                            <div class="line-clamp-1 text-xs text-slate-400">{{ $t->text }}</div></div>
                    </div></td>
                    <td class="text-amber-500">{{ str_repeat('★', $t->rating) }}</td>
                    <td>@if ($t->is_published)<span class="badge bg-emerald-50 text-emerald-700">منشور</span>@else<span class="badge bg-slate-100 text-slate-500">مخفي</span>@endif</td>
                    <td><div class="flex justify-end gap-1">
                        <a href="{{ route('admin.testimonials.edit', $t) }}" class="btn btn-ghost btn-sm">تعديل</a>
                        <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" data-confirm="حذف رأي «{{ $t->name }}»؟">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">حذف</button></form>
                    </div></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
