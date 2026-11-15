@extends('admin.layout')

@section('title', 'الرسائل')
@section('subtitle', 'رسائل نموذج «تواصل معنا».')
@section('actions')
    <a href="{{ route('admin.messages.index') }}" class="btn {{ request('filter') !== 'unread' ? 'btn-navy' : 'btn-ghost' }} btn-sm">الكل</a>
    <a href="{{ route('admin.messages.index', ['filter' => 'unread']) }}" class="btn {{ request('filter') === 'unread' ? 'btn-navy' : 'btn-ghost' }} btn-sm">غير المقروءة</a>
@endsection

@section('content')
<form method="GET" class="mb-4 flex flex-wrap gap-2">
    @if (request('filter'))<input type="hidden" name="filter" value="{{ request('filter') }}">@endif
    <input name="q" value="{{ request('q') }}" placeholder="بحث بالاسم أو البريد أو الجوال…" class="input max-w-xs">
    <select name="subject" class="input max-w-[180px]" onchange="this.form.submit()">
        <option value="">كل الأنواع</option>
        @foreach (\App\Models\ContactMessage::SUBJECTS as $k => $v)<option value="{{ $k }}" @selected(request('subject') === $k)>{{ $v }}</option>@endforeach
    </select>
    <button class="btn btn-ghost">بحث</button>
</form>
@if ($messages->isEmpty())
    <x-admin.empty text="لا توجد رسائل." />
@else
<div class="card overflow-hidden">
    <ul class="divide-y divide-slate-100">
        @foreach ($messages as $m)
            <li>
                <a href="{{ route('admin.messages.show', $m) }}" class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 {{ $m->read_at ? '' : 'bg-brand-soft/40' }}">
                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $m->read_at ? 'bg-transparent' : 'bg-brand' }}"></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="{{ $m->read_at ? 'font-semibold' : 'font-extrabold' }} text-navy">{{ $m->name }}</span>
                            <span class="badge bg-slate-100 text-slate-600">{{ $m->subjectLabel() }}</span>
                            <span class="ms-auto text-xs text-slate-400">{{ $m->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-0.5 line-clamp-1 text-sm text-slate-500">{{ $m->message }}</p>
                    </div>
                </a>
            </li>
        @endforeach
    </ul>
</div>
{{ $messages->links() }}
@endif
@endsection
