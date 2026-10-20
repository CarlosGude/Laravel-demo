<?php

namespace App\User\Domain\Exceptions;

final class EmailAlreadyRegisteredException extends \RuntimeException
{
    public static function withEmail(string $email): self
    {
        return new self(sprintf('A user with email "%s" is already registered', $email));
    }
}
