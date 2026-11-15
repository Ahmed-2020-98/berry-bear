<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaItem;
use App\Support\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/** Central media library: grid page + JSON API used by the library and the picker modal. */
class MediaController extends Controller
{
    public const PER_PAGE = 40;

    public const MAX_FILES = 20;

    public function index(Request $request)
    {
        if (! $request->expectsJson()) {
            return view('admin.media.index', ['total' => MediaItem::count()]);
        }

        $page = MediaItem::query()->search($request->string('q'))->latest('id')->paginate(self::PER_PAGE);

        return response()->json([
            'data' => $page->getCollection()->map->toPickerArray()->values(),
            'total' => $page->total(),
            'next_page' => $page->hasMorePages() ? $page->currentPage() + 1 : null,
        ]);
    }

    /** Upload one or more images. Each file is validated on its own so one bad file doesn't sink the batch. */
    public function store(Request $request): JsonResponse
    {
        $files = $request->file('files', $request->file('file') ? [$request->file('file')] : []);
        $files = array_values(array_filter(is_array($files) ? $files : [$files]));

        if ($files === []) {
            return response()->json(['message' => 'اختر صورة واحدة على الأقل.', 'items' => [], 'errors' => []], 422);
        }
        if (count($files) > self::MAX_FILES) {
            return response()->json(['message' => 'يمكن رفع '.self::MAX_FILES.' صورة كحد أقصى في المرة الواحدة.', 'items' => [], 'errors' => []], 422);
        }

        $items = [];
        $errors = [];
        foreach ($files as $file) {
            $v = Validator::make(['file' => $file], [
                'file' => ['required', 'file', 'image', 'mimes:'.implode(',', Media::IMAGE_MIMES), 'max:'.Media::MAX_KB],
            ], [], ['file' => 'الصورة']);
            $name = $file->getClientOriginalName();
            if ($v->fails()) {
                $errors[] = ['name' => $name, 'message' => $v->errors()->first('file')];

                continue;
            }
            try {
                $items[] = Media::library($file, $request->user()->id)->toPickerArray();
            } catch (\Throwable $e) {
                report($e);
                $errors[] = ['name' => $name, 'message' => 'تعذّر حفظ الملف على الخادم.'];
            }
        }

        return response()->json(['items' => $items, 'errors' => $errors], $items ? 201 : 422);
    }

    public function show(MediaItem $media): JsonResponse
    {
        return response()->json($media->toPickerArray() + [
            'usages' => $media->usages(),
            'uploader' => $media->uploader?->name,
        ]);
    }

    public function update(Request $request, MediaItem $media): JsonResponse
    {
        $data = $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
            'original_name' => ['sometimes', 'required', 'string', 'max:240'],
        ], [], ['alt' => 'النص البديل', 'original_name' => 'اسم الملف']);
        $media->update($data);

        return response()->json($media->toPickerArray());
    }

    public function destroy(Request $request, MediaItem $media)
    {
        $media->delete();

        return $request->expectsJson()
            ? response()->json(['deleted' => true])
            : redirect()->route('admin.media.index')->with('success', 'تم حذف الصورة.');
    }
}
