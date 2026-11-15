@extends('admin.layout')

@section('title', 'مكتبة الوسائط')
@section('subtitle')<span data-media-count>{{ ar_digits($total) }}</span> صورة — ارفع الصورة مرة واحدة واستخدمها في أي مكان بالموقع.@endsection
@section('actions')<button type="button" class="btn btn-primary" data-library-upload>＋ رفع صور</button>@endsection

@section('content')
<div data-media-library data-mode="manage" class="card overflow-hidden"></div>
@endsection
