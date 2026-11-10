<?php

namespace App\Domains\Seo\Enums;

enum SeoTemplatePageType: string
{
    case Home = 'home';
    case Platform = 'platform';
    case Category = 'category';
    case Product = 'product';
    case Blog = 'blog';
    case Page = 'page';
}
