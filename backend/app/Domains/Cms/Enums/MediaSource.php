<?php

namespace App\Domains\Cms\Enums;

enum MediaSource: string
{
    case Upload = 'upload';
    case Canva = 'canva';
    case Supplier = 'supplier';
}
