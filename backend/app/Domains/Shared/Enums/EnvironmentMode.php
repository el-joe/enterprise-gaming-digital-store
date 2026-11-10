<?php

namespace App\Domains\Shared\Enums;

enum EnvironmentMode: string
{
    case Sandbox = 'sandbox';
    case Production = 'production';
}
