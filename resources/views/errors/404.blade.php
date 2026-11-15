@extends('layouts.site')

@section('title', 'الصفحة غير موجودة')

@section('content')
<x-site.page-hero title='الصفحة <span class="hl">غير موجودة</span>' sub="يبدو أن الحلوى التي تبحث عنها ذابت! لنعُد إلى الصفحة الرئيسية." crumb="٤٠٤" />
<section class="section">
    <div class="container center">
        <div style="font-size:5rem; margin-bottom:10px">🐻🍬</div>
        <p style="color:var(--ink-soft); font-weight:600; margin-bottom:26px">لم نتمكّن من العثور على هذه الصفحة.</p>
        <a href="{{ url('/') }}" class="btn btn-primary btn-lg">العودة للرئيسية</a>
    </div>
</section>
@endsection
