<?php

namespace App\Task\Domain\Entity;

/**
 * @implements \IteratorAggregate<int, Category>
 */
final readonly class CategoryCollection implements \Countable, \IteratorAggregate
{
    /** @var array<int, Category> */
    private array $categories;

    public function __construct(Category ...$categories)
    {
        $this->categories = array_values($categories);
    }

    /**
     * @param array<int|string, Category> $categories
     */
    public static function fromArray(array $categories): self
    {
        return new self(...array_values($categories));
    }

    /**
     * @return \ArrayIterator<int, Category>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->categories);
    }

    public function count(): int
    {
        return count($this->categories);
    }

    /**
     * @return array<int, Category>
     */
    public function toArray(): array
    {
        return $this->categories;
    }

    /**
     * @return array<int, int>
     */
    public function ids(): array
    {
        return array_map(static fn (Category $category): int => $category->getPersistedId(), $this->categories);
    }

    /**
     * @return array<int, string>
     */
    public function names(): array
    {
        return array_map(static fn (Category $category): string => $category->getName(), $this->categories);
    }
}
