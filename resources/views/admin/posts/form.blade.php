@extends('admin.layout')

@section('title', $post->exists ? 'تعديل المقال' : 'مقال جديد')
@section('actions')
    @if ($post->exists && $post->status === 'published')<a href="{{ $post->url() }}" target="_blank" class="btn btn-ghost">👁️ عرض في الموقع</a>@endif
    <a href="{{ route('admin.posts.index') }}" class="btn btn-ghost">→ كل المقالات</a>
@endsection

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}" data-dirty-check>
    @csrf @if ($post->exists) @method('PUT') @endif
    <div class="grid gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            <div class="card card-body space-y-4">
                <x-admin.input name="title" label="العنوان" :value="$post->title" required data-slug-source />
                <x-admin.input name="slug" label="الرابط (slug)" :value="$post->slug" help="رابط المقال: /الرابط — يُولَّد تلقائيًا من العنوان." data-slug-target />
                <x-admin.textarea name="excerpt" label="مقتطف (يظهر على بطاقة المقال)" :value="$post->excerpt" rows="2" />
                <x-admin.editor name="body" label="المحتوى" :value="$post->body" />
            </div>
        </div>
        <div class="space-y-5">
            <div class="card card-body space-y-4">
                <x-admin.select name="status" label="الحالة" :value="$post->status" :options="['published' => 'منشور', 'draft' => 'مسودة']" />
                <x-admin.input name="published_at" type="datetime-local" label="تاريخ النشر" :value="$post->published_at?->format('Y-m-d\TH:i')" dir="ltr" />
                <div>
                    <x-admin.input name="category" label="التصنيف" :value="$post->category" list="categories" />
                    <datalist id="categories">@foreach ($categories as $c)<option value="{{ $c }}">@endforeach</datalist>
                </div>
                <x-admin.input name="reading_time" type="number" label="مدة القراءة (دقائق)" :value="$post->reading_time" help="اتركه فارغًا ليُحسب تلقائيًا." min="1" />
            </div>
            <div class="card card-body">
                <x-admin.media name="cover" file="cover_file" label="صورة الغلاف" :value="$post->cover" />
            </div>
        </div>
    </div>
    <x-admin.form-actions :cancel="route('admin.posts.index')" />
</form>
@endsection
