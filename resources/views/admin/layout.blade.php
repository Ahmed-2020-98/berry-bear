@php
    $nav = [
        ['group' => 'عام'],
        ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'الرئيسية', 'icon' => '🏠'],
        ['route' => 'admin.messages.index', 'match' => 'admin.messages.*', 'label' => 'الرسائل', 'icon' => '✉️', 'badge' => $unreadMessages],
        ['group' => 'الكتالوج'],
        ['route' => 'admin.products.index', 'match' => 'admin.products.*', 'label' => 'المنتجات', 'icon' => '🍬'],
        ['route' => 'admin.collections.index', 'match' => 'admin.collections.*', 'label' => 'التشكيلات', 'icon' => '🗂️'],
        ['group' => 'المحتوى'],
        ['route' => 'admin.media.index', 'match' => 'admin.media.*', 'label' => 'مكتبة الوسائط', 'icon' => '🖼️'],
        ['route' => 'admin.posts.index', 'match' => 'admin.posts.*', 'label' => 'المدونة', 'icon' => '📝'],
        ['route' => 'admin.faqs.index', 'match' => 'admin.faqs.*', 'label' => 'الأسئلة الشائعة', 'icon' => '❓'],
        ['route' => 'admin.testimonials.index', 'match' => 'admin.testimonials.*', 'label' => 'آراء العملاء', 'icon' => '💬'],
        ['route' => 'admin.pages.index', 'match' => 'admin.pages.*', 'label' => 'صفحات إضافية', 'icon' => '📄'],
        ['group' => 'محتوى الصفحات'],
    ];
    foreach (\App\Support\ContentSchema::pages() as $key => $title) {
        $nav[] = ['route' => 'admin.content.edit', 'params' => $key, 'match' => null, 'active' => request()->routeIs('admin.content.*') && request()->route('key') === $key, 'label' => $title, 'icon' => '🧩'];
    }
    if (auth()->user()->isAdmin()) {
        $nav[] = ['group' => 'الإدارة'];
        $nav[] = ['route' => 'admin.settings', 'match' => 'admin.settings*', 'label' => 'إعدادات الموقع', 'icon' => '⚙️'];
        $nav[] = ['route' => 'admin.menu.index', 'match' => 'admin.menu.*', 'label' => 'قائمة التنقل', 'icon' => '🧭'];
        $nav[] = ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'label' => 'المستخدمون', 'icon' => '👥'];
    }
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'لوحة التحكم') — لوحة تحكم بيري بير</title>
    <link rel="icon" href="{{ theme_img('logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Baloo+Bhaijaan+2:wght@600;700;800&family=Tajawal:wght@400;500;700;800&display=swap">
    <script>
        window.BERRY_UPLOAD_URL = @json(route('admin.uploads'));
        window.BERRY_MEDIA = { url: @json(route('admin.media.index')), max: {{ \App\Http\Controllers\Admin\MediaController::MAX_FILES }}, maxKb: {{ \App\Support\Media::MAX_KB }} };
    </script>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen">
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-navy/40 lg:hidden"></div>

    <aside id="sidebar" class="fixed inset-y-0 right-0 z-40 flex w-64 translate-x-full flex-col bg-navy transition-transform lg:translate-x-0">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-5 py-5">
            <img src="{{ theme_img('logo.png') }}" alt="" class="h-10 w-10 rounded-xl bg-white object-contain p-1">
            <div>
                <div class="font-display text-lg font-extrabold leading-none text-white">Berry Bear</div>
                <div class="mt-1 text-xs text-white/50">لوحة التحكم</div>
            </div>
        </a>
        <nav class="flex-1 overflow-y-auto px-3 pb-6">
            @foreach ($nav as $item)
                @if (isset($item['group']))
                    <div class="nav-group">{{ $item['group'] }}</div>
                @else
                    @php $active = $item['active'] ?? request()->routeIs($item['match']); @endphp
                    <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="nav-link {{ $active ? 'active' : '' }}">
                        <span class="text-base">{{ $item['icon'] }}</span>
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if (! empty($item['badge']))<span class="badge bg-brand text-white">{{ $item['badge'] }}</span>@endif
                    </a>
                @endif
            @endforeach
        </nav>
        <div class="border-t border-white/10 p-3">
            <a href="{{ url('/') }}" target="_blank" class="nav-link">🌐 <span>عرض الموقع</span></a>
        </div>
    </aside>

    <div class="lg:pr-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200/70 bg-white/85 px-4 backdrop-blur sm:px-6">
            <button type="button" data-sidebar-toggle class="btn btn-ghost btn-sm lg:hidden" aria-label="القائمة">☰</button>
            <h1 class="flex-1 truncate font-display text-xl font-extrabold text-navy">@yield('title', 'لوحة التحكم')</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.profile') }}" class="hidden text-sm font-semibold text-slate-600 hover:text-brand sm:block">
                    {{ auth()->user()->name }} <span class="badge bg-slate-100 text-slate-500">{{ auth()->user()->roleLabel() }}</span>
                </a>
                <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-ghost btn-sm">خروج</button></form>
            </div>
        </header>

        <main class="mx-auto max-w-6xl p-4 sm:p-6">
            @hasSection('actions')
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-sm text-slate-500">@yield('subtitle')</div>
                    <div class="flex flex-wrap gap-2">@yield('actions')</div>
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    @include('admin.partials.flash')
    @unless (request()->routeIs('admin.media.*'))@include('admin.media.modal')@endunless
</body>
</html>
