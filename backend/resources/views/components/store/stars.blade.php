@props(['rating' => 0, 'size' => 14])

<span {{ $attributes->class(['inline-flex items-center gap-0.5 text-sun-500']) }} role="img" aria-label="{{ __('storefront.reviews.rating', ['rating' => $rating]) }}">
    @for ($i = 1; $i <= 5; $i++)
        <x-store.icon name="star" :size="$size" @class(['fill-current' => $i <= (int) round((float) $rating)]) />
    @endfor
</span>
