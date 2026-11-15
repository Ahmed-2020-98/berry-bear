@extends('admin.layout')

@section('title', $page->exists ? 'تعديل الصفحة' : 'صفحة جديدة')
@section('actions')
    @if ($page->exists && $page->is_published)<a href="{{ url($page->slug) }}" target="_blank" class="btn btn-ghost">👁️ عرض في الموقع</a>@endif
    <a href="{{ route('admin.pages.index') }}" class="btn btn-ghost">→ كل الصفحات</a>
@endsection

@section('content')
<form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="max-w-4xl" data-dirty-check>
    @csrf @if ($page->exists) @method('PUT') @endif
    <div class="card card-body space-y-4">
        <x-admin.input name="title" label="العنوان" :value="$page->title" required data-slug-source />
        <x-admin.input name="slug" label="الرابط (slug)" :value="$page->slug" data-slug-target />
        <x-admin.editor name="body" label="المحتوى" :value="$page->body" />
        <x-admin.toggle name="is_published" label="منشورة" :checked="$page->is_published" />
    </div>
    <x-admin.form-actions :cancel="route('admin.pages.index')" />
</form>
@endsection
