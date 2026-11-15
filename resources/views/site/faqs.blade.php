@extends('layouts.site')

@section('title', 'الأسئلة الشائعة')

@section('content')
<x-site.page-hero :title="content('faqs', 'faq_hero_title')" :sub="content('faqs', 'faq_hero_sub')" crumb="الأسئلة الشائعة" />

<section class="section">
    <div class="container">
        <div class="faq-list">
            @foreach ($faqs as $faq)
                <div class="faq-item reveal">
                    <button class="faq-q"><span>{{ $faq->question }}</span><span class="ic">+</span></button>
                    <div class="faq-a"><div class="faq-a-inner">{{ safe_html(str_contains($faq->answer, '<') ? $faq->answer : '<p>'.nl2br(e($faq->answer)).'</p>') }}</div></div>
                </div>
            @endforeach
        </div>

        <div class="center" style="margin-top:50px">
            <p style="color:var(--ink-soft); margin-bottom:18px; font-weight:600;">{{ content('faqs', 'faq_cta_text') }}</p>
            <a href="{{ site_url(content('faqs', 'faq_cta_url'), '/contact') }}" class="btn btn-primary btn-lg">{{ content('faqs', 'faq_cta_label') }}</a>
        </div>
    </div>
</section>
@endsection
