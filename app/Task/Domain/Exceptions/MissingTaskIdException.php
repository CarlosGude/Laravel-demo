<?php

namespace App\Task\Domain\Exceptions;

final class MissingTaskIdException extends \RuntimeException
{
    public static function create(): self
    {
        return new self('Task id is required');
    }
}
