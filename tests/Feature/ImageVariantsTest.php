<?php

namespace Tests\Feature;

use App\Models\MediaItem;
use App\Support\ImageVariants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageVariantsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        if (! ImageVariants::supported()) {
            $this->markTestSkipped('GD WebP not available');
        }
    }

    public function test_srcset_builds_smaller_webp_copies_and_purges_them(): void
    {
        $path = UploadedFile::fake()->image('big.jpg', 1400, 1000)->storeAs('uploads/library/2026/11', 'big.jpg', 'public');

        $attrs = (string) img_srcset(Storage::disk('public')->url($path), '340px');

        $this->assertStringContainsString('sizes="340px"', $attrs);
        foreach ([160, 320, 640, 960] as $w) {
            $this->assertStringContainsString(' '.$w.'w', $attrs);
            Storage::disk('public')->assertExists(ImageVariants::variantPath($path, $w));
        }
        $this->assertStringContainsString('big.jpg 1400w', $attrs);
        Storage::disk('public')->assertMissing(ImageVariants::variantPath($path, 1280)); // too close to the original, not worth a copy

        [$vw] = getimagesize(Storage::disk('public')->path(ImageVariants::variantPath($path, 640)));
        $this->assertSame(640, $vw);

        $media = MediaItem::create(['original_name' => 'big.jpg', 'filename' => 'big.jpg', 'path' => $path, 'mime_type' => 'image/jpeg', 'size' => 1]);
        $media->delete();
        Storage::disk('public')->assertMissing(ImageVariants::variantPath($path, 640));
    }

    public function test_theme_assets_and_small_or_animated_images_get_no_srcset(): void
    {
        $this->assertSame('', (string) img_srcset(theme_img('logo.png'), '100px'));

        $small = UploadedFile::fake()->image('s.png', 150, 150)->storeAs('uploads/library', 's.png', 'public');
        $this->assertSame('', (string) img_srcset($small, '100px'));

        $gif = UploadedFile::fake()->image('a.gif', 900, 900)->storeAs('uploads/library', 'a.gif', 'public');
        $this->assertSame('', (string) img_srcset($gif, '100px'));

        $this->assertSame('', (string) img_srcset('uploads/../../.env', '100px'));
    }

    public function test_public_pages_render_with_srcset(): void
    {
        $this->seed();
        $this->get('/')->assertOk();
        $this->get('/products')->assertOk();
        $this->get('/blog')->assertOk();
    }
}
