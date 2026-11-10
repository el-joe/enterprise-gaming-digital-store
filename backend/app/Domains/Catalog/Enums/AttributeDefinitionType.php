<?php

namespace App\Domains\Catalog\Enums;

enum AttributeDefinitionType: string
{
    case Text = 'text';
    case Number = 'number';
    case Boolean = 'boolean';
    case Select = 'select';
}
