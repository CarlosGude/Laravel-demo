<?php

namespace App\Task\Infrastructure\Repository;

use App\Task\Domain\Entity\Category;
use App\Task\Domain\Entity\CategoryCollection;
use App\Task\Domain\Repository\CategoryRepositoryInterface;
use App\Task\Infrastructure\Models\CategoryModel;

final class MySqlCategoryRepository implements CategoryRepositoryInterface
{
    public function findOrCreateByNames(array $names): CategoryCollection
    {
        $uniqueNames = array_values(array_unique($names));

        return CategoryCollection::fromArray(array_map(
            static fn (string $name): Category => CategoryModel::query()
                ->firstOrCreate(['name' => $name])
                ->toEntity(),
            $uniqueNames
        ));
    }
}
