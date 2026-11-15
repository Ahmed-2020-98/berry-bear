@extends('admin.layout')

@section('title', 'الرئيسية')

@section('content')
<div class="mb-6 rounded-2xl bg-gradient-to-l from-brand to-grape p-6 text-white shadow-sm">
    <h2 class="font-display text-2xl font-extrabold">أهلًا {{ auth()->user()->name }} 👋</h2>
    <p class="mt-1 text-white/85">من هنا تدير كل محتوى موقع بيري بير.</p>
    <div class="mt-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.products.create') }}" class="btn bg-white/15 text-white hover:bg-white/25">＋ منتج</a>
        <a href="{{ route('admin.posts.create') }}" class="btn bg-white/15 text-white hover:bg-white/25">＋ مقال</a>
        <a href="{{ route('admin.content.edit', 'home') }}" class="btn bg-white/15 text-white hover:bg-white/25">🧩 الصفحة الرئيسية</a>
        <a href="{{ url('/') }}" target="_blank" class="btn bg-white text-brand hover:bg-white/90">🌐 عرض الموقع</a>
    </div>
</div>

<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    @foreach ($stats as $s)
        <a href="{{ route($s['route']) }}" class="card card-body transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="text-2xl">{{ $s['icon'] }}</div>
            <div class="mt-2 font-display text-3xl font-extrabold text-navy">{{ $s['value'] }}</div>
            <div class="text-sm font-semibold text-slate-500">{{ $s['label'] }}</div>
        </a>
    @endforeach
</div>

<div class="card mt-6">
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <h2 class="font-display text-lg font-extrabold text-navy">أحدث الرسائل @if ($unread)<span class="badge bg-brand text-white">{{ $unread }} جديدة</span>@endif</h2>
        <a href="{{ route('admin.messages.index') }}" class="text-sm font-bold text-brand">عرض الكل ←</a>
    </div>
    @forelse ($messages as $m)
        <a href="{{ route('admin.messages.show', $m) }}" class="flex items-center gap-3 border-b border-slate-50 px-5 py-3 last:border-0 hover:bg-slate-50">
            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $m->read_at ? 'bg-slate-200' : 'bg-brand' }}"></span>
            <span class="font-bold text-navy">{{ $m->name }}</span>
            <span class="badge bg-slate-100 text-slate-600">{{ $m->subjectLabel() }}</span>
            <span class="line-clamp-1 flex-1 text-sm text-slate-500">{{ $m->message }}</span>
            <span class="shrink-0 text-xs text-slate-400">{{ $m->created_at->diffForHumans() }}</span>
        </a>
    @empty
        <p class="px-5 py-8 text-center text-slate-400">لا توجد رسائل بعد.</p>
    @endforelse
</div>
@endsection
