<?php

namespace App\Enums;

enum UserRole: string
{
    case USER = 'user';
    case OWNER = 'provider';
    case ADMIN = 'super_admin';

    public function label(): string
    {
        return match($this) {
            self::USER => 'User',
            self::OWNER => 'provider',
            self::ADMIN => 'super_admin',
        };
    }
}
