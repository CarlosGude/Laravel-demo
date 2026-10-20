<?php

namespace Tests\Unit\Task\Application\Exceptions;

use App\Task\Application\Exceptions\TaskWithIdCantBeCreated;
use PHPUnit\Framework\TestCase;

class TaskWithIdCantBeCreatedTest extends TestCase
{
    public function test_it_builds_an_exception_with_a_descriptive_message(): void
    {
        $exception = new TaskWithIdCantBeCreated();

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertSame('Task with an id is not allowed', $exception->getMessage());
    }
}
