@php
    $plType = setting('preloader_media_type', 'logo');
    $plVideo = media_url(setting('preloader_video', ''));
    $plImage = media_url(setting('preloader_image', ''));
    $plBar = setting('preloader_show_bar');
    $plBar = (bool) $plBar;
    $plText = setting('preloader_text', 'يُحضّر المرح…');
    $siteName = setting('site_name', 'Berry Bear');
    $pageTitle = trim($__env->yieldContent('title'));
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ? $pageTitle.' – '.$siteName : $siteName }}</title>
    @hasSection('description')<meta name="description" content="@yield('description')">@endif
    <link rel="icon" href="{{ theme_img('logo.png') }}" type="image/png">
    <meta name="theme-color" content="#1C1552">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Baloo+Bhaijaan+2:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('theme/css/style.css') }}?v={{ @filemtime(public_path('theme/css/style.css')) }}">
    <link rel="stylesheet" href="{{ asset('theme/css/wp-extras.css') }}?v={{ @filemtime(public_path('theme/css/wp-extras.css')) }}">
    @stack('head')
</head>
<body class="@yield('body_class')">

    <div id="preloader"@if($plType === 'video' && $plVideo) class="preloader--video"@endif>
        <div class="pre-inner">
            @if ($plType === 'video' && $plVideo)
                <video class="pre-media" autoplay muted playsinline preload="auto"><source src="{{ $plVideo }}"></video>
            @elseif ($plType === 'image' && $plImage)
                <img src="{{ $plImage }}" alt="Berry Bear" class="pre-media">
            @else
                <img src="{{ theme_img('logo.png') }}" alt="Berry Bear" class="pre-logo">
            @endif
            @if ($plBar)<div class="pre-bar"><span></span></div>@endif
            @if ($plText)<div class="pre-text">{{ $plText }}</div>@endif
        </div>
    </div>

    <header class="site-header">
        <div class="container nav">
            <a href="{{ url('/') }}" class="brand" aria-label="Berry Bear الصفحة الرئيسية">
                <img src="{{ media_url(setting('logo', ''), 'logo.png') }}" alt="Berry Bear — Sour Gummy">
            </a>

            <ul class="nav-menu">
                @foreach ($menu as $item)
                    @if ($item->children->isNotEmpty())
                        <li class="has-drop">
                            <button type="button">{{ $item->label }} <span class="caret">&#9660;</span></button>
                            <div class="dropdown">
                                @foreach ($item->children as $ci => $child)
                                    <a href="{{ $child->href() }}"@if($child->open_new_tab) target="_blank" rel="noopener"@endif><span class="dot" style="background:{{ hex_color($child->color, ['#E51B6E', '#9B63B5', '#C0184A', '#1E8A44', '#FF8A3D', '#4C6EF5'][$ci % 6]) }}"></span> {{ $child->label }}</a>
                                @endforeach
                            </div>
                        </li>
                    @else
                        <li><a href="{{ $item->href() }}"@if(rtrim($item->href(), '/') === rtrim(url()->current(), '/')) class="active"@endif @if($item->open_new_tab) target="_blank" rel="noopener"@endif>{{ $item->label }}</a></li>
                    @endif
                @endforeach
            </ul>

            <div class="nav-cta">
                <a href="{{ site_url(setting('wholesale_url'), '/contact') }}" class="btn btn-primary">{{ setting('wholesale_label', 'اطلب بالجملة') }}</a>
                <button class="nav-toggle" aria-label="القائمة"><span></span></button>
            </div>
        </div>
    </header>

    @yield('content')

    @php
        $quick = setting('footer_quick_links', []) ?: [
            ['label' => 'الرئيسية', 'url' => '/'], ['label' => 'من نحن', 'url' => '/about'],
            ['label' => 'منتجاتنا', 'url' => '/products'], ['label' => 'المدونة', 'url' => '/blog'],
        ];
        $support = setting('footer_support_links', []) ?: [
            ['label' => 'الأسئلة الشائعة', 'url' => '/faqs'], ['label' => 'تواصل معنا', 'url' => '/contact'],
            ['label' => 'الطلب بالجملة', 'url' => '/contact'], ['label' => 'سياسة الخصوصية', 'url' => '#'],
        ];
        $thanks = setting('footer_thanks', 'شكرًا لثقتكم بـ Berry Bear — نصنع السعادة قطعة بعد قطعة 💜');
    @endphp
    <footer class="site-footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-brand">
                    <img src="{{ media_url(setting('logo', ''), 'logo.png') }}" alt="Berry Bear — Sour Gummy">
                    <p>{{ setting('footer_about', 'بيري بير — علامة حلوى الجيلي المرحة. دباديب وحلوى حامضة بنكهات الفواكه الحقيقية، متعة نظيفة يثق بها الجميع.') }}</p>
                    <div class="footer-social">
                        @foreach (\App\Support\Social::links() as $s){{ \App\Support\Social::link($s) }}@endforeach
                    </div>
                </div>
                <div class="footer-col">
                    <h4>{{ setting('footer_quick_title', 'روابط سريعة') }}</h4>
                    <ul>@foreach ($quick as $l)<li><a href="{{ site_url($l['url'] ?? '#') }}">◂ {{ $l['label'] ?? '' }}</a></li>@endforeach</ul>
                </div>
                <div class="footer-col">
                    <h4>{{ setting('footer_support_title', 'الدعم') }}</h4>
                    <ul>@foreach ($support as $l)<li><a href="{{ site_url($l['url'] ?? '#') }}">◂ {{ $l['label'] ?? '' }}</a></li>@endforeach</ul>
                </div>
                <div class="footer-col">
                    <h4>{{ setting('footer_contact_title', 'تواصل معنا') }}</h4>
                    <div class="fcontact">📍 <span>{{ $contact['address'] }}</span></div>
                    <div class="fcontact">✉️ <span>{{ $contact['email'] }}</span></div>
                    <div class="fcontact">📞 <span>{{ $contact['phone'] }}</span></div>
                    <div class="fcontact">🕒 <span>{{ $contact['hours'] }}</span></div>
                </div>
            </div>
            @if ($thanks)<div class="footer-thanks">{{ $thanks }}</div>@endif
            <div class="footer-bottom">
                © <span id="year">{{ date('Y') }}</span> {{ setting('footer_copyright', 'Berry Bear — جميع الحقوق محفوظة · صُنع بحُبّ 💜') }}
            </div>
        </div>
    </footer>

    <button class="to-top" aria-label="العودة للأعلى">↑</button>

    <script src="{{ asset('theme/js/main.js') }}?v={{ @filemtime(public_path('theme/js/main.js')) }}"></script>
    @stack('scripts')
</body>
</html>
