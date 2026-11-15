<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Extra free-form pages (privacy policy, terms…) served at /{slug}. */
class PageController extends Controller
{
    public function index()
    {
        return view('admin.pages.index', ['pages' => Page::orderBy('title')->get()]);
    }

    public function create()
    {
        return view('admin.pages.form', ['page' => new Page(['is_published' => true])]);
    }

    public function store(Request $request)
    {
        $page = Page::create($this->data($request, new Page));

        return redirect()->route('admin.pages.edit', $page)->with('success', 'تمت إضافة الصفحة.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.form', ['page' => $page]);
    }

    public function update(Request $request, Page $page)
    {
        $page->update($this->data($request, $page));

        return redirect()->route('admin.pages.edit', $page)->with('success', 'تم حفظ الصفحة.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'تم حذف الصفحة.');
    }

    protected function data(Request $request, Page $page): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('pages')->ignore($page->id), Rule::notIn(reserved_slugs())],
            'body' => ['nullable', 'string'],
        ], [], ['title' => 'العنوان', 'slug' => 'الرابط']);
        $data['slug'] = unique_slug(Page::class, ($data['slug'] ?? null) ?: $data['title'], $page->id);
        if (in_array($data['slug'], reserved_slugs(), true)) {
            $data['slug'] .= '-page';
        }

        return $data + ['is_published' => $request->boolean('is_published')];
    }
}
