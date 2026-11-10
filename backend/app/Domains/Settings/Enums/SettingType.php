<?php

namespace App\Domains\Settings\Enums;

enum SettingType: string
{
    case String = 'string';
    case Int = 'int';
    case Bool = 'bool';
    case Json = 'json';
    case Decimal = 'decimal';
}
