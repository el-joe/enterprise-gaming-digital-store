{{--
    The one product image of the storefront (Storefront v2, owner request): used by every card, the
    variant chooser, bag lines, checkout, orders, codes and the wishlist. Priority: the product's
    primary image (a staff choice first, else the active supplier image, both product images),
    then the brand logo, then the generated dial art. `<picture>` with AVIF and WebP, the product
    name as alt, lazy unless `eager`.
--}}
@props(['image' => null, 'logo' => null, 'slug' => '', 'name' => '', 'overline' => null, 'label' => null, 'eager' => false, 'sizes' => null])

<x-store.tile
    {{ $attributes }}
    :slug="$slug"
    :overline="$overline"
    :name="$label"
    :image="$image"
    :logo="$image ? null : $logo"
    :alt="$name"
    :eager="$eager"
    :sizes="$sizes"
/>
