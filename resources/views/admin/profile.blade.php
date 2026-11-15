@extends('admin.layout')

@section('title', 'حسابي')

@section('content')
<form method="POST" action="{{ route('admin.profile.update') }}" class="max-w-2xl">
    @csrf @method('PUT')
    <div class="card card-body grid gap-4 sm:grid-cols-2">
        <x-admin.input name="name" label="الاسم" :value="$user->name" required />
        <x-admin.input name="email" type="email" label="البريد الإلكتروني" :value="$user->email" required dir="ltr" />
    </div>
    <div class="card card-body mt-5 grid gap-4 sm:grid-cols-2">
        <h2 class="font-display text-lg font-extrabold text-navy sm:col-span-2">تغيير كلمة المرور</h2>
        <x-admin.input class="sm:col-span-2" name="current_password" type="password" label="كلمة المرور الحالية" autocomplete="current-password" dir="ltr" />
        <x-admin.input name="password" type="password" label="كلمة المرور الجديدة" autocomplete="new-password" dir="ltr" />
        <x-admin.input name="password_confirmation" type="password" label="تأكيد كلمة المرور" autocomplete="new-password" dir="ltr" />
    </div>
    <x-admin.form-actions />
</form>
@endsection
