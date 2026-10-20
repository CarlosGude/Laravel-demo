<?php

namespace App\Task\Domain\Repository;

use App\Task\Domain\Entity\CategoryCollection;

interface CategoryRepositoryInterface
{
    /**
     * @param array<int, string> $names
     */
    public function findOrCreateByNames(array $names): CategoryCollection;
}
