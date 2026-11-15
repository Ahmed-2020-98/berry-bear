import Sortable from 'sortablejs';
import Quill from 'quill';
import { initLibraryPage, initMediaFields, openMediaPicker } from './media-library';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

/* ---------- Mobile sidebar ---------- */
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) =>
        btn.addEventListener('click', () => {
            sidebar?.classList.toggle('translate-x-full');
            backdrop?.classList.toggle('hidden');
        }),
    );
    backdrop?.addEventListener('click', () => {
        sidebar?.classList.add('translate-x-full');
        backdrop.classList.add('hidden');
    });
}

/* ---------- Flash toasts ---------- */
function initFlash() {
    document.querySelectorAll('[data-flash]').forEach((el) => {
        setTimeout(() => el.classList.add('opacity-0', 'translate-y-2'), 4000);
        setTimeout(() => el.remove(), 4600);
        el.querySelector('[data-dismiss]')?.addEventListener('click', () => el.remove());
    });
}

/* ---------- Tabs (remember last tab per form) ---------- */
function initTabs() {
    document.querySelectorAll('[data-tabs]').forEach((wrap) => {
        const key = 'tab:' + location.pathname;
        const tabs = wrap.querySelectorAll('[data-tab]');
        const panels = document.querySelectorAll('[data-panel]');
        const show = (id) => {
            tabs.forEach((t) => t.classList.toggle('active', t.dataset.tab === id));
            panels.forEach((p) => p.classList.toggle('hidden', p.dataset.panel !== id));
            try { sessionStorage.setItem(key, id); } catch (e) {}
        };
        tabs.forEach((t) => t.addEventListener('click', () => show(t.dataset.tab)));
        // Jump to the tab that holds the first validation error.
        const err = document.querySelector('[data-panel] .error');
        let saved = null;
        try { saved = sessionStorage.getItem(key); } catch (e) {}
        const initial = err?.closest('[data-panel]')?.dataset.panel
            || (saved && wrap.querySelector(`[data-tab="${saved}"]`) ? saved : tabs[0]?.dataset.tab);
        if (initial) show(initial);
    });
}

/* ---------- Colour inputs (picker <-> text) ---------- */
function initColors(root = document) {
    root.querySelectorAll('[data-color]:not([data-ready])').forEach((box) => {
        box.dataset.ready = '1';
        const picker = box.querySelector('input[type=color]');
        const text = box.querySelector('input[type=text]');
        picker?.addEventListener('input', () => (text.value = picker.value));
        text?.addEventListener('input', () => {
            if (/^#[0-9a-f]{6}$/i.test(text.value)) picker.value = text.value;
        });
    });
}

/* ---------- Repeaters ---------- */
function reindex(list) {
    [...list.children].forEach((row, i) => {
        row.querySelectorAll('[data-name]').forEach((el) => {
            el.name = el.dataset.name.replace('#', i);
        });
        row.querySelector('[data-row-num]')?.replaceChildren(document.createTextNode(i + 1));
    });
}

function initRepeaters(root = document) {
    root.querySelectorAll('[data-repeater]:not([data-ready])').forEach((rep) => {
        rep.dataset.ready = '1';
        const list = rep.querySelector('[data-rows]');
        const tpl = rep.querySelector('template');
        reindex(list);
        Sortable.create(list, { handle: '.drag-handle', animation: 150, onEnd: () => reindex(list) });

        rep.querySelector('[data-add]')?.addEventListener('click', () => {
            list.appendChild(tpl.content.firstElementChild.cloneNode(true));
            reindex(list);
            initColors(list);
            list.lastElementChild.querySelector('input,textarea,select')?.focus();
        });

        list.addEventListener('click', (e) => {
            const row = e.target.closest('[data-row]');
            if (!row) return;
            if (e.target.closest('[data-row-remove]')) {
                row.remove();
                reindex(list);
            } else if (e.target.closest('[data-row-up]') && row.previousElementSibling) {
                list.insertBefore(row, row.previousElementSibling);
                reindex(list);
            } else if (e.target.closest('[data-row-down]') && row.nextElementSibling) {
                list.insertBefore(row.nextElementSibling, row);
                reindex(list);
            } else if (e.target.closest('[data-row-toggle]')) {
                row.querySelector('[data-row-body]')?.classList.toggle('hidden');
            }
        });
    });
}

/* ---------- Rich text (Quill) ---------- */
function initEditors() {
    document.querySelectorAll('[data-editor]').forEach((wrap) => {
        const input = wrap.querySelector('input[type=hidden]');
        const host = wrap.querySelector('[data-editor-host]');
        const quill = new Quill(host, {
            theme: 'snow',
            placeholder: 'اكتب المحتوى هنا…',
            modules: {
                toolbar: {
                    container: [
                        [{ header: [2, 3, 4, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        [{ align: [] }, 'blockquote'],
                        ['link', 'image'],
                        ['clean'],
                    ],
                    handlers: { image: () => uploadImage(quill) },
                },
            },
        });
        quill.format('direction', 'rtl');
        quill.format('align', 'right');
        const sync = () => (input.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML);
        quill.on('text-change', sync);
        wrap.closest('form')?.addEventListener('submit', sync);
    });
}

function uploadImage(quill) {
    // Pick from the media library (or upload there) instead of a bare file dialog.
    const range = quill.getSelection(true);
    openMediaPicker({
        title: 'إدراج صورة في المحتوى',
        onSelect: (item) => {
            quill.insertEmbed(range ? range.index : quill.getLength(), 'image', item.src, 'user');
            if (item.alt) quill.root.querySelectorAll(`img[src="${CSS.escape(item.src)}"]:not([alt])`).forEach((img) => (img.alt = item.alt));
        },
    });
}

/* ---------- Drag-sort for index tables ---------- */
function initSortTables() {
    document.querySelectorAll('[data-sort-url]').forEach((tbody) => {
        Sortable.create(tbody, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: async () => {
                const ids = [...tbody.querySelectorAll('[data-id]')].map((r) => r.dataset.id);
                const status = document.querySelector('[data-sort-status]');
                const res = await fetch(tbody.dataset.sortUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf(), 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify({ ids }),
                });
                if (status) {
                    status.textContent = res.ok ? 'تم حفظ الترتيب ✓' : 'تعذّر حفظ الترتيب';
                    status.classList.remove('opacity-0');
                    setTimeout(() => status.classList.add('opacity-0'), 2000);
                }
            },
        });
    });
}

/* ---------- Menu tree (two levels, drag between groups) ---------- */
function initMenuTree() {
    const root = document.querySelector('[data-menu-tree]');
    if (!root) return;
    const status = document.querySelector('[data-sort-status]');
    const save = async () => {
        const tree = [...root.children].map((li) => ({
            id: li.dataset.id,
            children: [...(li.querySelector('[data-menu-children]')?.children || [])].map((c) => c.dataset.id),
        }));
        const res = await fetch(root.dataset.url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf(), 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ tree }),
        });
        if (status) {
            status.textContent = res.ok ? 'تم حفظ ترتيب القائمة ✓' : 'تعذّر حفظ الترتيب';
            status.classList.remove('opacity-0');
            setTimeout(() => status.classList.add('opacity-0'), 2000);
        }
    };
    const opts = { handle: '.drag-handle', animation: 150, onEnd: save };
    Sortable.create(root, { ...opts, group: { name: 'top', put: (to, from, el) => !el.querySelector('[data-menu-children] > *') } });
    root.querySelectorAll('[data-menu-children]').forEach((ul) =>
        Sortable.create(ul, { ...opts, group: { name: 'child', put: ['child', 'top'] } }),
    );
}

/* ---------- Confirm before destructive submits ---------- */
function initConfirm() {
    document.addEventListener('submit', (e) => {
        const msg = e.target.dataset?.confirm;
        if (msg && !window.confirm(msg)) e.preventDefault();
    });
}

/* ---------- Slug from title ---------- */
function initSlug() {
    const title = document.querySelector('[data-slug-source]');
    const slug = document.querySelector('[data-slug-target]');
    if (!title || !slug) return;
    let touched = slug.value !== '';
    slug.addEventListener('input', () => (touched = true));
    title.addEventListener('input', () => {
        if (touched) return;
        slug.value = title.value.trim().toLowerCase()
            .replace(/[^\p{L}\p{N}\s-]/gu, '').replace(/\s+/g, '-').replace(/-+/g, '-');
    });
}

/* ---------- Warn about unsaved changes ---------- */
function initDirty() {
    document.querySelectorAll('form[data-dirty-check]').forEach((form) => {
        let dirty = false;
        form.addEventListener('input', () => (dirty = true));
        form.addEventListener('submit', () => (dirty = false));
        window.addEventListener('beforeunload', (e) => {
            if (dirty) { e.preventDefault(); e.returnValue = ''; }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initFlash();
    initTabs();
    initMediaFields();
    initLibraryPage();
    initColors();
    initRepeaters();
    initEditors();
    initSortTables();
    initMenuTree();
    initConfirm();
    initSlug();
    initDirty();
});
