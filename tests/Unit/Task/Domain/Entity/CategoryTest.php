<?php

namespace Tests\Unit\Task\Domain\Entity;

use App\Task\Domain\Entity\Category;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function test_create_new_builds_a_category_without_an_id(): void
    {
        $category = Category::createNew(1, 'Work');

        $this->assertNull($category->getId());
        $this->assertSame(1, $category->getUserId());
        $this->assertSame('Work', $category->getName());
    }

    public function test_create_with_id_keeps_the_name_unchanged(): void
    {
        $category = Category::createNew(1, 'Work')->createWithId(1);

        $this->assertSame(1, $category->getId());
        $this->assertSame('Work', $category->getName());
    }

    public function test_get_persisted_id_returns_the_id_when_present(): void
    {
        $category = Category::create(id: 1, userId: 1, name: 'Work');

        $this->assertSame(1, $category->getPersistedId());
    }

    public function test_get_persisted_id_throws_when_the_category_has_not_been_persisted(): void
    {
        $category = Category::createNew(1, 'Work');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Category has not been persisted yet.');

        $category->getPersistedId();
    }
}
