@extends('admin.layout')

@section('title', $collection->exists ? 'تعديل التشكيلة' : 'تشكيلة جديدة')
@section('actions')<a href="{{ route('admin.collections.index') }}" class="btn btn-ghost">→ كل التشكيلات</a>@endsection

@section('content')
<form method="POST" action="{{ $collection->exists ? route('admin.collections.update', $collection) : route('admin.collections.store') }}" class="max-w-2xl">
    @csrf @if ($collection->exists) @method('PUT') @endif
    <div class="card card-body space-y-4">
        <x-admin.input name="name" label="الاسم" :value="$collection->name" required data-slug-source />
        <x-admin.input name="slug" label="الرابط (slug)" :value="$collection->slug" help="يُولَّد تلقائيًا إذا تُرك فارغًا." data-slug-target />
        <x-admin.textarea name="description" label="الوصف (يظهر في صفحة التشكيلة)" :value="$collection->description" />
    </div>
    <x-admin.form-actions :cancel="route('admin.collections.index')" />
</form>
@endsection
