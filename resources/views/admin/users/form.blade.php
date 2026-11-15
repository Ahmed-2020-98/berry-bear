@extends('admin.layout')

@section('title', $user->exists ? 'تعديل المستخدم' : 'مستخدم جديد')
@section('actions')<a href="{{ route('admin.users.index') }}" class="btn btn-ghost">→ كل المستخدمين</a>@endsection

@section('content')
@php $self = $user->exists && $user->is(auth()->user()); @endphp
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="max-w-2xl">
    @csrf @if ($user->exists) @method('PUT') @endif
    <div class="card card-body grid gap-4 sm:grid-cols-2">
        <x-admin.input name="name" label="الاسم" :value="$user->name" required />
        <x-admin.input name="email" type="email" label="البريد الإلكتروني" :value="$user->email" required dir="ltr" />
        <x-admin.select name="role" label="الصلاحية" :value="$user->role" :options="\App\Models\User::ROLES" :disabled="$self" />
        <div class="self-end">
            @if ($self)<p class="help">لا يمكنك تغيير صلاحيتك أو إيقاف حسابك.</p><input type="hidden" name="role" value="{{ $user->role }}"><input type="hidden" name="is_active" value="1">
            @else<x-admin.toggle name="is_active" label="الحساب نشط" :checked="$user->is_active" />@endif
        </div>
        <x-admin.input name="password" type="password" :label="$user->exists ? 'كلمة مرور جديدة (اختياري)' : 'كلمة المرور'" :required="! $user->exists" autocomplete="new-password" dir="ltr" help="8 أحرف على الأقل." />
        <x-admin.input name="password_confirmation" type="password" label="تأكيد كلمة المرور" autocomplete="new-password" dir="ltr" />
    </div>
    <x-admin.form-actions :cancel="route('admin.users.index')" />
</form>
@endsection
