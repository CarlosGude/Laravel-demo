<?php

namespace App\User\Infrastructure\Security;

use App\User\Domain\Security\PasswordHasherInterface;
use Illuminate\Support\Facades\Hash;

final class LaravelPasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        return Hash::make($plainPassword);
    }

    public function verify(string $plainPassword, string $hashedPassword): bool
    {
        return Hash::check($plainPassword, $hashedPassword);
    }
}
