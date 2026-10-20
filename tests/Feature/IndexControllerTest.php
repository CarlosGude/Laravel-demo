<?php

namespace Tests\Feature;

use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    public function test_index_devuelve_hello_carlos(): void
    {
        $this->getJson('/api')
            ->assertOk()
            ->assertExactJson(['message' => 'Hello Carlos!']);
    }

    public function test_index_devuelve_hello_name_in_url(): void
    {
        $name = 'lucas';
        $this->getJson('/api/' . $name)
            ->assertOk()
            ->assertExactJson(['message' => str_replace('{name}', ucfirst($name), 'Hello {name}!')]);
        ;
    }
}
