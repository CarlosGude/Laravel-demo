<?php

namespace App\Task\Domain\Repository;

enum TaskSortField: string
{
    case CreatedAt = 'created_at';
    case UpdatedAt = 'updated_at';
}
