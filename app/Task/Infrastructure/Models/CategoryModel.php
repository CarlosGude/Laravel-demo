<?php

namespace App\Task\Infrastructure\Models;

use App\Task\Domain\Entity\Category;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 */
class CategoryModel extends Model
{
    /**
     * @use HasFactory<\Database\Factories\CategoryFactory>
     */
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        CategoryColumn::Name->value,
    ];

    /**
     * @return BelongsToMany<TaskModel, $this>
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(TaskModel::class, 'category_task', 'category_id', 'task_id');
    }

    public function toEntity(): Category
    {
        return Category::create(id: $this->id, name: $this->name);
    }
}
