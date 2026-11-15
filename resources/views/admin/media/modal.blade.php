{{-- Shared media picker modal (one per page). Opened by any [data-media-pick] button or by the editor. --}}
<div id="media-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="media-modal-title">
    <div class="absolute inset-0 bg-navy/50 backdrop-blur-sm" data-modal-close></div>
    <div class="absolute inset-2 flex flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:inset-6 lg:inset-10">
        <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3 sm:px-5">
            <h2 id="media-modal-title" class="flex-1 font-display text-lg font-extrabold text-navy">اختيار صورة</h2>
            <button type="button" class="btn btn-ghost btn-sm" data-modal-close aria-label="إغلاق">✕</button>
        </div>
        <div data-media-library data-mode="select" class="flex min-h-0 flex-1 flex-col"></div>
    </div>
</div>
