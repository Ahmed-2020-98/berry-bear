/*
 * Media library — one component, two modes:
 *   manage  → /admin/media page (browse, upload, edit alt text, delete)
 *   select  → picker modal opened from any image field or the rich-text editor
 *
 * Everything user-supplied (file names, alt text) is written with textContent, never innerHTML.
 */

const cfg = () => window.BERRY_MEDIA || { url: '/admin/media', max: 20, maxKb: 6144 };
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;
const ALLOWED = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

function h(tag, attrs = {}, ...children) {
    const el = document.createElement(tag);
    for (const [k, v] of Object.entries(attrs)) {
        if (v === null || v === undefined || v === false) continue;
        if (k === 'class') el.className = v;
        else if (k === 'text') el.textContent = v;
        else if (k.startsWith('on')) el.addEventListener(k.slice(2), v);
        else el.setAttribute(k, v === true ? '' : v);
    }
    for (const c of children.flat()) if (c !== null && c !== undefined && c !== false) el.append(c);
    return el;
}

const arDigits = (n) => String(n).replace(/\d/g, (d) => '٠١٢٣٤٥٦٧٨٩'[d]);

async function api(url, { method = 'GET', body = null } = {}) {
    const headers = { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() };
    if (body && !(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
        body = JSON.stringify(body);
    }
    const res = await fetch(url, { method, headers, body, credentials: 'same-origin' });
    let data = {};
    try { data = await res.json(); } catch (e) {}
    if (!res.ok) {
        const msg = data.message || Object.values(data.errors || {})[0]?.[0] || 'حدث خطأ غير متوقع، حاول مرة أخرى.';
        throw Object.assign(new Error(msg), { data, status: res.status });
    }
    return data;
}

/** Upload one file with progress (XHR gives upload progress events, fetch doesn't). */
function uploadFile(file, onProgress) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        const body = new FormData();
        body.append('files[]', file);
        xhr.open('POST', cfg().url);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrf());
        xhr.upload.onprogress = (e) => e.lengthComputable && onProgress(Math.round((e.loaded / e.total) * 100));
        xhr.onerror = () => reject(new Error('انقطع الاتصال أثناء الرفع.'));
        xhr.onload = () => {
            let data = {};
            try { data = JSON.parse(xhr.responseText); } catch (e) {}
            if (xhr.status >= 200 && xhr.status < 300 && data.items?.length) return resolve(data.items[0]);
            if (xhr.status === 419) return reject(new Error('انتهت الجلسة، حدّث الصفحة وحاول مجدداً.'));
            if (xhr.status === 413) return reject(new Error('حجم الملف أكبر من المسموح على الخادم.'));
            reject(new Error(data.errors?.[0]?.message || data.message || 'تعذّر رفع الصورة.'));
        };
        xhr.send(body);
    });
}

export class MediaLibrary {
    constructor(root, { mode = 'manage', onSelect = null } = {}) {
        this.root = root;
        this.mode = mode;
        this.onSelect = onSelect;
        this.items = new Map(); // id → item
        this.selected = null;
        this.selectedPath = null; // path wanted as selected (picker opened on an existing value)
        this.query = '';
        this.nextPage = 1;
        this.loading = false;
        this.render();
        if (mode !== 'select') this.load(true);
    }

    /* ---------- layout ---------- */
    render() {
        const isSelect = this.mode === 'select';
        this.fileInput = h('input', { type: 'file', multiple: true, accept: ALLOWED.join(','), class: 'sr-only', onchange: () => { this.upload([...this.fileInput.files]); this.fileInput.value = ''; } });
        this.search = h('input', { type: 'search', class: 'input max-w-xs', placeholder: 'ابحث باسم الملف…', 'aria-label': 'بحث في الوسائط' });
        let t;
        this.search.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => { this.query = this.search.value.trim(); this.load(true); }, 250); });
        this.countEl = h('span', { class: 'text-xs text-slate-400' });

        this.queue = h('div', { class: 'space-y-1.5 empty:hidden' });
        this.grid = h('div', { class: 'grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5 xl:grid-cols-6', role: 'listbox', 'aria-label': 'الصور' });
        this.emptyEl = h('div', { class: 'hidden py-16 text-center text-slate-400' });
        this.more = h('button', { type: 'button', class: 'btn btn-ghost btn-sm mx-auto mt-4 hidden', text: 'تحميل المزيد', onclick: () => this.load() });
        this.sentinel = h('div', { class: 'h-1' });

        this.drop = h('div', { class: 'pointer-events-none absolute inset-2 z-10 hidden place-items-center rounded-2xl border-4 border-dashed border-brand bg-brand-soft/90 text-center' },
            h('div', {}, h('div', { class: 'text-4xl', text: '⬆️' }), h('div', { class: 'mt-2 font-display text-xl font-extrabold text-brand', text: 'أفلت الصور هنا لرفعها' })));

        this.details = h('aside', { class: 'border-t border-slate-200 bg-slate-50/70 p-4 lg:w-80 lg:shrink-0 lg:overflow-y-auto lg:border-t-0 lg:border-s' + (isSelect ? '' : ' lg:sticky lg:top-16 lg:max-h-[calc(100vh-4rem)] lg:self-start') });

        const toolbar = h('div', { class: 'flex flex-wrap items-center gap-2 border-b border-slate-200 px-4 py-3' },
            this.search, this.countEl, h('span', { class: 'flex-1' }),
            isSelect && h('button', { type: 'button', class: 'btn btn-ghost btn-sm', onclick: () => this.fileInput.click() }, '⬆️ رفع من الجهاز'),
            this.fileInput);

        const dropHint = h('button', { type: 'button', class: 'mb-3 flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-slate-300 px-4 py-4 text-sm font-bold text-slate-500 transition hover:border-brand hover:text-brand', onclick: () => this.fileInput.click() },
            '🖼️ اسحب الصور وأفلتها هنا أو اضغط للاختيار',
            h('span', { class: 'font-normal text-slate-400', text: `(JPG, PNG, WEBP, GIF — حتى ${Math.round(cfg().maxKb / 1024)} ميجابايت)` }));

        this.main = h('div', { class: 'relative min-h-0 flex-1 overflow-y-auto p-4' }, dropHint, this.queue, this.grid, this.emptyEl, this.more, this.sentinel, this.drop);

        const body = h('div', { class: 'flex min-h-0 flex-1 flex-col lg:flex-row' + (isSelect ? '' : ' lg:min-h-[32rem]') }, this.main, this.details);
        this.root.replaceChildren(toolbar, body);

        if (isSelect) {
            this.useBtn = h('button', { type: 'button', class: 'btn btn-primary', disabled: true, text: 'استخدام هذه الصورة', onclick: () => this.confirm() });
            this.footerInfo = h('div', { class: 'min-w-0 flex-1 truncate text-sm text-slate-500', text: 'اختر صورة من المكتبة أو ارفع صورة جديدة.' });
            this.root.append(h('div', { class: 'flex items-center gap-3 border-t border-slate-200 bg-white px-4 py-3' }, this.footerInfo,
                h('button', { type: 'button', class: 'btn btn-ghost', 'data-modal-close': true, text: 'إلغاء' }), this.useBtn));
        }

        this.renderDetails();
        this.bindDrop();
        new IntersectionObserver((e) => e[0].isIntersecting && this.nextPage && !this.loading && this.items.size && this.load(), { root: this.mode === 'select' ? this.main : null }).observe(this.sentinel);
        document.querySelectorAll('[data-library-upload]').forEach((b) => this.mode === 'manage' && b.addEventListener('click', () => this.fileInput.click()));
    }

    bindDrop() {
        let depth = 0;
        const hasFiles = (e) => [...(e.dataTransfer?.types || [])].includes('Files');
        this.root.addEventListener('dragenter', (e) => { if (!hasFiles(e)) return; e.preventDefault(); depth++; this.drop.classList.replace('hidden', 'grid'); });
        this.root.addEventListener('dragover', (e) => hasFiles(e) && e.preventDefault());
        this.root.addEventListener('dragleave', () => { if (--depth <= 0) { depth = 0; this.drop.classList.replace('grid', 'hidden'); } });
        this.root.addEventListener('drop', (e) => {
            if (!hasFiles(e)) return;
            e.preventDefault();
            depth = 0;
            this.drop.classList.replace('grid', 'hidden');
            this.upload([...e.dataTransfer.files]);
        });
    }

    /* ---------- data ---------- */
    async load(reset = false) {
        if (this.loading) return;
        if (reset) { this.nextPage = 1; this.items.clear(); this.grid.replaceChildren(); }
        if (!this.nextPage) return;
        this.loading = true;
        this.more.textContent = 'جارٍ التحميل…';
        try {
            const params = new URLSearchParams({ page: this.nextPage });
            if (this.query) params.set('q', this.query);
            const data = await api(`${cfg().url}?${params}`);
            data.data.forEach((item) => this.addItem(item));
            this.nextPage = data.next_page;
            this.total = data.total;
            this.updateCount();
            if (this.selectedPath && !this.selected) {
                const hit = [...this.items.values()].find((i) => i.path === this.selectedPath);
                if (hit) this.select(hit, { scroll: true });
            }
        } catch (e) {
            this.toast(e.message, true);
        } finally {
            this.loading = false;
            this.more.textContent = 'تحميل المزيد';
            this.more.classList.toggle('hidden', !this.nextPage);
        }
    }

    updateCount() {
        const n = this.total ?? this.items.size;
        this.countEl.textContent = this.query ? `${arDigits(n)} نتيجة` : `${arDigits(n)} صورة`;
        document.querySelectorAll('[data-media-count]').forEach((el) => !this.query && (el.textContent = arDigits(n)));
        const empty = this.items.size === 0;
        this.emptyEl.classList.toggle('hidden', !empty);
        this.emptyEl.textContent = this.query ? 'لا توجد صور تطابق البحث.' : 'المكتبة فارغة — ارفع أول صورة.';
    }

    addItem(item, { prepend = false } = {}) {
        const old = this.items.get(item.id);
        if (old) old.el.remove();
        const el = h('button', { type: 'button', role: 'option', 'aria-selected': 'false', title: item.name,
            class: 'group relative aspect-square overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200 transition hover:ring-2 hover:ring-brand/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand' },
            h('img', { src: item.url, alt: item.alt || item.name, loading: 'lazy', class: 'h-full w-full object-cover' }),
            h('span', { class: 'check absolute top-1.5 left-1.5 hidden h-6 w-6 place-items-center rounded-full bg-brand text-xs font-bold text-white shadow ring-2 ring-white', text: '✓' }));
        el.addEventListener('click', () => this.select(item));
        el.addEventListener('dblclick', () => { this.select(item); if (this.mode === 'select') this.confirm(); });
        item.el = el;
        this.items.set(item.id, item);
        prepend ? this.grid.prepend(el) : this.grid.append(el);
    }

    select(item, { scroll = false } = {}) {
        if (this.selected?.el) {
            this.selected.el.classList.remove('ring-4', 'ring-brand');
            this.selected.el.setAttribute('aria-selected', 'false');
            this.selected.el.querySelector('.check').classList.replace('grid', 'hidden');
        }
        this.selected = item;
        if (item?.el) {
            item.el.classList.add('ring-4', 'ring-brand');
            item.el.setAttribute('aria-selected', 'true');
            item.el.querySelector('.check').classList.replace('hidden', 'grid');
            if (scroll) item.el.scrollIntoView({ block: 'nearest' });
        }
        if (this.useBtn) {
            this.useBtn.disabled = !item;
            this.footerInfo.textContent = item ? `المحددة: ${item.name}` : 'اختر صورة من المكتبة أو ارفع صورة جديدة.';
        }
        this.renderDetails();
    }

    /** Called by the modal each time it opens. */
    reset(currentPath = null) {
        this.selectedPath = currentPath || null;
        this.select(null);
        this.queue.replaceChildren();
        // Always refetch: the library may have changed in another tab or page since the last open.
        this.load(true);
    }

    confirm() {
        if (this.selected && this.onSelect) this.onSelect(this.selected);
    }

    /* ---------- details panel ---------- */
    renderDetails() {
        const item = this.selected;
        if (!item) {
            this.details.replaceChildren(h('div', { class: 'py-10 text-center text-sm text-slate-400' },
                h('div', { class: 'mb-2 text-3xl', text: '🖼️' }), 'اختر صورة لعرض تفاصيلها'));
            return;
        }
        const row = (k, v) => h('div', { class: 'flex justify-between gap-3 py-1' }, h('dt', { class: 'text-slate-400', text: k }), h('dd', { class: 'min-w-0 truncate text-end font-semibold text-slate-700', dir: 'auto', text: v }));
        const alt = h('input', { type: 'text', class: 'input', value: item.alt || '', placeholder: 'وصف قصير للصورة', maxlength: 255 });
        const altState = h('span', { class: 'text-xs text-emerald-600 opacity-0 transition' });
        alt.addEventListener('change', async () => {
            try {
                const upd = await api(`${cfg().url}/${item.id}`, { method: 'PATCH', body: { alt: alt.value } });
                item.alt = upd.alt;
                altState.textContent = 'تم الحفظ ✓';
                altState.classList.remove('opacity-0');
                setTimeout(() => altState.classList.add('opacity-0'), 1500);
            } catch (e) { this.toast(e.message, true); }
        });
        const urlInput = h('input', { type: 'text', class: 'input text-xs', value: item.url, readonly: true, dir: 'ltr', onfocus: (e) => e.target.select() });
        const copy = h('button', { type: 'button', class: 'btn btn-ghost btn-sm shrink-0', text: 'نسخ', onclick: async () => {
            try { await navigator.clipboard.writeText(item.url); copy.textContent = 'تم ✓'; } catch (e) { urlInput.select(); }
            setTimeout(() => (copy.textContent = 'نسخ'), 1500);
        } });
        this.usageEl = h('div', { class: 'text-xs text-slate-400', text: 'جارٍ التحقق من أماكن الاستخدام…' });

        this.details.replaceChildren(
            h('a', { href: item.url, target: '_blank', rel: 'noopener', class: 'block overflow-hidden rounded-xl bg-[repeating-conic-gradient(#f1f5f9_0_25%,#fff_0_50%)] bg-[length:16px_16px] ring-1 ring-slate-200', title: 'فتح بالحجم الكامل' },
                h('img', { src: item.url, alt: item.alt || '', class: 'mx-auto max-h-56 w-full object-contain' })),
            h('div', { class: 'mt-3 break-all font-bold text-navy', dir: 'auto', text: item.name }),
            h('dl', { class: 'mt-2 divide-y divide-slate-200/70 text-xs' },
                row('اسم الملف', item.filename),
                row('النوع', `${item.type} (${item.mime})`),
                row('الأبعاد', item.width ? `${item.width} × ${item.height} بكسل` : '—'),
                row('الحجم', item.size),
                row('تاريخ الرفع', item.date_label || item.date || '—'),
            ),
            h('label', { class: 'label mt-4 flex items-center justify-between' }, 'النص البديل (Alt)', altState), alt,
            h('label', { class: 'label mt-3' }, 'رابط الملف'), h('div', { class: 'flex gap-2' }, urlInput, copy),
            h('div', { class: 'mt-4' }, h('div', { class: 'label', text: 'مستخدمة في' }), this.usageEl),
            h('button', { type: 'button', class: 'btn btn-danger btn-sm mt-4 w-full', text: 'حذف نهائياً', onclick: () => this.remove(item) }),
        );
        this.loadUsages(item);
    }

    async loadUsages(item) {
        try {
            const full = await api(`${cfg().url}/${item.id}`);
            if (this.selected !== item) return;
            item.usages = full.usages;
            const list = full.usages.length
                ? h('ul', { class: 'space-y-1' }, full.usages.map((u) => h('li', {}, h('a', { href: u.url, class: 'font-semibold text-brand hover:underline', text: u.label }))))
                : h('span', { text: 'غير مستخدمة حالياً' });
            this.usageEl.replaceChildren(list);
            if (full.uploader) this.usageEl.append(h('div', { class: 'mt-2 text-slate-400', text: `رفعها: ${full.uploader}` }));
        } catch (e) {
            this.usageEl.textContent = '—';
        }
    }

    async remove(item) {
        const n = item.usages?.length || 0;
        const msg = n
            ? `هذه الصورة مستخدمة في ${arDigits(n)} مكان. حذفها سيجعلها تختفي من هذه الأماكن.\nهل تريد حذفها نهائياً؟`
            : 'حذف هذه الصورة نهائياً من المكتبة والخادم؟';
        if (!window.confirm(msg)) return;
        try {
            await api(`${cfg().url}/${item.id}`, { method: 'DELETE' });
            item.el.remove();
            this.items.delete(item.id);
            this.total = Math.max(0, (this.total ?? 1) - 1);
            this.select(null);
            this.updateCount();
            this.toast('تم حذف الصورة.');
        } catch (e) { this.toast(e.message, true); }
    }

    /* ---------- uploads ---------- */
    async upload(files) {
        files = files.filter(Boolean);
        if (!files.length) return;
        if (files.length > cfg().max) {
            this.toast(`يمكن رفع ${arDigits(cfg().max)} صورة كحد أقصى في المرة الواحدة.`, true);
            files = files.slice(0, cfg().max);
        }
        let last = null;
        for (const file of files) {
            const bar = h('div', { class: 'h-full w-0 rounded-full bg-brand transition-all' });
            const status = h('span', { class: 'shrink-0 text-xs text-slate-400', text: '٠٪' });
            const row = h('div', { class: 'flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-2 text-sm ring-1 ring-slate-200' },
                h('span', { class: 'min-w-0 flex-1 truncate font-semibold', dir: 'auto', text: file.name }),
                h('div', { class: 'h-1.5 w-24 overflow-hidden rounded-full bg-slate-200' }, bar), status);
            this.queue.append(row);

            const fail = (message) => {
                row.classList.replace('bg-slate-50', 'bg-red-50');
                row.classList.replace('ring-slate-200', 'ring-red-200');
                status.className = 'shrink-0 text-xs font-bold text-red-600';
                status.textContent = message;
                bar.parentElement.remove();
                row.append(h('button', { type: 'button', class: 'text-slate-400 hover:text-slate-600', 'aria-label': 'إخفاء', text: '✕', onclick: () => row.remove() }));
            };
            if (!ALLOWED.includes(file.type)) { fail('صيغة غير مدعومة'); continue; }
            if (file.size > cfg().maxKb * 1024) { fail(`أكبر من ${Math.round(cfg().maxKb / 1024)} ميجابايت`); continue; }

            try {
                const item = await uploadFile(file, (p) => { bar.style.width = p + '%'; status.textContent = arDigits(p) + '٪'; });
                const isNew = !this.items.has(item.id);
                this.addItem(item, { prepend: true });
                if (isNew) this.total = (this.total ?? 0) + 1;
                last = item;
                row.remove();
            } catch (e) { fail(e.message); }
        }
        this.updateCount();
        if (last) this.select(last, { scroll: true });
    }

    toast(text, error = false) {
        const el = h('div', { class: `fixed bottom-5 left-5 z-[60] max-w-sm rounded-xl px-4 py-3 text-sm font-bold shadow-lg ${error ? 'bg-red-600 text-white' : 'bg-navy text-white'}`, role: 'status', text });
        document.body.append(el);
        setTimeout(() => el.remove(), 3500);
    }
}

/* ---------- Picker modal (single shared instance) ---------- */
let picker = null;

export function openMediaPicker({ value = null, title = 'اختيار صورة', onSelect }) {
    const modal = document.getElementById('media-modal');
    if (!modal) return;
    if (!picker) {
        picker = new MediaLibrary(modal.querySelector('[data-media-library]'), { mode: 'select' });
        modal.addEventListener('click', (e) => e.target.closest('[data-modal-close]') && closeMediaPicker());
        document.addEventListener('keydown', (e) => e.key === 'Escape' && !modal.classList.contains('hidden') && closeMediaPicker());
    }
    picker.onSelect = (item) => { onSelect(item); closeMediaPicker(); };
    picker.reset(value);
    modal.querySelector('#media-modal-title').textContent = title;
    picker.lastFocus = document.activeElement;
    modal.classList.remove('hidden');
    document.documentElement.classList.add('overflow-hidden');
    setTimeout(() => picker.search.focus(), 30);
}

export function closeMediaPicker() {
    const modal = document.getElementById('media-modal');
    modal?.classList.add('hidden');
    document.documentElement.classList.remove('overflow-hidden');
    picker?.lastFocus?.focus?.();
}

/* ---------- Image fields: [data-media] boxes (works for repeater rows added later) ---------- */
export function initMediaFields() {
    document.addEventListener('click', (e) => {
        const pick = e.target.closest('[data-media-pick]');
        const box = e.target.closest('[data-media]');
        if (!box) return;
        const keep = box.querySelector('[data-keep]');
        const preview = box.querySelector('[data-preview]');
        const empty = box.querySelector('[data-empty]');
        const name = box.querySelector('[data-filename]');
        const removeBtn = box.querySelector('[data-remove]');
        const setValue = (path, url, label) => {
            if (keep) {
                keep.value = path;
                keep.dispatchEvent(new Event('input', { bubbles: true })); // unsaved-changes warning
            }
            if (preview) { preview.src = url || ''; preview.classList.toggle('hidden', !url); }
            empty?.classList.toggle('hidden', !!url);
            name?.replaceChildren(document.createTextNode(label || ''));
            removeBtn?.classList.toggle('hidden', !path);
        };

        if (pick) {
            e.preventDefault();
            openMediaPicker({
                value: keep?.value,
                title: box.dataset.label ? `اختيار صورة: ${box.dataset.label}` : 'اختيار صورة',
                onSelect: (item) => setValue(item.path, item.url, item.filename),
            });
        } else if (e.target.closest('[data-remove]')) {
            const file = box.querySelector('input[type=file]');
            if (file) file.value = '';
            setValue('', '', '');
        }
    });

    // Non-image file fields (video / PDF) keep a classic file input.
    document.addEventListener('change', (e) => {
        if (!e.target.matches('[data-media] input[type=file]')) return;
        const f = e.target.files?.[0];
        e.target.closest('[data-media]').querySelector('[data-filename]')?.replaceChildren(document.createTextNode(f ? f.name : ''));
    });
}

/* ---------- Full library page ---------- */
export function initLibraryPage() {
    const root = document.querySelector('[data-media-library][data-mode="manage"]');
    if (root) new MediaLibrary(root, { mode: 'manage' });
}
