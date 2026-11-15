<?php

/*
|--------------------------------------------------------------------------
| Editable content schema
|--------------------------------------------------------------------------
| Port of the theme's ACF field groups (inc/acf-fields.php). Each field is
| [name, label, type = 'text', extra = []]. Types: text, textarea, html,
| url, image, file, color, select, toggle, number, repeater (extra.fields).
| The admin forms, validation and front-end readers are all driven by this.
*/

$link = [['label', 'النص'], ['url', 'الرابط', 'url']];

return [

    'settings' => [
        'title' => 'إعدادات الموقع',
        'tabs' => [
            'عام' => [
                ['site_name', 'اسم الموقع (يظهر في عنوان المتصفح)'],
                ['logo', 'الشعار', 'image', ['help' => 'اتركه فارغًا لاستخدام الشعار الافتراضي.']],
                ['wholesale_label', 'زر الطلب بالجملة (النص)'],
                ['wholesale_url', 'زر الطلب بالجملة (الرابط)', 'url'],
            ],
            'شاشة التحميل' => [
                ['preloader_media_type', 'نوع شاشة التحميل', 'select', ['options' => ['logo' => 'الشعار الافتراضي', 'image' => 'صورة أو GIF', 'video' => 'فيديو'], 'help' => 'اختر ما يظهر أثناء تحميل الموقع.']],
                ['preloader_image', 'صورة أو GIF (عند اختيار «صورة أو GIF»)', 'image'],
                ['preloader_video', 'فيديو (عند اختيار «فيديو»)', 'file', ['accept' => 'mp4,webm,ogv,ogg', 'help' => 'يُفضّل MP4/WebM قصير وخفيف — يُشغّل تلقائيًا وبصمت ومكرّرًا.']],
                ['preloader_show_bar', 'إظهار شريط التحميل', 'toggle'],
                ['preloader_text', 'نص شاشة التحميل', 'text', ['help' => 'اتركه فارغًا لإخفاء النص.']],
            ],
            'الفوتر' => [
                ['footer_about', 'نبذة الفوتر', 'textarea'],
                ['footer_quick_title', 'عنوان عمود الروابط السريعة'],
                ['footer_quick_links', 'الروابط السريعة', 'repeater', ['fields' => $link, 'button' => 'إضافة رابط']],
                ['footer_support_title', 'عنوان عمود الدعم'],
                ['footer_support_links', 'روابط الدعم', 'repeater', ['fields' => $link, 'button' => 'إضافة رابط']],
                ['footer_thanks', 'عبارة الشكر (أعلى سطر الحقوق)'],
                ['footer_copyright', 'سطر الحقوق'],
            ],
            'التواصل والسوشال' => [
                ['footer_contact_title', 'عنوان عمود التواصل'],
                ['contact_address', 'العنوان'],
                ['contact_email', 'البريد الإلكتروني (يظهر في الموقع)'],
                ['notify_email', 'بريد استلام رسائل النموذج', 'text', ['help' => 'تُرسل إليه إشعارات رسائل نموذج التواصل. اتركه فارغًا لاستخدام البريد أعلاه.']],
                ['contact_phone', 'الهاتف'],
                ['contact_hours', 'ساعات العمل (مختصر)'],
                ['contact_hours_long', 'ساعات العمل (تفصيلي)'],
                ['social_links', 'روابط التواصل الاجتماعي', 'repeater', ['button' => 'إضافة منصة', 'fields' => [
                    ['platform', 'المنصة', 'select', ['options' => 'social']],
                    ['url', 'الرابط', 'url'],
                ]]],
            ],
        ],
    ],

    'pages' => [

        'home' => [
            'title' => 'الصفحة الرئيسية',
            'tabs' => [
                'البانر' => [
                    ['hero_badge', 'الشارة'],
                    ['hero_title', 'العنوان', 'html'],
                    ['hero_lead', 'النص التمهيدي', 'textarea'],
                    ['hero_cta1_label', 'زر 1 - النص'],
                    ['hero_cta1_url', 'زر 1 - الرابط', 'url'],
                    ['hero_cta2_label', 'زر 2 - النص'],
                    ['hero_cta2_url', 'زر 2 - الرابط', 'url'],
                    ['hero_tags', 'وسوم البانر', 'repeater', ['fields' => [['tag', 'الوسم']], 'button' => 'إضافة وسم']],
                    ['hero_image', 'صورة البانر', 'image'],
                ],
                'قسم من نحن' => [
                    ['about_image', 'الصورة', 'image'],
                    ['about_sticker_num', 'الملصق - الرقم'],
                    ['about_sticker_text', 'الملصق - النص'],
                    ['about_eyebrow', 'العنوان الصغير'],
                    ['about_title', 'العنوان', 'html'],
                    ['about_text', 'النص', 'textarea'],
                    ['about_points', 'النقاط', 'repeater', ['fields' => [['point', 'النقطة']], 'button' => 'إضافة نقطة']],
                    ['about_btn_label', 'الزر - النص'],
                    ['about_btn_url', 'الزر - الرابط', 'url'],
                ],
                'الرؤية والرسالة والقيم' => [
                    ['vision_eyebrow', 'العنوان الصغير'],
                    ['vision_title', 'العنوان', 'html'],
                    ['vision_sub', 'النص الفرعي', 'textarea'],
                    ['vision_cards', 'البطاقات', 'repeater', ['button' => 'إضافة بطاقة', 'fields' => [
                        ['icon', 'الأيقونة (إيموجي)'], ['title', 'العنوان'], ['text', 'النص', 'textarea'], ['tag', 'الوسم'],
                    ]]],
                ],
                'خطوات التصنيع' => [
                    ['process_eyebrow', 'العنوان الصغير'],
                    ['process_title', 'العنوان', 'html'],
                    ['process_sub', 'النص الفرعي', 'textarea'],
                    ['process_steps', 'الخطوات', 'repeater', ['button' => 'إضافة خطوة', 'fields' => [
                        ['color', 'لون الدبدوب', 'color'], ['e1', 'إيموجي 1'], ['e2', 'إيموجي 2'], ['icon', 'الأيقونة'],
                        ['title', 'العنوان'], ['text', 'النص', 'textarea'],
                        ['list', 'قائمة (سطر لكل عنصر، يدعم <b>)', 'textarea'],
                    ]]],
                ],
                'رؤوس الأقسام' => [
                    ['products_eyebrow', 'المنتجات - عنوان صغير'],
                    ['products_title', 'المنتجات - العنوان', 'html'],
                    ['products_sub', 'المنتجات - النص'],
                    ['marquee_eyebrow', 'الشريط - عنوان صغير'],
                    ['marquee_title', 'الشريط - العنوان', 'html'],
                    ['gallery_eyebrow', 'المعرض - عنوان صغير'],
                    ['gallery_title', 'المعرض - العنوان', 'html'],
                    ['gallery_sub', 'المعرض - النص'],
                    ['why_eyebrow', 'لماذا نحن - عنوان صغير'],
                    ['why_title', 'لماذا نحن - العنوان', 'html'],
                    ['why_sub', 'لماذا نحن - النص'],
                    ['reviews_eyebrow', 'الآراء - عنوان صغير'],
                    ['reviews_title', 'الآراء - العنوان', 'html'],
                    ['blog_eyebrow', 'المدونة - عنوان صغير'],
                    ['blog_title', 'المدونة - العنوان', 'html'],
                    ['blog_sub', 'المدونة - النص'],
                ],
                'الشريط والمعرض والدعوة' => [
                    ['marquee_chips', 'عناصر الشريط', 'repeater', ['fields' => [['label', 'النص'], ['color', 'اللون', 'color']]]],
                    ['gallery_images', 'صور المعرض', 'repeater', ['button' => 'إضافة صورة', 'fields' => [
                        ['img', 'الصورة', 'image'], ['title', 'العنوان'], ['cap', 'الوصف'],
                    ]]],
                    ['feature_cards', 'بطاقات (لماذا نحن)', 'repeater', ['button' => 'إضافة بطاقة', 'fields' => [
                        ['icon', 'الأيقونة'], ['title', 'العنوان'], ['text', 'النص', 'textarea'],
                    ]]],
                    ['cta_title', 'الدعوة - العنوان'],
                    ['cta_text', 'الدعوة - النص', 'textarea'],
                    ['cta_btn_label', 'الدعوة - نص الزر'],
                    ['cta_btn_url', 'الدعوة - رابط الزر', 'url'],
                ],
                'أقسام تسويقية' => [
                    ['trust_badges', 'شارات الثقة', 'repeater', ['fields' => [['text', 'النص']], 'button' => 'إضافة شارة']],
                    ['slogans', 'الشعارات التسويقية (الأول = رئيسي)', 'repeater', ['fields' => [['text', 'الشعار']], 'button' => 'إضافة شعار']],
                    ['shop_buttons', 'أزرار سريعة قرب المنتجات', 'repeater', ['fields' => $link, 'button' => 'إضافة زر']],
                    ['trust_reasons_eyebrow', 'لماذا يثق بنا - عنوان صغير'],
                    ['trust_reasons_title', 'لماذا يثق بنا - العنوان', 'html'],
                    ['trust_reasons_sub', 'لماذا يثق بنا - النص'],
                    ['trust_reasons', 'أسباب ثقة العملاء', 'repeater', ['fields' => [['text', 'السبب']], 'button' => 'إضافة سبب']],
                ],
            ],
        ],

        'about' => [
            'title' => 'من نحن',
            'tabs' => [
                'البانر والقصة' => [
                    ['ab_hero_title', 'البانر - العنوان', 'html'],
                    ['ab_hero_sub', 'البانر - النص', 'textarea'],
                    ['ab_story_image', 'صورة القصة', 'image'],
                    ['ab_sticker_num', 'الملصق - الرقم'],
                    ['ab_sticker_text', 'الملصق - النص'],
                    ['ab_story_eyebrow', 'عنوان صغير'],
                    ['ab_story_title', 'العنوان', 'html'],
                    ['ab_story_p1', 'الفقرة 1', 'textarea'],
                    ['ab_story_p2', 'الفقرة 2', 'textarea'],
                    ['ab_points', 'النقاط', 'repeater', ['fields' => [['point', 'النقطة']], 'button' => 'إضافة نقطة']],
                    ['ab_btn_label', 'الزر - النص'],
                    ['ab_btn_url', 'الزر - الرابط', 'url'],
                ],
                'الإحصائيات والهوية' => [
                    ['ab_stats', 'الإحصائيات', 'repeater', ['button' => 'إضافة إحصائية', 'fields' => [
                        ['value', 'القيمة (رقم)', 'number'], ['suffix', 'اللاحقة'], ['label', 'الوصف'],
                    ]]],
                    ['ab_vmv_eyebrow', 'الهوية - عنوان صغير'],
                    ['ab_vmv_title', 'الهوية - العنوان', 'html'],
                    ['ab_vmv_sub', 'الهوية - النص', 'text', ['help' => 'بطاقات الرؤية والرسالة والقيم مشتركة مع الصفحة الرئيسية — عدّلها من «الصفحة الرئيسية».']],
                ],
                'وعودنا' => [
                    ['ab_promise_eyebrow', 'وعودنا - عنوان صغير'],
                    ['ab_promise_title', 'وعودنا - العنوان', 'html'],
                    ['ab_promise_sub', 'وعودنا - النص'],
                    ['ab_promise', 'بطاقات الوعود', 'repeater', ['button' => 'إضافة بطاقة', 'fields' => [
                        ['icon', 'الأيقونة'], ['title', 'العنوان'], ['text', 'النص', 'textarea'],
                    ]]],
                    ['ab_promise_statement', 'عبارة الوعد', 'textarea'],
                ],
                'كلمة المدير والدعوة' => [
                    ['manager_word_eyebrow', 'كلمة المدير - عنوان صغير'],
                    ['manager_word_text', 'كلمة المدير - النص', 'textarea'],
                    ['manager_name', 'الاسم / الجهة'],
                    ['manager_role', 'الصفة'],
                    ['ab_cta_title', 'الدعوة - العنوان'],
                    ['ab_cta_text', 'الدعوة - النص', 'textarea'],
                    ['ab_cta_btn_label', 'الدعوة - نص الزر'],
                    ['ab_cta_btn_url', 'الدعوة - رابط الزر', 'url'],
                ],
            ],
        ],

        'products' => [
            'title' => 'المنتجات والخدمات',
            'tabs' => [
                'البانر والمنتجات' => [
                    ['pr_hero_title', 'البانر - العنوان', 'html'],
                    ['pr_hero_sub', 'البانر - النص', 'textarea'],
                    ['pr_eyebrow', 'المنتجات - عنوان صغير'],
                    ['pr_title', 'المنتجات - العنوان', 'html'],
                    ['pr_sub', 'المنتجات - النص'],
                ],
                'الخدمات' => [
                    ['sv_eyebrow', 'الخدمات - عنوان صغير'],
                    ['sv_title', 'الخدمات - العنوان', 'html'],
                    ['sv_sub', 'الخدمات - النص'],
                    ['services', 'الخدمات', 'repeater', ['button' => 'إضافة خدمة', 'fields' => [
                        ['img', 'الصورة', 'image'], ['icon', 'الأيقونة'], ['title', 'العنوان'], ['text', 'النص', 'textarea'],
                        ['list', 'قائمة (سطر لكل عنصر، يدعم <b>)', 'textarea'],
                    ]]],
                ],
                'الدعوة' => [
                    ['pr_cta_title', 'الدعوة - العنوان'],
                    ['pr_cta_text', 'الدعوة - النص', 'textarea'],
                    ['pr_cta_btn_label', 'الدعوة - نص الزر'],
                    ['pr_cta_btn_url', 'الدعوة - رابط الزر', 'url'],
                ],
            ],
        ],

        'faqs' => [
            'title' => 'الأسئلة الشائعة',
            'tabs' => [
                'المحتوى' => [
                    ['faq_hero_title', 'البانر - العنوان', 'html'],
                    ['faq_hero_sub', 'البانر - النص', 'textarea'],
                    ['faq_cta_text', 'نص الدعوة السفلية'],
                    ['faq_cta_label', 'نص الزر'],
                    ['faq_cta_url', 'رابط الزر', 'url'],
                ],
            ],
        ],

        'contact' => [
            'title' => 'تواصل معنا',
            'tabs' => [
                'المحتوى' => [
                    ['ct_hero_title', 'البانر - العنوان', 'html'],
                    ['ct_hero_sub', 'البانر - النص', 'textarea'],
                    ['ct_info_title', 'عنوان معلومات التواصل'],
                    ['ct_info_sub', 'نص معلومات التواصل', 'textarea'],
                    ['ct_form_title', 'عنوان النموذج'],
                    ['ct_form_sub', 'نص النموذج', 'textarea'],
                ],
            ],
        ],

        'blog' => [
            'title' => 'المدونة',
            'tabs' => [
                'المحتوى' => [
                    ['blog_hero_title', 'البانر - العنوان', 'html'],
                    ['blog_hero_sub', 'البانر - النص', 'textarea'],
                ],
            ],
        ],
    ],
];
