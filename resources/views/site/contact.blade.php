@extends('layouts.site')

@section('title', 'تواصل معنا')

@php $c = fn ($k) => content('contact', $k); @endphp

@section('content')
<x-site.page-hero :title="$c('ct_hero_title')" :sub="$c('ct_hero_sub')" crumb="تواصل معنا" />

<section class="section">
    <div class="container contact-grid">
        <div class="contact-info reveal">
            <h3>{{ $c('ct_info_title') }}</h3>
            <p>{{ $c('ct_info_sub') }}</p>
            <div class="contact-item"><div class="ci">📍</div><div><b>العنوان</b><span>{{ $contact['address'] }}</span></div></div>
            <div class="contact-item"><div class="ci">✉️</div><div><b>البريد الإلكتروني</b><span>{{ $contact['email'] }}</span></div></div>
            <div class="contact-item"><div class="ci">📞</div><div><b>الهاتف</b><span>{{ $contact['phone'] }}</span></div></div>
            <div class="contact-item"><div class="ci">🕒</div><div><b>ساعات العمل</b><span>{{ $contact['hours_long'] }}</span></div></div>
            <div class="contact-social">
                @foreach (\App\Support\Social::links() as $s){{ \App\Support\Social::link($s) }}@endforeach
            </div>
        </div>

        <div class="contact-form reveal d1" id="form">
            <h3 style="margin-bottom:6px">{{ $c('ct_form_title') }}</h3>
            <p style="color:var(--ink-soft); margin-bottom:22px;">{{ $c('ct_form_sub') }}</p>

            <form id="berry-contact" method="POST" action="{{ route('contact.send') }}#form" novalidate>
                @csrf
                <div style="position:absolute;left:-9999px" aria-hidden="true">
                    <label for="website">Website</label><input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>
                <div class="grid-2" style="gap:0 18px">
                    <div class="field">
                        <label for="name">الاسم الكامل</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="اكتب اسمك" required>
                        @error('name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="phone">رقم الجوال</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="05xxxxxxxx" required>
                        @error('phone')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="field">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="subject">نوع الطلب</label>
                    <select id="subject" name="subject">
                        @foreach (\App\Models\ContactMessage::SUBJECTS as $val => $label)
                            <option value="{{ $val }}" @selected(old('subject') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="message">رسالتك</label>
                    <textarea id="message" name="message" placeholder="اكتب رسالتك هنا…" required>{{ old('message') }}</textarea>
                    @error('message')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width:100%">إرسال الرسالة</button>
                <div class="form-success{{ session('contact_sent') ? ' show' : '' }}">✅ تم استلام رسالتك بنجاح! سنتواصل معك قريبًا.</div>
                @if ($errors->has('throttle'))<div class="form-error show">{{ $errors->first('throttle') }}</div>@endif
                <p class="form-note">بالضغط على «إرسال» فإنك توافق على أن نتواصل معك بخصوص طلبك.</p>
            </form>
        </div>
    </div>
</section>
@endsection

@push('head')
<style>
    .contact-form .field-error{display:block;margin-top:6px;color:#C0184A;font-size:.85rem;font-weight:600}
    .contact-form .form-error{text-align:center;padding:14px;border-radius:var(--r-sm);background:#FDE2EC;color:#C0184A;font-weight:700;margin-top:16px}
</style>
@endpush
