@extends('admin.layout')

@section('title', $faq->exists ? 'تعديل السؤال' : 'سؤال جديد')
@section('actions')<a href="{{ route('admin.faqs.index') }}" class="btn btn-ghost">→ كل الأسئلة</a>@endsection

@section('content')
<form method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="max-w-3xl" data-dirty-check>
    @csrf @if ($faq->exists) @method('PUT') @endif
    <div class="card card-body space-y-4">
        <x-admin.input name="question" label="السؤال" :value="$faq->question" required />
        <x-admin.editor name="answer" label="الإجابة" :value="$faq->answer" />
        <x-admin.toggle name="is_published" label="منشور في الموقع" :checked="$faq->is_published" />
    </div>
    <x-admin.form-actions :cancel="route('admin.faqs.index')" />
</form>
@endsection
