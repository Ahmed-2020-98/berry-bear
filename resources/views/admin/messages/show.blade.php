@extends('admin.layout')

@section('title', 'رسالة من '.$message->name)
@section('actions')
    <a href="{{ route('admin.messages.index') }}" class="btn btn-ghost">→ كل الرسائل</a>
@endsection

@section('content')
<div class="grid gap-5 lg:grid-cols-3">
    <div class="card card-body lg:col-span-2">
        <div class="mb-3 flex items-center gap-2">
            <span class="badge bg-brand-soft text-brand">{{ $message->subjectLabel() }}</span>
            <span class="text-xs text-slate-400">{{ $message->created_at->format('Y-m-d H:i') }} · {{ $message->created_at->diffForHumans() }}</span>
        </div>
        <div class="whitespace-pre-line leading-8 text-slate-700">{{ $message->message }}</div>
    </div>
    <div class="space-y-5">
        <div class="card card-body space-y-3 text-sm">
            <div><div class="text-xs text-slate-400">الاسم</div><div class="font-bold">{{ $message->name }}</div></div>
            <div><div class="text-xs text-slate-400">البريد</div><a href="mailto:{{ $message->email }}" class="font-bold text-brand" dir="ltr">{{ $message->email }}</a></div>
            <div><div class="text-xs text-slate-400">الجوال</div><a href="tel:{{ $message->phone }}" class="font-bold text-brand" dir="ltr">{{ $message->phone }}</a></div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('رد: '.$message->subjectLabel()) }}" class="btn btn-primary">✉️ رد بالبريد</a>
            @php $wa = preg_replace('/\D/', '', strtr((string) $message->phone, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9'])); if (str_starts_with($wa, '05')) $wa = '966'.substr($wa, 1); @endphp
            @if ($wa)<a href="https://wa.me/{{ $wa }}" target="_blank" class="btn btn-ghost">واتساب</a>@endif
            <form method="POST" action="{{ route('admin.messages.toggle', $message) }}">@csrf @method('PATCH')<button class="btn btn-ghost">تعليم كغير مقروءة</button></form>
            <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" data-confirm="حذف هذه الرسالة نهائيًا؟">@csrf @method('DELETE')<button class="btn btn-danger">حذف</button></form>
        </div>
    </div>
</div>
@endsection
