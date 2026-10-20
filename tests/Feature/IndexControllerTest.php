<?php

namespace Tests\Feature;

use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    public function test_index_devuelve_hello_world(): void
    {
        $this->getJson('/api')
            ->assertOk()
            ->assertExactJson(['message' => 'Hello World!']);
    }
}
