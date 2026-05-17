<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_api_health_endpoint_returns_ok(): void
    {
        $this->getJson('/api/v1/health')->assertOk();
    }
}
