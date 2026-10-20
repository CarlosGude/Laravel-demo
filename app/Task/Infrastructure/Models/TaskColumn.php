<?php

namespace App\Task\Infrastructure\Models;

enum TaskColumn: string
{
    case Uuid = 'uuid';
    case UserId = 'user_id';
    case Name = 'name';
    case Description = 'description';
    case Completed = 'completed';
    case CreatedAt = 'created_at';
    case UpdatedAt = 'updated_at';
}
