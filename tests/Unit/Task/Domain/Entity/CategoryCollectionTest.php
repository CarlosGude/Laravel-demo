<?php

namespace Tests\Unit\Task\Domain\Entity;

use App\Task\Domain\Entity\Category;
use App\Task\Domain\Entity\CategoryCollection;
use PHPUnit\Framework\TestCase;

class CategoryCollectionTest extends TestCase
{
    public function test_an_empty_collection_has_no_items(): void
    {
        $collection = new CategoryCollection();

        $this->assertCount(0, $collection);
        $this->assertSame([], $collection->toArray());
        $this->assertSame([], $collection->ids());
        $this->assertSame([], $collection->names());
    }

    public function test_from_array_builds_a_collection_from_a_list_of_categories(): void
    {
        $work = Category::create(1, 1, 'Work');
        $urgent = Category::create(2, 1, 'Urgent');

        $collection = CategoryCollection::fromArray([$work, $urgent]);

        $this->assertCount(2, $collection);
        $this->assertSame([1, 2], $collection->ids());
        $this->assertSame(['Work', 'Urgent'], $collection->names());
    }

    public function test_is_iterable(): void
    {
        $work = Category::create(1, 1, 'Work');

        $collection = new CategoryCollection($work);

        $this->assertSame([$work], iterator_to_array($collection));
    }
}
