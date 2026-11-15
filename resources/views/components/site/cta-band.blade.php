@props(['title', 'text', 'label', 'url'])
<div class="cta-band reveal">
    <h2>{{ $title }}</h2>
    <p>{{ $text }}</p>
    <a href="{{ site_url($url, '/contact') }}" class="btn btn-yellow btn-lg">{{ $label }}</a>
</div>
