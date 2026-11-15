<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function index()
    {
        return view('admin.menu.index', [
            'items' => MenuItem::whereNull('parent_id')->with('children')->orderBy('sort')->orderBy('id')->get(),
            'parents' => MenuItem::whereNull('parent_id')->orderBy('sort')->pluck('label', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->data($request);
        MenuItem::create($data + ['sort' => (int) MenuItem::where('parent_id', $data['parent_id'] ?? null)->max('sort') + 1]);

        return $this->done('تمت إضافة الرابط.');
    }

    public function update(Request $request, MenuItem $item)
    {
        $data = $this->data($request);
        if (($data['parent_id'] ?? null) == $item->id || ($data['parent_id'] && $item->children()->exists())) {
            $data['parent_id'] = $item->parent_id; // keep the menu two levels deep
        }
        $item->update($data);

        return $this->done('تم حفظ الرابط.');
    }

    public function destroy(MenuItem $item)
    {
        $item->delete();

        return $this->done('تم حذف الرابط.');
    }

    /** Body: {tree: [{id, children: [id…]}…]} */
    public function reorder(Request $request)
    {
        $tree = $request->validate(['tree' => ['required', 'array'], 'tree.*.id' => ['required', 'integer'], 'tree.*.children' => ['array'], 'tree.*.children.*' => ['integer']])['tree'];

        DB::transaction(function () use ($tree) {
            foreach (array_values($tree) as $i => $node) {
                MenuItem::whereKey($node['id'])->update(['parent_id' => null, 'sort' => $i + 1]);
                foreach (array_values($node['children'] ?? []) as $j => $childId) {
                    MenuItem::whereKey($childId)->update(['parent_id' => $node['id'], 'sort' => $j + 1]);
                }
            }
        });
        Cache::forget('site.menu');

        return response()->json(['ok' => true]);
    }

    protected function data(Request $request): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'url' => ['nullable', 'string', 'max:500'],
            'parent_id' => ['nullable', 'exists:menu_items,id'],
            'color' => ['nullable', 'regex:/^#([0-9a-f]{3}|[0-9a-f]{6})$/i'],
        ], [], ['label' => 'النص', 'url' => 'الرابط', 'color' => 'اللون']);

        return $data + ['open_new_tab' => $request->boolean('open_new_tab')];
    }

    protected function done(string $msg)
    {
        Cache::forget('site.menu');

        return redirect()->route('admin.menu.index')->with('success', $msg);
    }
}
