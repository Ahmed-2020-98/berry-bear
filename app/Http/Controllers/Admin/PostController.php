<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\ContentSaver;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->q.'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latestFirst()->paginate(20)->withQueryString();

        return view('admin.posts.index', ['posts' => $posts]);
    }

    public function create()
    {
        return $this->form(new Post(['status' => 'published', 'published_at' => now()]));
    }

    public function store(Request $request)
    {
        $post = Post::create($this->data($request, new Post));

        return redirect()->route('admin.posts.edit', $post)->with('success', 'تم نشر المقال.');
    }

    public function edit(Post $post)
    {
        return $this->form($post);
    }

    public function update(Request $request, Post $post)
    {
        $post->update($this->data($request, $post));

        return redirect()->route('admin.posts.edit', $post)->with('success', 'تم حفظ المقال.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'تم حذف المقال.');
    }

    protected function form(Post $post)
    {
        return view('admin.posts.form', [
            'post' => $post,
            'categories' => Post::whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    protected function data(Request $request, Post $post): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('posts')->ignore($post->id)],
            'category' => ['nullable', 'string', 'max:100'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'status' => ['required', 'in:published,draft'],
            'published_at' => ['nullable', 'date'],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:120'],
            'cover' => ['nullable', 'string'],
            'cover_file' => array_merge(['nullable'], ContentSaver::IMAGE_RULE),
        ], [], ['title' => 'العنوان', 'slug' => 'الرابط', 'cover_file' => 'صورة الغلاف', 'published_at' => 'تاريخ النشر']);

        $data['slug'] = unique_slug(Post::class, ($data['slug'] ?? null) ?: $data['title'], $post->id);
        // Post slugs share the root URL space with pages and fixed routes.
        if (in_array($data['slug'], reserved_slugs(), true)) {
            $data['slug'] .= '-post';
        }
        if ($request->hasFile('cover_file')) {
            $data['cover'] = Media::store($request->file('cover_file'), 'posts');
        }
        unset($data['cover_file']);
        $data['published_at'] = $data['published_at'] ?? ($post->published_at ?? now());

        return $data;
    }
}
