@php $item = $item ?? null; @endphp
<form method="POST" action="{{ $item ? route('admin.menu.update', $item) : route('admin.menu.store') }}" class="grid gap-3 {{ ($compact ?? false) ? '' : 'sm:grid-cols-2' }}">
    @csrf @if ($item) @method('PUT') @endif
    <div><label class="label">النص</label><input name="label" value="{{ $item?->label }}" class="input" required></div>
    <div><label class="label">الرابط</label><input name="url" value="{{ $item?->url }}" class="input" dir="ltr" placeholder="/about أو https://… (فارغ = قائمة منسدلة)"></div>
    <div>
        <label class="label">داخل القائمة المنسدلة لـ</label>
        <select name="parent_id" class="input">
            <option value="">— رابط رئيسي —</option>
            @foreach ($parents as $id => $label)
                @continue($item && $item->id == $id)
                <option value="{{ $id }}" @selected($item?->parent_id == $id)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <x-admin.color name="color" label="لون النقطة (للروابط الفرعية)" :value="$item?->color" />
    <label class="flex items-center gap-2 text-sm font-semibold text-slate-600"><input type="checkbox" name="open_new_tab" value="1" class="h-4 w-4 accent-brand" @checked($item?->open_new_tab)> فتح في نافذة جديدة</label>
    <div class="flex justify-end gap-2 {{ ($compact ?? false) ? '' : 'sm:col-span-2' }}"><button class="btn btn-primary btn-sm">{{ $item ? 'حفظ' : '＋ إضافة' }}</button></div>
</form>
