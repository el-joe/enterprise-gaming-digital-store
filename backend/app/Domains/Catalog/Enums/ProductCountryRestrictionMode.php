<?php

namespace App\Domains\Catalog\Enums;

enum ProductCountryRestrictionMode: string
{
    case Allow = 'allow';
    case Deny = 'deny';
}
