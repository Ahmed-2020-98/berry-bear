@extends('layouts.site')

@section('title', $pageTitle ?? 'المدونة')

@section('content')
<x-site.blog-archive :posts="$posts" :title="$heroTitle" :sub="$heroSub" :crumb="$crumb" />
@endsection
