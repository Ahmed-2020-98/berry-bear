<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Post;
use App\Models\Product;
use App\Models\Testimonial;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'المنتجات', 'value' => Product::count(), 'icon' => '🍬', 'route' => 'admin.products.index'],
                ['label' => 'المقالات', 'value' => Post::count(), 'icon' => '📝', 'route' => 'admin.posts.index'],
                ['label' => 'الأسئلة', 'value' => Faq::count(), 'icon' => '❓', 'route' => 'admin.faqs.index'],
                ['label' => 'آراء العملاء', 'value' => Testimonial::count(), 'icon' => '💬', 'route' => 'admin.testimonials.index'],
            ],
            'unread' => ContactMessage::whereNull('read_at')->count(),
            'messages' => ContactMessage::latest()->limit(6)->get(),
        ]);
    }
}
