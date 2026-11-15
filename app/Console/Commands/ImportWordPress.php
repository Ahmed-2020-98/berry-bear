<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Testimonial;
use App\Support\Content;
use App\Support\ContentSchema;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * One-time import of the Berry Bear WordPress site (theme "berry-bear" + ACF).
 * Safe to re-run: records are upserted by their WordPress ID.
 */
class ImportWordPress extends Command
{
    protected $signature = 'berry:import-wp
        {--fresh : Delete existing catalog/blog/FAQ/testimonial/menu rows first}
        {--no-media : Skip copying files from wp-content/uploads}';

    protected $description = 'Import products, posts, pages, FAQs, testimonials, menu, page content, settings and media from WordPress';

    protected Connection $wp;

    protected string $px;

    protected string $siteUrl;

    protected string $uploads;

    /** @var array<int,string> attachment ID => stored path */
    protected array $attachments = [];

    protected array $stats = [];

    /** Pages that are rendered by dedicated templates (content lives in page_contents). */
    protected const TEMPLATE_PAGES = ['home', 'about', 'products', 'faqs', 'contact', 'blog'];

    public function handle(): int
    {
        $this->wp = DB::connection('wordpress');
        $this->px = env('WP_TABLE_PREFIX', 'wp_');
        $this->siteUrl = rtrim((string) env('WP_SITE_URL', ''), '/');
        $this->uploads = $this->resolvePath((string) env('WP_UPLOADS_PATH', '../app/public/wp-content/uploads'));

        try {
            $this->wp->getPdo();
        } catch (\Throwable $e) {
            $this->error('Cannot connect to the WordPress database: '.$e->getMessage());
            $this->line('Start the site in Local, then set WP_DB_* (or WP_DB_SOCKET) in .env.');

            return self::FAILURE;
        }

        if (! $this->siteUrl) {
            $this->siteUrl = rtrim((string) $this->option_('siteurl'), '/');
        }

        if ($this->option('fresh')) {
            DB::transaction(function () {
                foreach ([Product::class, Collection::class, Post::class, Faq::class, Testimonial::class, Page::class] as $m) {
                    $m::query()->delete();
                }
                MenuItem::query()->delete();
            });
            $this->warn('Existing content cleared.');
        } else {
            // Replace the theme's demo cards (no wp_id) so the real data takes over.
            foreach ([Product::class, Post::class, Faq::class, Testimonial::class] as $m) {
                $m::whereNull('wp_id')->delete();
            }
        }

        DB::transaction(function () {
            $this->importCollections();
            $this->importProducts();
            $this->importPosts();
            $this->importFaqs();
            $this->importTestimonials();
            $this->importPages();
            $this->importPageContent();
            $this->importSettings();
            $this->importMenu();
            $this->importMessages();
        });

        foreach (['home', 'about', 'products', 'faqs', 'contact', 'blog', 'settings'] as $k) {
            Content::forget($k);
        }

        $this->newLine();
        $this->table(['Item', 'Imported'], collect($this->stats)->map(fn ($v, $k) => [$k, $v])->values()->all());
        $this->info('Done. Media copied: '.count(array_filter($this->attachments)));

        return self::SUCCESS;
    }

    /* ------------------------------------------------------------------ */

    protected function importCollections(): void
    {
        $terms = $this->terms('product_collection');
        foreach ($terms as $i => $t) {
            Collection::updateOrCreate(['wp_id' => $t->term_id], [
                'name' => $t->name,
                'slug' => $this->uniqueSlug(Collection::class, urldecode($t->slug), 'wp_id', $t->term_id),
                'description' => $t->description ?: null,
                'sort' => $i + 1,
            ]);
        }
        $this->stats['Collections'] = count($terms);
    }

    protected function importProducts(): void
    {
        $rows = $this->posts('product', ['publish', 'draft', 'private']);
        foreach ($rows as $p) {
            $meta = $this->meta($p->ID);
            $termId = $this->wp->table($this->px.'term_relationships as tr')
                ->join($this->px.'term_taxonomy as tt', 'tt.term_taxonomy_id', '=', 'tr.term_taxonomy_id')
                ->where('tr.object_id', $p->ID)->where('tt.taxonomy', 'product_collection')->value('tt.term_id');
            $title = $p->post_title ?: 'منتج بدون اسم';

            Product::updateOrCreate(['wp_id' => $p->ID], [
                'title' => $title,
                'slug' => $this->uniqueSlug(Product::class, urldecode($p->post_name) ?: $title, 'wp_id', $p->ID),
                'collection_id' => $termId ? Collection::where('wp_id', $termId)->value('id') : null,
                'excerpt' => $this->excerpt($p),
                'body' => $this->html($p->post_content),
                'image' => $this->attachment($meta['_thumbnail_id'] ?? null),
                'carton_image' => $this->attachment($meta['carton_image'] ?? null),
                'flavor_label' => $meta['flavor_label'] ?? null,
                'flavor_color' => $meta['flavor_color'] ?? null,
                'anchor_id' => $meta['anchor_id'] ?? null,
                'sort' => (int) $p->menu_order,
                'is_published' => $p->post_status === 'publish',
            ]);
        }
        $this->stats['Products'] = count($rows);
    }

    protected function importPosts(): void
    {
        $rows = $this->posts('post', ['publish', 'draft', 'future', 'private']);
        foreach ($rows as $p) {
            $meta = $this->meta($p->ID);
            $cat = $this->wp->table($this->px.'term_relationships as tr')
                ->join($this->px.'term_taxonomy as tt', 'tt.term_taxonomy_id', '=', 'tr.term_taxonomy_id')
                ->join($this->px.'terms as t', 't.term_id', '=', 'tt.term_id')
                ->where('tr.object_id', $p->ID)->where('tt.taxonomy', 'category')
                ->where('t.slug', '!=', 'uncategorized')->value('t.name');
            $slug = urldecode($p->post_name) ?: $p->post_title;
            if (in_array($slug, reserved_slugs(), true)) {
                $slug .= '-post';
            }

            Post::updateOrCreate(['wp_id' => $p->ID], [
                'title' => $p->post_title,
                'slug' => $this->uniqueSlug(Post::class, $slug, 'wp_id', $p->ID),
                'category' => $cat,
                'excerpt' => $p->post_excerpt ?: null,
                'body' => $this->html($p->post_content),
                'cover' => $this->attachment($meta['_thumbnail_id'] ?? null),
                'reading_time' => ! empty($meta['reading_time']) ? (int) $meta['reading_time'] : null,
                'status' => in_array($p->post_status, ['publish', 'future'], true) ? 'published' : 'draft',
                'published_at' => $p->post_date,
            ]);
        }
        $this->stats['Blog posts'] = count($rows);
    }

    protected function importFaqs(): void
    {
        $rows = $this->posts('faq', ['publish', 'draft', 'private']);
        foreach ($rows as $p) {
            Faq::updateOrCreate(['wp_id' => $p->ID], [
                'question' => $p->post_title,
                'answer' => $this->html($p->post_content),
                'sort' => (int) $p->menu_order,
                'is_published' => $p->post_status === 'publish',
            ]);
        }
        $this->stats['FAQs'] = count($rows);
    }

    protected function importTestimonials(): void
    {
        $rows = $this->posts('testimonial', ['publish', 'draft', 'private']);
        foreach ($rows as $p) {
            $meta = $this->meta($p->ID);
            Testimonial::updateOrCreate(['wp_id' => $p->ID], [
                'name' => $p->post_title,
                'text' => trim(strip_tags($p->post_content)),
                'role' => $meta['role'] ?? null,
                'avatar_color' => $meta['avatar_color'] ?? null,
                'rating' => max(1, min(5, (int) ($meta['rating'] ?? 5))),
                'sort' => (int) $p->menu_order,
                'is_published' => $p->post_status === 'publish',
            ]);
        }
        $this->stats['Testimonials'] = count($rows);
    }

    /** Free-form pages (e.g. privacy policy). Template pages are skipped. */
    protected function importPages(): void
    {
        $count = 0;
        foreach ($this->posts('page', ['publish', 'draft', 'private']) as $p) {
            $slug = urldecode($p->post_name);
            if (in_array($slug, self::TEMPLATE_PAGES, true) || $this->templateKey($p->ID)) {
                continue;
            }
            if (in_array($slug, reserved_slugs(), true)) {
                $slug .= '-page';
            }
            Page::updateOrCreate(['wp_id' => $p->ID], [
                'title' => $p->post_title,
                'slug' => $this->uniqueSlug(Page::class, $slug ?: $p->post_title, 'wp_id', $p->ID),
                'body' => $this->html($p->post_content),
                'is_published' => $p->post_status === 'publish',
            ]);
            $count++;
        }
        $this->stats['Extra pages'] = $count;
    }

    /** ACF fields on the template pages → page_contents. */
    protected function importPageContent(): void
    {
        $ids = [
            'home' => (int) $this->option_('page_on_front'),
            'blog' => (int) $this->option_('page_for_posts'),
        ];
        foreach ($this->posts('page', ['publish', 'draft', 'private']) as $p) {
            if ($key = $this->templateKey($p->ID) ?? (in_array(urldecode($p->post_name), self::TEMPLATE_PAGES, true) ? urldecode($p->post_name) : null)) {
                $ids[$key] ??= $p->ID;
            }
        }

        $fieldsTotal = 0;
        foreach ($ids as $key => $pageId) {
            if (! $pageId || ! ContentSchema::has($key)) {
                continue;
            }
            $meta = $this->meta($pageId);
            $data = $this->mapFields(ContentSchema::fields($key), fn ($name) => $meta[$name] ?? null, $meta);
            // About reads its vision cards from the home page in the theme; keep stored extras.
            if ($data) {
                Content::merge($key, $data);
                $fieldsTotal += count($data);
            }
        }
        $this->stats['Page content fields'] = $fieldsTotal;
    }

    /** ACF options page → settings (+ logo / site name from WP core). */
    protected function importSettings(): void
    {
        $opts = $this->wp->table($this->px.'options')->where('option_name', 'like', 'options\_%')
            ->pluck('option_value', 'option_name')
            ->mapWithKeys(fn ($v, $k) => [substr($k, 8) => $v])->all();

        $data = $this->mapFields(ContentSchema::fields('settings'), fn ($name) => $opts[$name] ?? null, $opts);

        $mods = $this->unserialize($this->option_('theme_mods_'.$this->option_('stylesheet')));
        if (is_array($mods) && ! empty($mods['custom_logo'])) {
            $data['logo'] = $this->attachment($mods['custom_logo']);
        }
        if ($name = $this->option_('blogname')) {
            $data['site_name'] = $name;
        }

        Content::merge('settings', array_filter($data, fn ($v) => ! Content::isEmpty($v)));
        $this->stats['Settings fields'] = count($data);
    }

    protected function importMenu(): void
    {
        $mods = $this->unserialize($this->option_('theme_mods_'.$this->option_('stylesheet')));
        $menuId = is_array($mods) ? ($mods['nav_menu_locations']['primary'] ?? null) : null;
        if (! $menuId) {
            $this->stats['Menu items'] = 0;

            return;
        }

        $items = $this->wp->table($this->px.'posts as p')
            ->join($this->px.'term_relationships as tr', 'tr.object_id', '=', 'p.ID')
            ->join($this->px.'term_taxonomy as tt', 'tt.term_taxonomy_id', '=', 'tr.term_taxonomy_id')
            ->where('tt.term_id', $menuId)->where('p.post_type', 'nav_menu_item')->where('p.post_status', 'publish')
            ->orderBy('p.menu_order')->select('p.*')->get();

        MenuItem::query()->delete();
        $map = [];
        $pending = [];
        foreach ($items as $it) {
            $m = $this->meta($it->ID);
            $type = $m['_menu_item_type'] ?? 'custom';
            $objectId = (int) ($m['_menu_item_object_id'] ?? 0);
            $label = $it->post_title;
            $url = $m['_menu_item_url'] ?? '';

            if ($type === 'post_type' && $objectId) {
                $obj = $this->wp->table($this->px.'posts')->where('ID', $objectId)->first();
                $label = $label ?: ($obj->post_title ?? '');
                $url = $this->objectUrl($obj);
            } elseif ($type === 'taxonomy' && $objectId) {
                $term = $this->wp->table($this->px.'terms')->where('term_id', $objectId)->first();
                $label = $label ?: ($term->name ?? '');
                $url = $term ? ($m['_menu_item_object'] === 'product_collection' ? '/collection/' : '/category/').urldecode($term->slug) : '#';
            }

            $color = trim(strip_tags((string) $it->post_content));
            $item = MenuItem::create([
                'label' => $label ?: 'رابط',
                'url' => ($url === '' || $url === '#') && $this->hasChildren($items, $it->ID) ? null : $this->localUrl($url),
                'color' => preg_match('/^#?[0-9a-f]{6}$/i', $color) ? '#'.ltrim($color, '#') : null,
                'open_new_tab' => ($m['_menu_item_target'] ?? '') === '_blank',
                'sort' => (int) $it->menu_order,
            ]);
            $map[$it->ID] = $item->id;
            if (! empty($m['_menu_item_menu_item_parent'])) {
                $pending[$item->id] = (int) $m['_menu_item_menu_item_parent'];
            }
        }
        foreach ($pending as $id => $wpParent) {
            if (isset($map[$wpParent])) {
                MenuItem::whereKey($id)->update(['parent_id' => $map[$wpParent]]);
            }
        }
        $this->stats['Menu items'] = count($items);
    }

    /** Flamingo inbound messages (CF7 submissions). */
    protected function importMessages(): void
    {
        $rows = $this->posts('flamingo_inbound', ['publish', 'flamingo-spam']);
        $n = 0;
        foreach ($rows as $p) {
            if ($p->post_status !== 'publish') {
                continue;
            }
            $m = $this->meta($p->ID);
            $subject = $this->unserialize($m['_field_your-subject'] ?? '');
            $subject = is_array($subject) ? ($subject[0] ?? null) : ($subject ?: null);
            $email = $m['_field_your-email'] ?? $m['_from_email'] ?? '';
            if (ContactMessage::where('email', $email)->where('created_at', $p->post_date)->exists()) {
                continue;
            }
            $msg = new ContactMessage([
                'name' => $m['_field_your-name'] ?? $m['_from_name'] ?? '—',
                'phone' => $m['_field_your-phone'] ?? null,
                'email' => $email,
                'subject' => array_key_exists((string) $subject, ContactMessage::SUBJECTS) ? $subject : 'other',
                'message' => $m['_field_your-message'] ?? strip_tags($p->post_content),
                'read_at' => $p->post_date,
            ]);
            $msg->created_at = $p->post_date;
            $msg->save();
            $n++;
        }
        $this->stats['Contact messages'] = $n;
    }

    /* ------------------------------------------------------------------ */

    /**
     * Map schema fields from a WP meta source. Repeaters may be stored either
     * as a serialized array (the theme seeder) or ACF's row format
     * (name = count, name_{i}_{sub}).
     */
    protected function mapFields(array $fields, callable $get, array $all): array
    {
        $out = [];
        foreach ($fields as $f) {
            $raw = $get($f['name']);
            if ($f['type'] === 'repeater') {
                $rows = $this->unserialize($raw);
                if (! is_array($rows) && is_numeric($raw)) {
                    $rows = [];
                    for ($i = 0; $i < (int) $raw; $i++) {
                        $row = [];
                        foreach ($f['fields'] as $sub) {
                            $row[$sub['name']] = $all["{$f['name']}_{$i}_{$sub['name']}"] ?? '';
                        }
                        $rows[] = $row;
                    }
                }
                if (! is_array($rows)) {
                    continue;
                }
                $clean = [];
                foreach ($rows as $row) {
                    $row = (array) $row;
                    foreach ($f['fields'] as $sub) {
                        $row[$sub['name']] = $this->convert($sub, $row[$sub['name']] ?? '');
                    }
                    $clean[] = $row;
                }
                $out[$f['name']] = $clean;
            } elseif ($raw !== null) {
                $out[$f['name']] = $this->convert($f, $raw);
            }
        }

        return $out;
    }

    protected function convert(array $f, mixed $v): mixed
    {
        return match ($f['type']) {
            'image', 'file' => $this->attachment($v),
            'url' => $this->localUrl((string) $v),
            'toggle' => in_array($v, ['1', 1, true], true) ? '1' : '0',
            default => is_scalar($v) ? (string) $v : '',
        };
    }

    /** Attachment ID / URL → stored path (copies the file into storage). */
    protected function attachment(mixed $val): ?string
    {
        if (is_array($val)) {
            $val = $val['ID'] ?? $val['id'] ?? $val['url'] ?? null;
        }
        if ($val === null || $val === '' || $val === '0' || $val === 0) {
            return null;
        }
        if (is_numeric($val)) {
            $id = (int) $val;
            if (! array_key_exists($id, $this->attachments)) {
                $file = $this->wp->table($this->px.'postmeta')->where('post_id', $id)->where('meta_key', '_wp_attached_file')->value('meta_value');
                $this->attachments[$id] = $file ? $this->copyUpload($file) : null;
            }

            return $this->attachments[$id];
        }
        $val = (string) $val;
        if (preg_match('#/wp-content/uploads/(.+)$#', $val, $m)) {
            return $this->copyUpload($m[1]) ?? $val;
        }
        if (preg_match('#/wp-content/themes/[^/]+/assets/img/(.+)$#', $val, $m)) {
            return $m[1]; // theme asset, served from public/theme/img
        }

        return $val;
    }

    protected function copyUpload(string $relative): ?string
    {
        $relative = ltrim(rawurldecode($relative), '/');
        $target = 'uploads/wp/'.$relative;
        if ($this->option('no-media')) {
            return $target;
        }
        $source = $this->uploads.'/'.$relative;
        if (! is_file($source)) {
            $this->warn("Missing upload: $relative");

            return null;
        }
        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, File::get($source));
        }
        \App\Support\Media::register($target); // make it reusable from the media library

        return $target;
    }

    /** Clean post HTML: drop block comments, autop, rewrite upload URLs. */
    protected function html(?string $html): string
    {
        $html = preg_replace('/<!--\s*\/?wp:.*?-->/s', '', (string) $html);
        $html = trim($html);
        if ($html !== '' && ! preg_match('/<(p|div|h[1-6]|ul|ol|figure|table|blockquote)[\s>]/i', $html)) {
            $html = collect(preg_split('/\n\s*\n/', $html))->map(fn ($p) => '<p>'.nl2br(trim($p), false).'</p>')->implode("\n");
        }

        return preg_replace_callback('#(?:https?:)?//[^"\'\s]+?/wp-content/uploads/([^"\'\s)]+)#', function ($m) {
            $path = $this->copyUpload($m[1]);

            return $path ? '/storage/'.$path : $m[0];
        }, $html);
    }

    protected function excerpt(object $p): ?string
    {
        if ($p->post_excerpt) {
            return $p->post_excerpt;
        }
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($this->html($p->post_content))));

        return $text === '' ? null : Str::words($text, 55, ' […]');
    }

    /** WP absolute link → site-relative ("/products"), external links untouched. */
    protected function localUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || $url === '#') {
            return $url;
        }
        foreach (array_filter([$this->siteUrl, preg_replace('#^https?:#', '', $this->siteUrl)]) as $base) {
            if (str_starts_with($url, $base)) {
                $url = substr($url, strlen($base)) ?: '/';
                break;
            }
        }
        if (str_starts_with($url, '/')) {
            [$path, $frag] = array_pad(explode('#', $url, 2), 2, null);
            $path = rtrim(rawurldecode($path), '/') ?: '/';

            return $path.($frag !== null ? '#'.$frag : '');
        }

        return $url;
    }

    protected function objectUrl(?object $obj): string
    {
        if (! $obj) {
            return '#';
        }
        $slug = urldecode($obj->post_name);
        if ($obj->post_type === 'page') {
            return (int) $obj->ID === (int) $this->option_('page_on_front') ? '/' : '/'.$slug;
        }

        return match ($obj->post_type) {
            'product' => '/product/'.$slug,
            default => '/'.$slug,
        };
    }

    protected function hasChildren($items, int $id): bool
    {
        foreach ($items as $it) {
            if ((int) $this->wp->table($this->px.'postmeta')->where('post_id', $it->ID)->where('meta_key', '_menu_item_menu_item_parent')->value('meta_value') === $id) {
                return true;
            }
        }

        return false;
    }

    protected function templateKey(int $pageId): ?string
    {
        $tpl = $this->wp->table($this->px.'postmeta')->where('post_id', $pageId)->where('meta_key', '_wp_page_template')->value('meta_value');

        return [
            'page-about.php' => 'about', 'page-products.php' => 'products',
            'page-faqs.php' => 'faqs', 'page-contact.php' => 'contact',
        ][$tpl] ?? null;
    }

    protected function posts(string $type, array $statuses)
    {
        return $this->wp->table($this->px.'posts')->where('post_type', $type)->whereIn('post_status', $statuses)
            ->orderBy('menu_order')->orderBy('post_date')->get();
    }

    /** @return array<string,string> */
    protected function meta(int $postId): array
    {
        return $this->wp->table($this->px.'postmeta')->where('post_id', $postId)->pluck('meta_value', 'meta_key')->all();
    }

    protected function terms(string $taxonomy)
    {
        return $this->wp->table($this->px.'terms as t')
            ->join($this->px.'term_taxonomy as tt', 'tt.term_id', '=', 't.term_id')
            ->where('tt.taxonomy', $taxonomy)->orderBy('t.name')
            ->select('t.term_id', 't.name', 't.slug', 'tt.description')->get();
    }

    protected function option_(string $name): ?string
    {
        return $this->wp->table($this->px.'options')->where('option_name', $name)->value('option_value');
    }

    protected function unserialize(mixed $v): mixed
    {
        if (! is_string($v) || ! preg_match('/^(a|O|s|i|b):/', $v)) {
            return $v;
        }
        $r = @unserialize($v, ['allowed_classes' => false]);

        return $r === false && $v !== 'b:0;' ? $v : $r;
    }

    protected function uniqueSlug(string $model, string $slug, string $key, int $wpId): string
    {
        $base = trim(preg_replace('/[\s\/]+/u', '-', mb_strtolower(trim($slug))), '-') ?: 'item';
        $candidate = $base;
        $i = 2;
        while ($model::where('slug', $candidate)->where(fn ($q) => $q->whereNull($key)->orWhere($key, '!=', $wpId))->exists()) {
            $candidate = $base.'-'.$i++;
        }

        return $candidate;
    }

    protected function resolvePath(string $path): string
    {
        return str_starts_with($path, '/') ? rtrim($path, '/') : rtrim(realpath(base_path($path)) ?: base_path($path), '/');
    }
}
