<?php

namespace Tests\Feature\Task\Infrastructure\Repository;

use App\Task\Infrastructure\Models\CategoryModel;
use App\Task\Infrastructure\Repository\MySqlCategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MySqlCategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private MySqlCategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new MySqlCategoryRepository();
    }

    public function test_find_or_create_by_names_creates_missing_categories(): void
    {
        $categories = $this->repository->findOrCreateByNames(['Work', 'Urgent']);

        $this->assertSame(['Work', 'Urgent'], $categories->names());
        $this->assertDatabaseHas('categories', ['name' => 'Work']);
        $this->assertDatabaseHas('categories', ['name' => 'Urgent']);
    }

    public function test_find_or_create_by_names_reuses_existing_categories(): void
    {
        $existing = CategoryModel::factory()->create(['name' => 'Work']);

        $categories = $this->repository->findOrCreateByNames(['Work']);

        $this->assertSame([$existing->id], $categories->ids());
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_find_or_create_by_names_deduplicates_repeated_names(): void
    {
        $categories = $this->repository->findOrCreateByNames(['Work', 'Work']);

        $this->assertCount(1, $categories);
        $this->assertDatabaseCount('categories', 1);
    }
}
