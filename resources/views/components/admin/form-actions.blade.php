@props(['cancel' => null, 'label' => 'حفظ'])
<div class="sticky bottom-0 z-10 -mx-4 mt-6 flex items-center gap-2 border-t border-slate-200 bg-white/90 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6">
    <button class="btn btn-primary">💾 {{ $label }}</button>
    @if ($cancel)<a href="{{ $cancel }}" class="btn btn-ghost">إلغاء</a>@endif
    {{ $slot }}
</div>
