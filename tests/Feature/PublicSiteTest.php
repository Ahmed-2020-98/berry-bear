<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Support\Content;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_main_pages_render_with_theme_defaults(): void
    {
        foreach (['/', '/about', '/products', '/faqs', '/contact', '/blog', '/search?s='.rawurlencode('جيلي')] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/')
            ->assertSee('عالمٌ من <span class="pink">المرح</span>', false)
            ->assertSee('دببة الجيلي الكلاسيكية')
            ->assertSee('أم يزن');
    }

    public function test_product_post_and_extra_page_permalinks(): void
    {
        $product = Product::first();
        $this->get($product->url())->assertOk()->assertSee($product->title);

        $post = Post::first();
        $this->get($post->url())->assertOk()->assertSee($post->title);

        Page::create(['title' => 'سياسة الخصوصية', 'slug' => 'privacy-policy', 'body' => '<p>نص</p>']);
        $this->get('/privacy-policy')->assertOk()->assertSee('سياسة الخصوصية');
    }

    public function test_hidden_content_and_unknown_urls_404(): void
    {
        Product::first()->update(['is_published' => false]);
        $this->get(Product::first()->url())->assertNotFound();
        Post::first()->update(['status' => 'draft']);
        $this->get(Post::first()->url())->assertNotFound();
        $this->get('/no-such-page')->assertNotFound()->assertSee('غير موجودة');
    }

    public function test_stored_content_overrides_defaults_and_html_is_sanitised(): void
    {
        Content::put('home', ['hero_title' => 'مرحبا <span class="hl">بك</span><script>alert(1)</script>']);

        $this->get('/')
            ->assertSee('مرحبا <span class="hl">بك</span>', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_wordpress_search_query_redirects(): void
    {
        $this->get('/?s=gummy')->assertRedirect(route('search', ['s' => 'gummy']));
    }
}
