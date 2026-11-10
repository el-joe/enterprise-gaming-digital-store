<?php

namespace App\Domains\Catalog\Enums;

enum DeliveryFieldDefinitionInputType: string
{
    case Text = 'text';
    case Number = 'number';
    case Select = 'select';
    case Email = 'email';
}
