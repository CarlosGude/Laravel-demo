<?php

namespace Tests\Feature\Task\Infrastructure\Ports\Http;

use App\Task\Infrastructure\Models\TaskModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAllTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_every_persisted_task(): void
    {
        TaskModel::factory()->count(2)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertOk();
        $response->assertJsonCount(2);
    }

    public function test_it_returns_an_empty_list_when_there_are_no_tasks(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertOk();
        $response->assertExactJson([]);
    }
}
