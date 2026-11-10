<?php

namespace App\Domains\Cms\Enums;

enum PageStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
