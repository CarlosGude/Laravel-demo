<?php

namespace Tests\Unit\Task\Domain\Exceptions;

use App\Task\Domain\Exceptions\TaskNotFoundException;
use PHPUnit\Framework\TestCase;

class TaskNotFoundExceptionTest extends TestCase
{
    public function test_with_id_builds_a_runtime_exception_with_a_descriptive_message(): void
    {
        $exception = TaskNotFoundException::withId(42);

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame('Task with id "42" not found', $exception->getMessage());
    }
}
