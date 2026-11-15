@extends('admin.layout')

@section('title', $t->exists ? 'تعديل الرأي' : 'رأي جديد')
@section('actions')<a href="{{ route('admin.testimonials.index') }}" class="btn btn-ghost">→ كل الآراء</a>@endsection

@section('content')
<form method="POST" action="{{ $t->exists ? route('admin.testimonials.update', $t) : route('admin.testimonials.store') }}" class="max-w-2xl">
    @csrf @if ($t->exists) @method('PUT') @endif
    <div class="card card-body grid gap-4 sm:grid-cols-2">
        <x-admin.input name="name" label="اسم العميل" :value="$t->name" required />
        <x-admin.input name="role" label="الصفة" :value="$t->role" help="مثال: عميل، شريك توزيع" />
        <x-admin.textarea class="sm:col-span-2" name="text" label="نص الرأي" :value="$t->text" rows="4" required />
        <x-admin.select name="rating" label="التقييم" :value="$t->rating" :options="[5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★']" />
        <x-admin.color name="avatar_color" label="لون الأفاتار" :value="$t->avatar_color" />
        <x-admin.toggle class="sm:col-span-2" name="is_published" label="منشور في الموقع" :checked="$t->is_published" />
    </div>
    <x-admin.form-actions :cancel="route('admin.testimonials.index')" />
</form>
@endsection
