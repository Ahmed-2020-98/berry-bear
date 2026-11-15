<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CollectionController extends Controller
{
    public function index()
    {
        return view('admin.collections.index', ['collections' => Collection::withCount('products')->orderBy('sort')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.collections.form', ['collection' => new Collection]);
    }

    public function store(Request $request)
    {
        Collection::create($this->data($request, new Collection) + ['sort' => (int) Collection::max('sort') + 1]);

        return redirect()->route('admin.collections.index')->with('success', 'تمت إضافة التشكيلة.');
    }

    public function edit(Collection $collection)
    {
        return view('admin.collections.form', ['collection' => $collection]);
    }

    public function update(Request $request, Collection $collection)
    {
        $collection->update($this->data($request, $collection));

        return redirect()->route('admin.collections.index')->with('success', 'تم حفظ التشكيلة.');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();

        return redirect()->route('admin.collections.index')->with('success', 'تم حذف التشكيلة (المنتجات لم تُحذف).');
    }

    protected function data(Request $request, Collection $c): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('collections')->ignore($c->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [], ['name' => 'الاسم', 'slug' => 'الرابط']);
        $data['slug'] = unique_slug(Collection::class, ($data['slug'] ?? null) ?: $data['name'], $c->id);

        return $data;
    }
}
