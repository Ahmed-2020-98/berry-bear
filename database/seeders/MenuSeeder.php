<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/** The theme's fallback header menu (berry_primary_menu_fallback). */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        if (MenuItem::exists()) {
            return;
        }

        $items = [
            ['label' => 'الرئيسية', 'url' => '/'],
            ['label' => 'من نحن', 'url' => '/about'],
            ['label' => 'منتجاتنا', 'url' => null, 'children' => [
                ['label' => 'كل التشكيلات', 'url' => '/products', 'color' => '#E51B6E'],
                ['label' => 'دببة الجيلي الكلاسيكية', 'url' => '/products#bears', 'color' => '#E51B6E'],
                ['label' => 'سَور جامي الحامض', 'url' => '/products#sour', 'color' => '#9B63B5'],
                ['label' => 'دببة الكولا', 'url' => '/products#cola', 'color' => '#C0184A'],
                ['label' => 'ديدان الجيلي', 'url' => '/products#worms', 'color' => '#1E8A44'],
                ['label' => 'دببة التوت الأزرق', 'url' => '/products#rings', 'color' => '#FF8A3D'],
            ]],
            ['label' => 'المنتجات والخدمات', 'url' => '/products'],
            ['label' => 'المدونة', 'url' => '/blog'],
            ['label' => 'الأسئلة الشائعة', 'url' => '/faqs'],
            ['label' => 'تواصل معنا', 'url' => '/contact'],
        ];

        foreach ($items as $i => $item) {
            $parent = MenuItem::create(['label' => $item['label'], 'url' => $item['url'], 'sort' => $i + 1]);
            foreach ($item['children'] ?? [] as $j => $child) {
                MenuItem::create($child + ['parent_id' => $parent->id, 'sort' => $j + 1]);
            }
        }
        Cache::forget('site.menu');
    }
}
