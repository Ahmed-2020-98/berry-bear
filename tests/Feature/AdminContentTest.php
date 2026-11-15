<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use App\Support\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->admin()->create();
    }

    public function test_page_sections_with_repeaters_and_uploads_are_saved(): void
    {
        $this->actingAs($this->admin)->put('/admin/content/home', [
            'data' => [
                'hero_badge' => 'شارة جديدة',
                'hero_image' => '',
                'gallery_images' => [
                    ['img' => 'old.jpeg', 'title' => 'الأولى', 'cap' => 'وصف'],
                    ['img' => '', 'title' => 'الثانية', 'cap' => ''],
                    ['img' => '', 'title' => '', 'cap' => ''], // empty row is dropped
                ],
                'marquee_chips' => [['label' => 'البقالات', 'color' => '#9B63B5']],
            ],
            'files' => [
                'hero_image' => UploadedFile::fake()->image('hero.jpg'),
                'gallery_images' => [1 => ['img' => UploadedFile::fake()->image('g.png')]],
            ],
        ])->assertRedirect('/admin/content/home')->assertSessionHasNoErrors();

        $data = Content::data('home');
        $this->assertSame('شارة جديدة', $data['hero_badge']);
        $this->assertStringStartsWith('uploads/library/', $data['hero_image']);
        Storage::disk('public')->assertExists($data['hero_image']);
        $this->assertCount(2, $data['gallery_images']);
        $this->assertSame('old.jpeg', $data['gallery_images'][0]['img']);
        $this->assertStringStartsWith('uploads/library/', $data['gallery_images'][1]['img']);

        $this->get('/')->assertSee('شارة جديدة')->assertSee('الثانية');
    }

    public function test_invalid_colour_and_upload_are_rejected(): void
    {
        $this->actingAs($this->admin)->from('/admin/content/home')->put('/admin/content/home', [
            'data' => ['marquee_chips' => [['label' => 'x', 'color' => 'red;']]],
        ])->assertSessionHasErrors();

        $this->actingAs($this->admin)->from('/admin/content/home')->put('/admin/content/home', [
            'files' => ['hero_image' => UploadedFile::fake()->create('evil.php', 1, 'application/x-php')],
        ])->assertSessionHasErrors('files.hero_image');
    }

    public function test_settings_toggle_false_is_kept(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings', ['data' => ['preloader_show_bar' => '0', 'preloader_media_type' => 'logo']])
            ->assertSessionHasNoErrors();
        $this->assertFalse((bool) setting('preloader_show_bar'));
        $this->get('/')->assertDontSee('pre-bar', false);
    }

    public function test_product_crud_with_image_and_sorting(): void
    {
        $this->actingAs($this->admin)->post('/admin/products', [
            'title' => 'دببة الفراولة', 'excerpt' => 'لذيذة', 'body' => '<p>تفاصيل</p>',
            'flavor_label' => 'فراولة', 'flavor_color' => '#E51B6E', 'is_published' => '1',
            'image_file' => UploadedFile::fake()->image('p.jpg'),
        ])->assertSessionHasNoErrors();

        $product = Product::firstWhere('title', 'دببة الفراولة');
        $this->assertSame('دببة-الفراولة', $product->slug);
        Storage::disk('public')->assertExists($product->image);
        $this->get($product->url())->assertOk()->assertSee('تفاصيل');

        $other = Product::create(['title' => 'ب', 'slug' => 'b', 'sort' => 5]);
        $this->actingAs($this->admin)->postJson('/admin/sort/products', ['ids' => [$other->id, $product->id]])->assertOk();
        $this->assertSame(1, $other->fresh()->sort);

        $this->actingAs($this->admin)->delete("/admin/products/{$product->id}")->assertRedirect('/admin/products');
        $this->assertModelMissing($product);
    }

    public function test_post_slug_never_shadows_a_fixed_route(): void
    {
        $this->actingAs($this->admin)->post('/admin/posts', ['title' => 'Contact', 'slug' => 'contact', 'status' => 'published']);
        $this->assertSame('contact-post', Post::first()->slug);
    }

    public function test_faq_create_and_toggle(): void
    {
        $this->actingAs($this->admin)->post('/admin/faqs', ['question' => 'سؤال؟', 'answer' => '<p>جواب</p>', 'is_published' => '0']);
        $this->assertFalse(Faq::first()->is_published);
        $this->get('/faqs')->assertDontSee('سؤال؟');
    }

    public function test_menu_create_and_reorder(): void
    {
        $this->actingAs($this->admin)->post('/admin/menu', ['label' => 'منتجاتنا']);
        $this->actingAs($this->admin)->post('/admin/menu', ['label' => 'الكل', 'url' => '/products']);
        [$parent, $child] = MenuItem::orderBy('id')->get()->all();

        $this->actingAs($this->admin)->postJson('/admin/menu-order', ['tree' => [['id' => $parent->id, 'children' => [$child->id]]]])->assertOk();
        $this->assertSame($parent->id, $child->fresh()->parent_id);

        $this->get('/')->assertSee('has-drop', false)->assertSee('الكل');
    }

    public function test_messages_inbox_marks_read(): void
    {
        $msg = ContactMessage::create(['name' => 'أ', 'email' => 'a@example.com', 'message' => 'مرحبا', 'subject' => 'general']);
        $this->actingAs($this->admin)->get('/admin/messages')->assertSee('أ');
        $this->actingAs($this->admin)->get("/admin/messages/{$msg->id}")->assertOk()->assertSee('مرحبا');
        $this->assertNotNull($msg->fresh()->read_at);
    }

    public function test_editor_image_upload_endpoint(): void
    {
        $this->actingAs($this->admin)->postJson('/admin/uploads', ['file' => UploadedFile::fake()->image('x.png')])
            ->assertOk()->assertJsonPath('url', fn ($url) => str_starts_with($url, '/storage/uploads/library/'));
    }
}
