{{--
    The one product image of the storefront (Storefront v2, owner request): used by every card, the
    variant chooser, bag lines, checkout, orders, codes and the wishlist. Priority: the product's
    primary image (a staff choice first, else the active supplier image, both product images),
    then the brand logo, then the default art (Cards v3: nothing drawn over the image).
    `<picture>` with AVIF and WebP, the product name as alt, lazy unless `eager`.
--}}
@props(['image' => null, 'logo' => null, 'slug' => '', 'name' => '', 'eager' => false, 'sizes' => null, 'flush' => false])

<x-store.tile
    {{ $attributes }}
    :slug="$slug"
    :image="$image"
    :logo="$image ? null : $logo"
    :alt="$name"
    :eager="$eager"
    :sizes="$sizes"
    :flush="$flush"
/>
