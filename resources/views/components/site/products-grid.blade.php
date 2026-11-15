@props(['products', 'withAnchor' => false])
@forelse ($products as $i => $p)
    <x-site.product-card
        :title="$p->title" :desc="$p->excerpt" :img="$p->imageUrl()" :label="$p->flavor_label"
        :color="$p->flavor_color ?: '#E51B6E'" :anchor="$withAnchor ? $p->anchor_id : ''"
        :delay="delay_class($i)" :url="$p->url()" :back-img="$p->cartonUrl()" />
@empty
    <p class="berry-empty">لا توجد منتجات بعد.</p>
@endforelse
