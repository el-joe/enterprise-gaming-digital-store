<?php

namespace App\Domains\Cms\Enums;

enum BlogPostStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';
}
