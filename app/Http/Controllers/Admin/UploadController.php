<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ContentSaver;
use App\Support\Media;
use Illuminate\Http\Request;

/** Image uploads from the rich-text editor. */
class UploadController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['file' => array_merge(['required'], ContentSaver::IMAGE_RULE)]);

        return response()->json(['url' => '/storage/'.Media::store($request->file('file'), 'editor')]);
    }
}
