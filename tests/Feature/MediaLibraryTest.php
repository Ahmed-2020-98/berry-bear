<?php

namespace Tests\Feature;

use App\Models\MediaItem;
use App\Models\Product;
use App\Models\User;
use App\Support\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->admin()->create();
    }

    protected function upload(array $files)
    {
        return $this->actingAs($this->admin)->post('/admin/media', ['files' => $files], ['Accept' => 'application/json']);
    }

    public function test_library_page_requires_login_and_renders(): void
    {
        $this->get('/admin/media')->assertRedirect('/admin/login');
        $this->actingAs($this->admin)->get('/admin/media')->assertOk()->assertSee('مكتبة الوسائط')->assertSee('data-media-library', false);
    }

    public function test_multiple_images_upload_with_metadata(): void
    {
        $res = $this->upload([
            UploadedFile::fake()->image('واجهة المتجر.jpg', 640, 480),
            UploadedFile::fake()->image('logo.png', 200, 100),
        ])->assertCreated()->assertJsonCount(2, 'items')->assertJsonCount(0, 'errors');

        $item = MediaItem::where('original_name', 'واجهة المتجر.jpg')->firstOrFail();
        $this->assertSame([640, 480], [$item->width, $item->height]);
        $this->assertSame('image/jpeg', $item->mime_type);
        $this->assertGreaterThan(0, $item->size);
        $this->assertSame($this->admin->id, $item->uploaded_by);
        $this->assertStringStartsWith('uploads/library/', $item->path);
        Storage::disk('public')->assertExists($item->path);
        $res->assertJsonPath('items.0.width', 640)->assertJsonPath('items.0.path', $item->path);
    }

    public function test_invalid_files_are_rejected_per_file(): void
    {
        $this->upload([
            UploadedFile::fake()->image('ok.webp'),
            UploadedFile::fake()->create('evil.php', 1, 'application/x-php'),
            UploadedFile::fake()->create('vector.svg', 1, 'image/svg+xml'),
            UploadedFile::fake()->image('huge.jpg')->size(7000),
        ])->assertCreated()->assertJsonCount(1, 'items')->assertJsonCount(3, 'errors');

        $this->assertSame(1, MediaItem::count());
        $this->upload([UploadedFile::fake()->create('evil.php', 1)])->assertStatus(422);
    }

    public function test_identical_image_is_not_stored_twice(): void
    {
        $file = UploadedFile::fake()->image('same.png', 50, 50);
        $first = $this->upload([$file])->json('items.0.id');
        $second = $this->upload([new UploadedFile($file->getRealPath(), 'copy.png', 'image/png', null, true)])->json('items.0.id');

        $this->assertSame($first, $second);
        $this->assertSame(1, MediaItem::count());
        $this->assertCount(1, Storage::disk('public')->allFiles('uploads/library'));
    }

    public function test_search_and_pagination_json(): void
    {
        $this->upload([UploadedFile::fake()->image('strawberry.jpg', 10, 10), UploadedFile::fake()->image('mango.jpg', 20, 20)]);

        $this->actingAs($this->admin)->getJson('/admin/media?q=straw')
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.name', 'strawberry.jpg')->assertJsonPath('next_page', null);
        $this->actingAs($this->admin)->getJson('/admin/media')->assertJsonPath('total', 2);
    }

    public function test_selected_library_image_is_reused_without_copying(): void
    {
        $path = $this->upload([UploadedFile::fake()->image('bear.jpg')])->json('items.0.path');

        foreach (['أ', 'ب'] as $title) {
            $this->actingAs($this->admin)->post('/admin/products', ['title' => $title, 'image' => $path, 'is_published' => 1])->assertRedirect();
        }
        $this->assertSame(2, Product::where('image', $path)->count());
        $this->assertCount(1, Storage::disk('public')->allFiles('uploads'));

        // Page content and settings use the same path value.
        $this->actingAs($this->admin)->put('/admin/content/home', ['data' => ['hero_image' => $path]])->assertRedirect();
        $this->assertSame($path, Content::get('home', 'hero_image'));

        $item = MediaItem::firstOrFail();
        $usages = $this->actingAs($this->admin)->getJson("/admin/media/{$item->id}")->assertOk()->json('usages');
        $this->assertCount(3, $usages);
    }

    public function test_alt_text_update_and_delete_removes_file(): void
    {
        $item = MediaItem::findOrFail($this->upload([UploadedFile::fake()->image('x.png')])->json('items.0.id'));

        $this->actingAs($this->admin)->patchJson("/admin/media/{$item->id}", ['alt' => 'دب بيري'])->assertOk()->assertJsonPath('alt', 'دب بيري');
        $this->actingAs($this->admin)->deleteJson("/admin/media/{$item->id}")->assertOk();

        $this->assertModelMissing($item);
        Storage::disk('public')->assertMissing($item->path);
    }

    public function test_legacy_form_uploads_land_in_the_library(): void
    {
        $this->actingAs($this->admin)->post('/admin/products', ['title' => 'ج', 'image_file' => UploadedFile::fake()->image('p.jpg')])->assertRedirect();

        $this->assertSame(Product::firstOrFail()->image, MediaItem::firstOrFail()->path);
    }

    public function test_sync_command_registers_existing_images(): void
    {
        Storage::disk('public')->put('uploads/wp/2025/01/a.png', UploadedFile::fake()->image('a.png', 30, 20)->getContent());
        Storage::disk('public')->put('uploads/wp/2025/01/notes.txt', 'hello');

        $this->artisan('berry:media-sync')->assertSuccessful();
        $this->artisan('berry:media-sync')->assertSuccessful(); // idempotent

        $this->assertSame(1, MediaItem::count());
        $this->assertSame(30, MediaItem::first()->width);
    }

    public function test_editor_can_use_library(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        $this->actingAs($editor)->getJson('/admin/media')->assertOk();
    }
}
