<?php

namespace Tests\Feature\Task\Infrastructure\Repository;

use App\Task\Infrastructure\Models\CategoryModel;
use App\Task\Infrastructure\Repository\MySqlCategoryRepository;
use App\User\Infrastructure\Models\UserModel;
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
        $user = UserModel::factory()->create();

        $categories = $this->repository->findOrCreateByNames($user->id, ['Work', 'Urgent']);

        $this->assertSame(['Work', 'Urgent'], $categories->names());
        $this->assertDatabaseHas('categories', ['user_id' => $user->id, 'name' => 'Work']);
        $this->assertDatabaseHas('categories', ['user_id' => $user->id, 'name' => 'Urgent']);
    }

    public function test_find_or_create_by_names_reuses_existing_categories(): void
    {
        $existing = CategoryModel::factory()->create(['name' => 'Work']);

        $categories = $this->repository->findOrCreateByNames($existing->user_id, ['Work']);

        $this->assertSame([$existing->id], $categories->ids());
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_find_or_create_by_names_deduplicates_repeated_names(): void
    {
        $user = UserModel::factory()->create();

        $categories = $this->repository->findOrCreateByNames($user->id, ['Work', 'Work']);

        $this->assertCount(1, $categories);
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_find_or_create_by_names_scopes_categories_per_user(): void
    {
        $firstUser = UserModel::factory()->create();
        $secondUser = UserModel::factory()->create();

        $firstUserCategories = $this->repository->findOrCreateByNames($firstUser->id, ['Work']);
        $secondUserCategories = $this->repository->findOrCreateByNames($secondUser->id, ['Work']);

        $this->assertNotSame($firstUserCategories->ids(), $secondUserCategories->ids());
        $this->assertDatabaseCount('categories', 2);
    }
}
