<?php

namespace App\User\Domain\Entity;

enum UserRole: string
{
    case Admin = 'admin';
    case Regular = 'user';
}
