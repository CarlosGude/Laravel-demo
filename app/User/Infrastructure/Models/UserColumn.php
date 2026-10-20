<?php

namespace App\User\Infrastructure\Models;

enum UserColumn: string
{
    case Name = 'name';
    case Email = 'email';
    case Password = 'password';
    case Role = 'role';
    case CreatedAt = 'created_at';
    case UpdatedAt = 'updated_at';
}
