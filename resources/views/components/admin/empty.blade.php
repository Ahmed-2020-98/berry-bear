@props(['text' => 'لا توجد عناصر بعد.', 'action' => null, 'actionLabel' => null])
<div class="card card-body py-14 text-center">
    <div class="text-4xl">🐻</div>
    <p class="mt-3 font-semibold text-slate-500">{{ $text }}</p>
    @if ($action)<a href="{{ $action }}" class="btn btn-primary mt-4">{{ $actionLabel }}</a>@endif
</div>
