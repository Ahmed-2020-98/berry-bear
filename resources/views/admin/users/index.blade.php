@extends('admin.layout')

@section('title', 'المستخدمون')
@section('subtitle', 'المدير يدير كل شيء. المحرر يدير المحتوى والرسائل فقط.')
@section('actions')<a href="{{ route('admin.users.create') }}" class="btn btn-primary">＋ مستخدم جديد</a>@endsection

@section('content')
<div class="card overflow-x-auto">
    <table class="table">
        <thead><tr><th>الاسم</th><th>البريد</th><th>الصلاحية</th><th>الحالة</th><th class="w-32"></th></tr></thead>
        <tbody>
            @foreach ($users as $u)
                <tr>
                    <td class="font-bold text-navy">{{ $u->name }} @if ($u->is(auth()->user()))<span class="badge bg-slate-100 text-slate-500">أنت</span>@endif</td>
                    <td class="text-slate-500" dir="ltr">{{ $u->email }}</td>
                    <td><span class="badge {{ $u->isAdmin() ? 'bg-grape/10 text-grape' : 'bg-sky-50 text-sky-700' }}">{{ $u->roleLabel() }}</span></td>
                    <td>@if ($u->is_active)<span class="badge bg-emerald-50 text-emerald-700">نشط</span>@else<span class="badge bg-slate-100 text-slate-500">موقوف</span>@endif</td>
                    <td><div class="flex justify-end gap-1">
                        <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-ghost btn-sm">تعديل</a>
                        @unless ($u->is(auth()->user()))
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" data-confirm="حذف المستخدم «{{ $u->name }}»؟">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">حذف</button></form>
                        @endunless
                    </div></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
