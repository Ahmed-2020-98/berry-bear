<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use App\Support\ContentSaver;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('collection')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->q.'%'))
            ->when($request->filled('collection'), fn ($q) => $q->where('collection_id', $request->collection))
            ->ordered()->get();

        return view('admin.products.index', ['products' => $products, 'collections' => Collection::orderBy('sort')->pluck('name', 'id')]);
    }

    public function create()
    {
        return $this->form(new Product(['is_published' => true, 'flavor_color' => '#E51B6E']));
    }

    public function store(Request $request)
    {
        $product = Product::create($this->data($request, new Product) + ['sort' => (int) Product::max('sort') + 1]);

        return redirect()->route('admin.products.edit', $product)->with('success', 'تمت إضافة المنتج.');
    }

    public function edit(Product $product)
    {
        return $this->form($product);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->data($request, $product));

        return redirect()->route('admin.products.edit', $product)->with('success', 'تم حفظ المنتج.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'تم حذف المنتج.');
    }

    protected function form(Product $product)
    {
        return view('admin.products.form', ['product' => $product, 'collections' => Collection::orderBy('sort')->pluck('name', 'id')]);
    }

    protected function data(Request $request, Product $product): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('products')->ignore($product->id)],
            'collection_id' => ['nullable', 'exists:collections,id'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'flavor_label' => ['nullable', 'string', 'max:100'],
            'flavor_color' => ['nullable', 'regex:/^#([0-9a-f]{3}|[0-9a-f]{6})$/i'],
            'anchor_id' => ['nullable', 'alpha_dash', 'max:60'],
            'is_published' => ['boolean'],
            'image' => ['nullable', 'string'],
            'carton_image' => ['nullable', 'string'],
            'image_file' => array_merge(['nullable'], ContentSaver::IMAGE_RULE),
            'carton_image_file' => array_merge(['nullable'], ContentSaver::IMAGE_RULE),
        ], [], ['title' => 'الاسم', 'slug' => 'الرابط', 'image_file' => 'صورة المنتج', 'carton_image_file' => 'صورة الكرتونة', 'flavor_color' => 'اللون']);

        $data['slug'] = unique_slug(Product::class, ($data['slug'] ?? null) ?: $data['title'], $product->id);
        foreach (['image', 'carton_image'] as $f) {
            if ($request->hasFile($f.'_file')) {
                $data[$f] = Media::store($request->file($f.'_file'), 'products');
            }
            unset($data[$f.'_file']);
        }
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
