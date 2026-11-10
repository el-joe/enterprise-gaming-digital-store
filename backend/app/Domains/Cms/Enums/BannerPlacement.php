<?php

namespace App\Domains\Cms\Enums;

enum BannerPlacement: string
{
    case Hero = 'hero';
    case HomePromo = 'home_promo';
    case Category = 'category';
    case Platform = 'platform';
}
