<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Content;
use App\Support\ContentSaver;
use App\Support\ContentSchema;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    protected const PREVIEW = ['home' => '/', 'about' => '/about', 'products' => '/products', 'faqs' => '/faqs', 'contact' => '/contact', 'blog' => '/blog'];

    public function edit(string $key)
    {
        abort_unless(ContentSchema::has($key) && $key !== 'settings', 404);

        return $this->form($key, route('admin.content.update', $key), url(self::PREVIEW[$key] ?? '/'));
    }

    public function update(Request $request, string $key)
    {
        abort_unless(ContentSchema::has($key) && $key !== 'settings', 404);
        Content::merge($key, ContentSaver::save($request, $key));

        return redirect()->route('admin.content.edit', $key)->with('success', 'تم حفظ محتوى الصفحة.');
    }

    public function editSettings()
    {
        return $this->form('settings', route('admin.settings.update'), null);
    }

    public function updateSettings(Request $request)
    {
        Content::merge('settings', ContentSaver::save($request, 'settings'));

        return redirect()->route('admin.settings')->with('success', 'تم حفظ الإعدادات.');
    }

    protected function form(string $key, string $action, ?string $previewUrl)
    {
        $values = Content::withDefaults($key);
        // Re-populate after a failed validation.
        if (old('data')) {
            $values = array_merge($values, old('data'));
        }

        return view('admin.content.edit', [
            'key' => $key,
            'group' => ContentSchema::group($key),
            'values' => $values,
            'action' => $action,
            'previewUrl' => $previewUrl,
        ]);
    }
}
