@extends('admin.layout')

@section('title', 'المنتجات')
@section('subtitle', 'اسحب ⠿ لترتيب ظهور المنتجات في الموقع.')
@section('actions')
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">＋ منتج جديد</a>
@endsection

@section('content')
<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="q" value="{{ request('q') }}" placeholder="بحث بالاسم…" class="input max-w-xs">
    <select name="collection" class="input max-w-[200px]" onchange="this.form.submit()">
        <option value="">كل التشكيلات</option>
        @foreach ($collections as $id => $name)<option value="{{ $id }}" @selected(request('collection') == $id)>{{ $name }}</option>@endforeach
    </select>
    <button class="btn btn-ghost">بحث</button>
    <span data-sort-status class="self-center text-sm font-bold text-emerald-600 opacity-0 transition"></span>
</form>

@if ($products->isEmpty())
    <x-admin.empty text="لا توجد منتجات." :action="route('admin.products.create')" action-label="إضافة منتج" />
@else
<div class="card overflow-x-auto">
    <table class="table">
        <thead><tr><th class="w-8"></th><th>المنتج</th><th>التشكيلة</th><th>النكهة</th><th>الحالة</th><th class="w-32"></th></tr></thead>
        <tbody data-sort-url="{{ route('admin.sort', 'products') }}">
            @foreach ($products as $p)
                <tr data-id="{{ $p->id }}" class="bg-white">
                    <td><span class="drag-handle text-lg">⠿</span></td>
                    <td>
                        <div class="flex items-center gap-3">
                            <img src="{{ $p->imageUrl() }}" alt="" class="h-12 w-12 rounded-lg object-cover ring-1 ring-slate-200">
                            <div>
                                <a href="{{ route('admin.products.edit', $p) }}" class="font-bold text-navy hover:text-brand">{{ $p->title }}</a>
                                <div class="text-xs text-slate-400" dir="ltr">/product/{{ $p->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-slate-500">{{ $p->collection?->name ?? '—' }}</td>
                    <td>@if ($p->flavor_label)<span class="badge text-white" style="background:{{ hex_color($p->flavor_color) }}">{{ $p->flavor_label }}</span>@endif</td>
                    <td>@if ($p->is_published)<span class="badge bg-emerald-50 text-emerald-700">منشور</span>@else<span class="badge bg-slate-100 text-slate-500">مخفي</span>@endif</td>
                    <td>
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-ghost btn-sm">تعديل</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $p) }}" data-confirm="حذف المنتج «{{ $p->title }}»؟">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">حذف</button></form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
