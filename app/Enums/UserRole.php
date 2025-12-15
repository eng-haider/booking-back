<?php

namespace App\Enums;

enum UserRole: string
{
    case USER = 'user';
    case OWNER = 'owner';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match($this) {
            self::USER => 'User',
            self::OWNER => 'Owner',
            self::ADMIN => 'Admin',
        };
    }
}
