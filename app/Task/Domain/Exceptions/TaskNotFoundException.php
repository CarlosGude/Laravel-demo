<?php

namespace App\Task\Domain\Exceptions;

final class TaskNotFoundException extends \RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Task with id "%d" not found', $id));
    }

    public static function withUuid(string $uuid): self
    {
        return new self(sprintf('Task with uuid "%s" not found', $uuid));
    }
}
