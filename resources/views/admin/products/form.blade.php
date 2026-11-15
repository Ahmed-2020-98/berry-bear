@extends('admin.layout')

@section('title', $product->exists ? 'تعديل: '.$product->title : 'منتج جديد')
@section('actions')
    @if ($product->exists && $product->is_published)<a href="{{ $product->url() }}" target="_blank" class="btn btn-ghost">👁️ عرض في الموقع</a>@endif
    <a href="{{ route('admin.products.index') }}" class="btn btn-ghost">→ كل المنتجات</a>
@endsection

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" data-dirty-check>
    @csrf @if ($product->exists) @method('PUT') @endif
    <div class="grid gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            <div class="card card-body space-y-4">
                <x-admin.input name="title" label="اسم المنتج" :value="$product->title" required data-slug-source />
                <x-admin.input name="slug" label="الرابط (slug)" :value="$product->slug" help="يُولَّد تلقائيًا من الاسم إذا تُرك فارغًا." dir="auto" data-slug-target />
                <x-admin.textarea name="excerpt" label="وصف قصير (يظهر على بطاقة المنتج)" :value="$product->excerpt" rows="2" />
                <x-admin.editor name="body" label="تفاصيل المنتج" :value="$product->body" />
            </div>
        </div>
        <div class="space-y-5">
            <div class="card card-body space-y-4">
                <x-admin.toggle name="is_published" label="منشور في الموقع" :checked="$product->is_published" />
                <x-admin.select name="collection_id" label="التشكيلة" :value="$product->collection_id" :options="$collections" placeholder="— بدون —" />
            </div>
            <div class="card card-body space-y-4">
                <x-admin.media name="image" file="image_file" label="صورة المنتج" :value="$product->image" />
                <x-admin.media name="carton_image" file="carton_image_file" label="صورة الكرتونة (اختياري)" :value="$product->carton_image" help="تظهر على ظهر البطاقة وفي صفحة المنتج." />
            </div>
            <div class="card card-body space-y-4">
                <x-admin.input name="flavor_label" label="وسم النكهة" :value="$product->flavor_label" />
                <x-admin.color name="flavor_color" label="لون الوسم" :value="$product->flavor_color" />
                <x-admin.input name="anchor_id" label="مُعرّف الرابط (anchor)" :value="$product->anchor_id" dir="ltr" help="مثال: bears — للروابط مثل /products#bears" />
            </div>
        </div>
    </div>
    <x-admin.form-actions :cancel="route('admin.products.index')" />
</form>
@endsection
