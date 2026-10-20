<?php

namespace App\Task\Domain\Repository;

enum SortDirection: string
{
    case Ascending = 'asc';
    case Descending = 'desc';
}
