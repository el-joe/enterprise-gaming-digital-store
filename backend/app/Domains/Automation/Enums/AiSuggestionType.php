<?php

namespace App\Domains\Automation\Enums;

enum AiSuggestionType: string
{
    case Product = 'product';
    case Campaign = 'campaign';
    case Bundle = 'bundle';
    case Coupon = 'coupon';
    case BlogIdea = 'blog_idea';
    case SeoDescription = 'seo_description';
    case MarketingCopy = 'marketing_copy';
    case SocialPost = 'social_post';
    case Pricing = 'pricing';
}
