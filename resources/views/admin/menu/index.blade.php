@extends('admin.layout')

@section('title', 'قائمة التنقل')
@section('subtitle', 'روابط القائمة العلوية للموقع. اسحب ⠿ للترتيب أو لنقل رابط داخل/خارج قائمة منسدلة.')

@section('content')
<div class="grid gap-5 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <span data-sort-status class="mb-2 block text-sm font-bold text-emerald-600 opacity-0 transition"></span>
        @if ($items->isEmpty())
            <x-admin.empty text="القائمة فارغة. أضف أول رابط." />
        @endif
        <ul data-menu-tree data-url="{{ route('admin.menu.reorder') }}" class="space-y-2">
            @foreach ($items as $item)
                <li data-id="{{ $item->id }}" class="card">
                    <details class="group">
                        <summary class="flex cursor-pointer list-none items-center gap-3 px-4 py-3">
                            <span class="drag-handle text-lg">⠿</span>
                            <span class="flex-1 font-bold text-navy">{{ $item->label }} @if ($item->children->isNotEmpty())<span class="text-xs text-slate-400">▾ قائمة منسدلة</span>@endif</span>
                            <span class="text-xs text-slate-400" dir="ltr">{{ $item->url }}</span>
                            <span class="btn btn-ghost btn-sm">تعديل</span>
                        </summary>
                        <div class="border-t border-slate-100 p-4">
                            @include('admin.menu.item-form', ['item' => $item])
                            <form method="POST" action="{{ route('admin.menu.destroy', $item) }}" data-confirm="حذف «{{ $item->label }}»{{ $item->children->isNotEmpty() ? ' وكل روابطه الفرعية' : '' }}؟" class="mt-2">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">حذف</button></form>
                        </div>
                    </details>
                    <ul data-menu-children class="space-y-2 px-4 pb-3 empty:pb-1 {{ $item->children->isEmpty() ? 'min-h-2' : '' }}" style="padding-inline-start:2.5rem">
                        @foreach ($item->children as $child)
                            <li data-id="{{ $child->id }}" class="rounded-xl bg-slate-50 ring-1 ring-slate-200">
                                <details>
                                    <summary class="flex cursor-pointer list-none items-center gap-3 px-3 py-2">
                                        <span class="drag-handle">⠿</span>
                                        <span class="h-2.5 w-2.5 rounded-full" style="background:{{ hex_color($child->color, '#cbd5e1') }}"></span>
                                        <span class="flex-1 text-sm font-semibold">{{ $child->label }}</span>
                                        <span class="text-xs text-slate-400" dir="ltr">{{ $child->url }}</span>
                                    </summary>
                                    <div class="border-t border-slate-200 p-3">
                                        @include('admin.menu.item-form', ['item' => $child])
                                        <form method="POST" action="{{ route('admin.menu.destroy', $child) }}" data-confirm="حذف «{{ $child->label }}»؟" class="mt-2">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">حذف</button></form>
                                    </div>
                                </details>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>
    </div>
    <div>
        <div class="card card-body lg:sticky lg:top-20">
            <h2 class="mb-4 font-display text-lg font-extrabold text-navy">إضافة رابط</h2>
            @include('admin.menu.item-form', ['item' => null, 'compact' => true])
        </div>
    </div>
</div>
@endsection
