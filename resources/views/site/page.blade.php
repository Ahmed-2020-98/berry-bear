@extends('layouts.site')

@section('title', $page->title)

@section('content')
<x-site.page-hero :title="e($page->title)" :crumb="$page->title" />
<section class="post-single">
    <div class="container">
        <div class="post-body">{{ safe_html($page->body) }}</div>
    </div>
</section>
@endsection
