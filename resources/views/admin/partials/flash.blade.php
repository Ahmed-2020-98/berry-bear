<div class="pointer-events-none fixed bottom-4 left-4 z-50 flex flex-col gap-2">
    @foreach (['success' => 'bg-emerald-600', 'error' => 'bg-red-600'] as $type => $cls)
        @if (session($type))
            <div data-flash class="pointer-events-auto flex items-center gap-3 rounded-xl {{ $cls }} px-4 py-3 text-sm font-bold text-white shadow-lg transition duration-500">
                <span>{{ session($type) }}</span>
                <button type="button" data-dismiss class="opacity-70 hover:opacity-100">✕</button>
            </div>
        @endif
    @endforeach
    @if ($errors->any() && ! session('error'))
        <div data-flash class="pointer-events-auto flex items-center gap-3 rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-lg transition duration-500">
            <span>يرجى مراجعة الحقول المظللة بالأحمر.</span>
            <button type="button" data-dismiss class="opacity-70 hover:opacity-100">✕</button>
        </div>
    @endif
</div>
