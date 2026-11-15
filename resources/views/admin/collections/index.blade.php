@extends('admin.layout')

@section('title', 'التشكيلات')
@section('subtitle', 'مجموعات المنتجات. اسحب ⠿ للترتيب.')
@section('actions')<a href="{{ route('admin.collections.create') }}" class="btn btn-primary">＋ تشكيلة جديدة</a>@endsection

@section('content')
<span data-sort-status class="mb-2 block text-sm font-bold text-emerald-600 opacity-0 transition"></span>
@if ($collections->isEmpty())
    <x-admin.empty text="لا توجد تشكيلات." :action="route('admin.collections.create')" action-label="إضافة تشكيلة" />
@else
<div class="card overflow-x-auto">
    <table class="table">
        <thead><tr><th class="w-8"></th><th>الاسم</th><th>الرابط</th><th>المنتجات</th><th class="w-32"></th></tr></thead>
        <tbody data-sort-url="{{ route('admin.sort', 'collections') }}">
            @foreach ($collections as $c)
                <tr data-id="{{ $c->id }}" class="bg-white">
                    <td><span class="drag-handle text-lg">⠿</span></td>
                    <td><a href="{{ route('admin.collections.edit', $c) }}" class="font-bold text-navy hover:text-brand">{{ $c->name }}</a></td>
                    <td><a href="{{ route('collections.show', $c->slug) }}" target="_blank" class="text-xs text-slate-400 hover:text-brand" dir="ltr">/collection/{{ $c->slug }}</a></td>
                    <td><span class="badge bg-slate-100 text-slate-600">{{ $c->products_count }}</span></td>
                    <td><div class="flex justify-end gap-1">
                        <a href="{{ route('admin.collections.edit', $c) }}" class="btn btn-ghost btn-sm">تعديل</a>
                        <form method="POST" action="{{ route('admin.collections.destroy', $c) }}" data-confirm="حذف التشكيلة «{{ $c->name }}»؟ لن تُحذف منتجاتها.">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">حذف</button></form>
                    </div></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
