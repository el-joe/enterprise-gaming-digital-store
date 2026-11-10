<?php

namespace App\Domains\Identity\Enums;

enum UserType: string
{
    case Customer = 'customer';
    case Staff = 'staff';
}
