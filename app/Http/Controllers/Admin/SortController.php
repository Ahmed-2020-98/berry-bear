<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Faq;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Persists drag-and-drop order for sortable index tables. */
class SortController extends Controller
{
    protected const MODELS = [
        'products' => Product::class,
        'collections' => Collection::class,
        'faqs' => Faq::class,
        'testimonials' => Testimonial::class,
    ];

    public function __invoke(Request $request, string $type)
    {
        $model = self::MODELS[$type] ?? abort(404);
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];

        DB::transaction(function () use ($ids, $model) {
            foreach (array_values($ids) as $i => $id) {
                $model::whereKey($id)->update(['sort' => $i + 1]);
            }
        });

        return response()->json(['ok' => true]);
    }
}
