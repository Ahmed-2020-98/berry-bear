<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageReceived;
use App\Models\Collection;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class SiteController extends Controller
{
    public function home(Request $request)
    {
        if ($request->filled('s')) {
            return redirect()->route('search', ['s' => $request->query('s')]);
        }

        return view('site.home', [
            'products' => Product::published()->ordered()->limit(6)->get(),
            'testimonials' => Testimonial::published()->ordered()->limit(3)->get(),
            'posts' => Post::published()->latestFirst()->limit(3)->get(),
        ]);
    }

    public function about()
    {
        return view('site.about');
    }

    public function products()
    {
        return view('site.products', [
            'collection' => null,
            'products' => Product::published()->ordered()->get(),
        ]);
    }

    public function collection(string $slug)
    {
        $collection = Collection::where('slug', $slug)->firstOrFail();

        return view('site.products', [
            'collection' => $collection,
            'products' => $collection->products()->published()->ordered()->get(),
        ]);
    }

    public function product(string $slug)
    {
        return view('site.product', ['product' => Product::published()->where('slug', $slug)->firstOrFail()]);
    }

    public function faqs()
    {
        return view('site.faqs', ['faqs' => Faq::published()->ordered()->get()]);
    }

    public function contact()
    {
        return view('site.contact');
    }

    public function sendContact(Request $request)
    {
        // Honeypot: silently accept bots without storing anything.
        if ($request->filled('website')) {
            return redirect()->to(route('contact').'#form')->with('contact_sent', true);
        }

        $key = 'contact:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withInput()->withErrors(['throttle' => 'أرسلت عددًا كبيرًا من الرسائل، يرجى المحاولة بعد قليل.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\s\-()٠-٩]{6,30}$/u'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['nullable', 'in:'.implode(',', array_keys(ContactMessage::SUBJECTS))],
            'message' => ['required', 'string', 'max:5000'],
        ], [], [
            'name' => 'الاسم', 'phone' => 'رقم الجوال', 'email' => 'البريد الإلكتروني', 'message' => 'الرسالة',
        ]);

        RateLimiter::hit($key, 600);

        $message = ContactMessage::create($data + ['ip' => $request->ip()]);

        $to = setting('notify_email') ?: setting('contact_email');
        if ($to && filter_var($to, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($to)->send(new ContactMessageReceived($message));
            } catch (\Throwable $e) {
                Log::warning('Contact notification failed: '.$e->getMessage());
            }
        }

        return redirect()->to(route('contact').'#form')->with('contact_sent', true);
    }

    public function blog(Request $request)
    {
        return view('site.blog.index', [
            'posts' => Post::published()->latestFirst()->paginate(9),
            'heroTitle' => content('blog', 'blog_hero_title'),
            'heroSub' => content('blog', 'blog_hero_sub'),
            'crumb' => 'المدونة',
        ]);
    }

    public function category(string $category)
    {
        $posts = Post::published()->where('category', $category)->latestFirst()->paginate(9);
        abort_if($posts->isEmpty() && $posts->currentPage() === 1, 404);

        return view('site.blog.index', ['posts' => $posts, 'heroTitle' => e($category), 'heroSub' => '', 'crumb' => 'المدونة', 'pageTitle' => $category]);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('s', ''));
        $posts = Post::published()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('title', 'like', "%$q%")->orWhere('body', 'like', "%$q%")))
            ->latestFirst()->paginate(9)->withQueryString();

        return view('site.blog.index', ['posts' => $posts, 'heroTitle' => 'نتائج البحث', 'heroSub' => 'عن: '.$q, 'crumb' => 'بحث', 'pageTitle' => 'نتائج البحث']);
    }

    /** WordPress-style /{slug} permalinks: blog posts first, then static pages. */
    public function slug(string $slug)
    {
        if ($post = Post::published()->where('slug', $slug)->first()) {
            $base = Post::published();

            return view('site.blog.show', [
                'post' => $post,
                'prev' => (clone $base)->where('published_at', '<', $post->published_at)->latestFirst()->first(),
                'next' => (clone $base)->where('published_at', '>', $post->published_at)->orderBy('published_at')->first(),
                'related' => (clone $base)->whereKeyNot($post->id)
                    ->when($post->category, fn ($q) => $q->where('category', $post->category))
                    ->latestFirst()->limit(3)->get(),
            ]);
        }

        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('site.page', ['page' => $page]);
    }
}
