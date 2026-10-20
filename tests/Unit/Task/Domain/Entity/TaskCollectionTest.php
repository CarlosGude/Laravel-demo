<?php

namespace Tests\Unit\Task\Domain\Entity;

use App\Task\Domain\Entity\Task;
use App\Task\Domain\Entity\TaskCollection;
use PHPUnit\Framework\TestCase;

class TaskCollectionTest extends TestCase
{
    public function test_an_empty_collection_has_no_items(): void
    {
        $collection = new TaskCollection();

        $this->assertCount(0, $collection);
        $this->assertSame([], $collection->toArray());
    }

    public function test_from_array_builds_a_collection_from_a_list_of_tasks(): void
    {
        $first = Task::createNew(1, 'Buy milk', 'Whole milk, 2 liters');
        $second = Task::createNew(1, 'Buy bread', 'Sourdough');

        $collection = TaskCollection::fromArray([$first, $second]);

        $this->assertCount(2, $collection);
        $this->assertSame([$first, $second], $collection->toArray());
    }

    public function test_is_iterable(): void
    {
        $task = Task::createNew(1, 'Buy milk', 'Whole milk, 2 liters');

        $collection = new TaskCollection($task);

        $this->assertSame([$task], iterator_to_array($collection));
    }
}
